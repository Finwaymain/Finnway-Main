<?php

namespace App\Services;

use App\Models\WalletTopupCashbackRule;
use App\Models\WalletTopupCashbackLog;
use App\Http\Controllers\API\v1\GcmController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class WalletTopupCashbackService
{
    /**
     * Find the best matching active rule for a given top-up.
     */
    public static function findMatchingRule(float $topupAmount, string $userType, int $userId): ?WalletTopupCashbackRule
    {
        $now = Carbon::now();

        $rules = WalletTopupCashbackRule::where('is_active', true)
            ->where(function ($query) use ($userType) {
                $query->where('target_user_type', 'all')
                      ->orWhere('target_user_type', $userType);
            })
            ->where('min_topup_amount', '<=', $topupAmount)
            ->where(function ($query) use ($topupAmount) {
                $query->whereNull('max_topup_amount')
                      ->orWhere('max_topup_amount', '>=', $topupAmount);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('start_date')
                      ->orWhere('start_date', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('end_date')
                      ->orWhere('end_date', '>=', $now);
            })
            ->orderBy('priority', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        foreach ($rules as $rule) {
            // Check usage limit per user if set
            if ($rule->usage_limit_per_user && $rule->usage_limit_per_user > 0) {
                $usedCount = WalletTopupCashbackLog::where('rule_id', $rule->id)
                    ->where('user_id', $userId)
                    ->where('user_type', $userType)
                    ->count();

                if ($usedCount >= $rule->usage_limit_per_user) {
                    continue; // Skip this rule, user exceeded limit
                }
            }

            return $rule;
        }

        return null;
    }

    /**
     * Calculate cashback amount based on rule and top-up amount.
     */
    public static function calculateCashback(WalletTopupCashbackRule $rule, float $topupAmount): float
    {
        $cashback = 0.0;

        if ($rule->cashback_type === 'flat') {
            $cashback = (float) $rule->cashback_value;
        } elseif ($rule->cashback_type === 'percentage') {
            $cashback = ($topupAmount * (float) $rule->cashback_value) / 100.0;

            // Apply "upto" ceiling if defined
            if (!empty($rule->max_cashback_amount) && $rule->max_cashback_amount > 0) {
                $cashback = min($cashback, (float) $rule->max_cashback_amount);
            }
        }

        return round(max(0, $cashback), 2);
    }

    /**
     * Evaluate, credit wallet, send notification, and log cashback.
     *
     * @return array|null Returns details if cashback was awarded, or null.
     */
    public static function processTopupCashback(int $userId, string $userType, float $topupAmount, ?string $origTxnId = null): ?array
    {
        try {
            $userType = strtolower(trim($userType));
            if ($userType === 'driver' || $userType === 'conducteur') {
                $userType = 'driver';
            } else {
                $userType = 'customer';
            }

            $rule = self::findMatchingRule($topupAmount, $userType, $userId);
            if (!$rule) {
                return null;
            }

            $cashbackAmount = self::calculateCashback($rule, $topupAmount);
            if ($cashbackAmount <= 0) {
                return null;
            }

            $nowDateTime = Carbon::now('Asia/Kolkata')->format('Y-m-d H:i:s');
            $cashbackTxnId = 'CB_' . time() . '_' . rand(1000, 9999);

            DB::beginTransaction();

            $newBalance = 0;
            $userFcm = null;
            $userName = 'User';

            if ($userType === 'customer') {
                $user = DB::table('tj_user_app')->where('id', $userId)->lockForUpdate()->first();
                if (!$user) {
                    DB::rollBack();
                    return null;
                }

                $currentCashback = (float) ($user->cashback_balance ?? 0);
                $newCashbackBalance = $currentCashback + $cashbackAmount;
                DB::table('tj_user_app')->where('id', $userId)->update([
                    'cashback_balance' => $newCashbackBalance,
                    'modifier'         => $nowDateTime,
                ]);

                $userFcm = $user->fcm_id ?? null;
                $userName = trim(($user->nom ?? '') . ' ' . ($user->prenom ?? ''));

            } else {
                $driver = DB::table('tj_conducteur')->where('id', $userId)->lockForUpdate()->first();
                if (!$driver) {
                    DB::rollBack();
                    return null;
                }

                $currentCashback = (float) ($driver->cashback_balance ?? 0);
                $newCashbackBalance = $currentCashback + $cashbackAmount;
                DB::table('tj_conducteur')->where('id', $userId)->update([
                    'cashback_balance' => $newCashbackBalance,
                    'modifier'         => $nowDateTime,
                ]);

                $userFcm = $driver->fcm_id ?? null;
                $userName = trim(($driver->nom ?? '') . ' ' . ($driver->prenom ?? ''));
            }

            // Create log record
            WalletTopupCashbackLog::create([
                'rule_id'         => $rule->id,
                'user_id'         => $userId,
                'user_type'       => $userType,
                'topup_amount'    => $topupAmount,
                'cashback_amount' => $cashbackAmount,
                'txn_id'          => $cashbackTxnId,
                'status'          => 'credited',
                'notes'           => "Rule: {$rule->name} | Topup: ₹{$topupAmount} | Cashback: ₹{$cashbackAmount}",
            ]);

            DB::commit();

            // Send Push & In-app Notification
            $formattedCashback = number_format($cashbackAmount, 2);
            $formattedTopup = number_format($topupAmount, 2);
            $formattedBalance = number_format($newBalance, 2);

            $notifTitle = '🎉 Cashback Credited!';
            $notifBody = "Congratulations! You received ₹{$formattedCashback} cashback on your wallet top-up of ₹{$formattedTopup}. Updated balance: ₹{$formattedBalance}.";

            if (!empty($userFcm)) {
                $payload = [
                    'title'     => $notifTitle,
                    'body'      => $notifBody,
                    'sound'     => 'default',
                    'tag'       => 'cashback_topup',
                    'type'      => 'wallet',
                    'amount'    => (string) $cashbackAmount,
                    'user_type' => $userType,
                ];
                try {
                    GcmController::sendNotification($userFcm, $payload);
                } catch (\Throwable $e) {
                    Log::warning("FCM cashback notification failed: " . $e->getMessage());
                }
            }

            if (Schema::hasTable('tj_notification')) {
                try {
                    DB::table('tj_notification')->insert([
                        'titre'    => $notifTitle,
                        'message'  => $notifBody,
                        'statut'   => 'yes',
                        'creer'    => $nowDateTime,
                        'modifier' => $nowDateTime,
                        'to_id'    => $userId,
                        'from_id'  => 0,
                        'type'     => 'wallet_credit',
                    ]);
                } catch (\Throwable $e) {
                    Log::warning("In-app cashback notification insert failed: " . $e->getMessage());
                }
            }

            return [
                'cashback_amount' => $cashbackAmount,
                'rule_name'       => $rule->name,
                'new_balance'     => $newBalance,
                'txn_id'          => $cashbackTxnId,
            ];

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("WalletTopupCashbackService error: " . $e->getMessage());
            return null;
        }
    }
}
