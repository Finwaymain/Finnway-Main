<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WalletBalanceHelper
{
    /**
     * Clean a phone string to the last 10 digits.
     */
    public static function cleanPhone(?string $phone): string
    {
        if (empty($phone)) {
            return '';
        }
        $digits = preg_replace('/\D/', '', $phone);
        return (strlen($digits) >= 10) ? substr($digits, -10) : $digits;
    }

    /**
     * Resolve user strictly using unique phone number first, then ID with type.
     */
    public static function resolveUser(?string $phone, $id, string $userType = 'customer')
    {
        $table = ($userType === 'driver') ? 'tj_conducteur' : 'tj_user_app';
        $clean = self::cleanPhone($phone);

        if (!empty($clean)) {
            $user = DB::table($table)->where('phone', 'like', "%{$clean}%")->first();
            if ($user) {
                return $user;
            }
        }

        if (!empty($id)) {
            return DB::table($table)->where('id', $id)->first();
        }

        return null;
    }

    /**
     * Synchronize and guarantee that legitimate platform earnings
     * (Marketplace sales, referral rewards, rides, services) are accurately
     * reflected in withdrawable_balance.
     */
    public static function syncUserWithdrawableBalance($userId, string $userType = 'customer', ?string $phone = null): float
    {
        $table = ($userType === 'driver') ? 'tj_conducteur' : 'tj_user_app';
        $user = self::resolveUser($phone, $userId, $userType);
        if (!$user) {
            return 0.0;
        }

        $currentBalance = floatval($user->amount ?? 0);
        if ($currentBalance <= 0) {
            return 0.0;
        }

        $actualId = (int)$user->id;
        $userPhone = $user->phone ?? $phone;
        $cleanPhone = self::cleanPhone($userPhone);

        // 1. Marketplace Seller Payouts (Released or completed)
        $mpEarnings = 0.0;
        if (Schema::hasTable('marketplace_orders')) {
            $mpQ = DB::table('marketplace_orders')
                ->whereIn('payout_status', ['released', 'completed', 'success'])
                ->where(function($q) use ($actualId, $cleanPhone, $userType) {
                    $q->where(function($sq) use ($actualId, $userType) {
                        $sq->where('seller_id', $actualId);
                        if (Schema::hasColumn('marketplace_orders', 'seller_type')) {
                            $sq->where(function($ssq) use ($userType) {
                                $ssq->where('seller_type', $userType)->orWhereNull('seller_type');
                            });
                        }
                    });
                    if (!empty($cleanPhone) && Schema::hasColumn('marketplace_orders', 'seller_phone')) {
                        $q->orWhere('seller_phone', 'like', "%{$cleanPhone}%");
                    }
                });
            $mpEarnings = floatval($mpQ->sum('seller_payout_amount'));
        }

        // 2. Referral Cashback / Rewards from ledger
        $refEarnings = 0.0;
        $txTable = ($userType === 'driver') ? 'tj_conducteur_transaction' : 'tj_transaction';
        $idCol = ($userType === 'driver') ? 'id_conducteur' : 'id_user_app';
        if (Schema::hasTable($txTable)) {
            $refEarnings = floatval(DB::table($txTable)
                ->where($idCol, $actualId)
                ->where(function($q) {
                    $q->where('payment_method', 'LIKE', '%Referral%')
                      ->orWhere('payment_method', 'LIKE', '%Cashback%')
                      ->orWhere('payment_method', 'LIKE', '%Marketplace%')
                      ->orWhere('wallet_bucket', 'earning');
                })
                ->where(function($q) {
                    $q->where('type', 'credit')
                      ->orWhere('deduction_type', 'credit')
                      ->orWhere('deduction_type', 1);
                })
                ->sum('amount'));
        }

        // 3. Driver Rides / Services (if driver)
        $serviceEarnings = 0.0;
        if ($userType === 'driver') {
            if (Schema::hasTable('tj_requete')) {
                $serviceEarnings += floatval(DB::table('tj_requete')->where('id_conducteur', $actualId)->where('statut', 'completed')->sum('montant'));
            }
            if (Schema::hasTable('parcel_orders')) {
                $serviceEarnings += floatval(DB::table('parcel_orders')->where('id_conducteur', $actualId)->where('status', 'completed')->sum('amount'));
            }
            if (Schema::hasTable('service_requests')) {
                $serviceEarnings += floatval(DB::table('service_requests')->where('driver_id', $actualId)->whereIn('status', ['Completed', 'completed'])->sum('amount'));
            }
        }

        // 4. Total previous successful payouts
        $totalWithdrawn = 0.0;
        if (Schema::hasTable('withdrawals')) {
            $totalWithdrawn = floatval(DB::table('withdrawals')
                ->where('id_conducteur', $actualId)
                ->whereIn('statut', ['success', 'approved', 'completed'])
                ->sum('amount'));
        }

        $totalEarned = max(
            floatval($user->earn_amount ?? 0),
            $mpEarnings + $refEarnings,
            $serviceEarnings
        );

        // Check if user has explicit top-up transactions
        $hasTopupRecord = false;
        if (Schema::hasTable($txTable)) {
            $hasTopupRecord = DB::table($txTable)
                ->where($idCol, $actualId)
                ->where(function($q) {
                    $q->where('wallet_bucket', 'topup')
                      ->orWhere('description', 'LIKE', '%Top-Up%')
                      ->orWhere('payment_method', 'LIKE', '%Razorpay%')
                      ->orWhere('payment_method', 'LIKE', '%UPI%')
                      ->orWhere('payment_method', 'LIKE', '%Card%');
                })
                ->exists();
        }

        // If the user never did a top-up, their wallet balance is legitimately from earnings/platform balance
        if (!$hasTopupRecord && $currentBalance > 0 && $totalEarned <= 0) {
            $totalEarned = $currentBalance;
        }

        $calculatedWithdrawable = max(0, min($currentBalance, $totalEarned - $totalWithdrawn));
        $currentWithdrawable = floatval($user->withdrawable_balance ?? 0);

        // If stored withdrawable balance is lower than verified earnings, update database
        if ($calculatedWithdrawable > $currentWithdrawable || floatval($user->topup_balance ?? 0) > ($currentBalance - $calculatedWithdrawable)) {
            $newTopup = max(0, $currentBalance - $calculatedWithdrawable);
            DB::table($table)->where('id', $actualId)->update([
                'withdrawable_balance' => $calculatedWithdrawable,
                'topup_balance'        => $newTopup,
                'earn_amount'          => max(floatval($user->earn_amount ?? 0), $totalEarned),
            ]);
            return $calculatedWithdrawable;
        }

        return $currentWithdrawable;
    }
}
