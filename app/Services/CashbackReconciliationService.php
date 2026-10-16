<?php

namespace App\Services;

use App\Models\WalletTopupCashbackLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CashbackReconciliationService
{
    /**
     * Revert duplicate cashback disbursements from 16 Oct 2026.
     * Retains the first legitimate disbursement for each user/transaction
     * and rolls back any excess amount from user cashback_balance / amount.
     */
    public static function revertDuplicateDisbursements(): array
    {
        $report = [
            'total_duplicates_found' => 0,
            'total_reverted_amount'  => 0.0,
            'affected_users'         => [],
        ];

        try {
            // Find all logs on 16 Oct 2026
            $logs = WalletTopupCashbackLog::where('created_at', '>=', '2026-10-16 00:00:00')
                ->where('status', '!=', 'reverted')
                ->orderBy('id', 'asc')
                ->get();

            if ($logs->isEmpty()) {
                return $report;
            }

            // Group by user_id and user_type
            $grouped = $logs->groupBy(function ($item) {
                return $item->user_id . '_' . $item->user_type;
            });

            foreach ($grouped as $key => $userLogs) {
                if ($userLogs->count() <= 1) {
                    continue; // No duplicate for this user
                }

                // Keep the first log as the legitimate one
                $firstLog = $userLogs->shift();
                $duplicateLogs = $userLogs; // All remaining logs are duplicates

                $excessAmount = 0.0;
                $dupIds = [];

                foreach ($duplicateLogs as $dup) {
                    $excessAmount += (float) $dup->cashback_amount;
                    $dupIds[] = $dup->id;
                }

                if ($excessAmount <= 0) {
                    continue;
                }

                $userId   = (int) $firstLog->user_id;
                $userType = $firstLog->user_type; // customer or driver
                $table    = ($userType === 'driver') ? 'tj_conducteur' : 'tj_user_app';

                DB::beginTransaction();

                $account = DB::table($table)->where('id', $userId)->lockForUpdate()->first();
                if ($account) {
                    $currentCashback = (float) ($account->cashback_balance ?? 0.0);
                    $newCashback = max(0.0, round($currentCashback - $excessAmount, 2));

                    // If user had converted cashback to amount, adjust amount if needed
                    $remainingExcess = $excessAmount - $currentCashback;
                    $updates = ['cashback_balance' => $newCashback];

                    if ($remainingExcess > 0 && isset($account->amount)) {
                        $currentAmount = (float) $account->amount;
                        $updates['amount'] = max(0.0, round($currentAmount - $remainingExcess, 2));
                    }

                    DB::table($table)->where('id', $userId)->update($updates);

                    // Mark duplicate logs as reverted
                    WalletTopupCashbackLog::whereIn('id', $dupIds)->update([
                        'status' => 'reverted',
                        'notes'  => DB::raw("CONCAT(COALESCE(notes, ''), ' | REVERTED DUPLICATE DISBURSEMENT')"),
                    ]);

                    // Also remove any corresponding transactions from tj_transaction if created
                    if ($userType === 'customer') {
                        DB::table('tj_transaction')
                            ->where('id_user_app', $userId)
                            ->where('payment_method', 'Cashback')
                            ->where('creer', '>=', '2026-10-16 00:00:00')
                            ->orderBy('id', 'desc')
                            ->limit(count($dupIds))
                            ->delete();
                    } else {
                        DB::table('tj_conducteur_transaction')
                            ->where('id_conducteur', $userId)
                            ->where('payment_method', 'Cashback')
                            ->where('creer', '>=', '2026-10-16 00:00:00')
                            ->orderBy('id', 'desc')
                            ->limit(count($dupIds))
                            ->delete();
                    }

                    DB::commit();

                    $report['total_duplicates_found'] += count($dupIds);
                    $report['total_reverted_amount']  += $excessAmount;
                    $report['affected_users'][] = [
                        'user_id'         => $userId,
                        'user_type'       => $userType,
                        'duplicate_count' => count($dupIds),
                        'excess_reverted' => $excessAmount,
                        'new_cashback'    => $newCashback,
                    ];
                } else {
                    DB::rollBack();
                }
            }

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("CashbackReconciliationService error: " . $e->getMessage());
        }

        return $report;
    }
}
