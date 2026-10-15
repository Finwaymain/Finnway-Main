<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ReconcileDuplicateWalletTransactions extends Command
{
    protected $signature = 'wallet:reconcile-duplicates {--dry-run : Only display duplicate entries without modifying database}';
    protected $description = 'Find and delete duplicate top-up records created simultaneously by Webhook and AddAmountController, and reconcile wallet balances';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        if ($dryRun) {
            $this->warn('Running in DRY-RUN mode. No database records will be modified.');
        } else {
            $this->info('Starting Wallet Duplicate Reconciler...');
        }

        $customerDuplicatesRemoved = 0;
        $driverDuplicatesRemoved = 0;
        $affectedCustomerIds = [];
        $affectedDriverIds = [];

        // 1. Reconcile Customer Transactions (tj_transaction)
        $this->info('--- Scanning Customer Transactions (tj_transaction) ---');
        $customerTxns = DB::table('tj_transaction')
            ->orderBy('id_user_app')
            ->orderBy('creer')
            ->get();

        $groupedByCustomer = [];
        foreach ($customerTxns as $t) {
            $groupedByCustomer[$t->id_user_app][] = $t;
        }

        foreach ($groupedByCustomer as $userId => $txns) {
            $count = count($txns);
            for ($i = 0; $i < $count; $i++) {
                $itemA = $txns[$i];
                if (!$itemA) continue;

                // Look for an authoritative Razorpay UPI payment entry
                $isUpiA = (stripos((string)($itemA->description ?? ''), 'UPI Payment from') !== false)
                    || (str_starts_with((string)($itemA->txn_id ?? ''), 'pay_'))
                    || ($itemA->payment_method === 'UPI');

                if (!$isUpiA) continue;

                $amtA = floatval($itemA->amount ?? 0);
                $timeA = strtotime($itemA->creer ?? $itemA->date ?? '0');

                // Check other rows for this same user for a redundant "Wallet Top-Up" row
                for ($j = 0; $j < $count; $j++) {
                    if ($i === $j) continue;
                    $itemB = $txns[$j];
                    if (!$itemB) continue;

                    $isTopupB = (trim((string)($itemB->description ?? '')) === 'Wallet Top-Up');
                    if (!$isTopupB) continue;

                    $amtB = floatval($itemB->amount ?? 0);
                    $timeB = strtotime($itemB->creer ?? $itemB->date ?? '0');

                    // If same amount and created within 5 minutes (300 seconds)
                    if (abs($amtA - $amtB) < 0.01 && abs($timeA - $timeB) <= 300) {
                        $this->line("Found Customer #{$userId} duplicate: Top-Up Row #{$itemB->id} duplicates UPI Row #{$itemA->id} ({$itemA->txn_id}, ₹{$amtA})");
                        
                        if (!$dryRun) {
                            DB::table('tj_transaction')->where('id', $itemB->id)->delete();
                            $customerDuplicatesRemoved++;
                            $affectedCustomerIds[$userId] = true;
                        }

                        $txns[$j] = null; // Mark as processed
                    }
                }
            }
        }

        // 2. Reconcile Driver Transactions (tj_conducteur_transaction)
        $this->info('--- Scanning Driver Transactions (tj_conducteur_transaction) ---');
        $driverTxns = DB::table('tj_conducteur_transaction')
            ->orderBy('id_conducteur')
            ->orderBy('creer')
            ->get();

        $groupedByDriver = [];
        foreach ($driverTxns as $t) {
            $groupedByDriver[$t->id_conducteur][] = $t;
        }

        foreach ($groupedByDriver as $driverId => $txns) {
            $count = count($txns);
            for ($i = 0; $i < $count; $i++) {
                $itemA = $txns[$i];
                if (!$itemA) continue;

                $isUpiA = (stripos((string)($itemA->description ?? ''), 'UPI Payment from') !== false)
                    || (str_starts_with((string)($itemA->txn_id ?? ''), 'pay_'))
                    || ($itemA->payment_method === 'UPI');

                if (!$isUpiA) continue;

                $amtA = floatval($itemA->amount ?? 0);
                $timeA = strtotime($itemA->creer ?? '0');

                for ($j = 0; $j < $count; $j++) {
                    if ($i === $j) continue;
                    $itemB = $txns[$j];
                    if (!$itemB) continue;

                    $isTopupB = (trim((string)($itemB->description ?? '')) === 'Wallet Top-Up');
                    if (!$isTopupB) continue;

                    $amtB = floatval($itemB->amount ?? 0);
                    $timeB = strtotime($itemB->creer ?? '0');

                    if (abs($amtA - $amtB) < 0.01 && abs($timeA - $timeB) <= 300) {
                        $this->line("Found Driver #{$driverId} duplicate: Top-Up Row #{$itemB->id} duplicates UPI Row #{$itemA->id} ({$itemA->txn_id}, ₹{$amtA})");
                        
                        if (!$dryRun) {
                            DB::table('tj_conducteur_transaction')->where('id', $itemB->id)->delete();
                            $driverDuplicatesRemoved++;
                            $affectedDriverIds[$driverId] = true;
                        }

                        $txns[$j] = null;
                    }
                }
            }
        }

        // 3. Balance Reconciliation for Affected Accounts
        if (!$dryRun) {
            $this->info('--- Reconciling Wallet Balances ---');

            // Force check key test users even if not captured above
            $affectedCustomerIds[227] = true; // Ranjit Pal
            if (DB::table('tj_user_app')->where('id', 228)->exists()) $affectedCustomerIds[228] = true; // Sudeep Kashyap
            if (DB::table('tj_user_app')->where('id', 229)->exists()) $affectedCustomerIds[229] = true; // Ashutosh Giri
            if (DB::table('tj_conducteur')->where('id', 115)->exists()) $affectedDriverIds[115] = true; // Vijay Agrahari

            foreach (array_keys($affectedCustomerIds) as $cId) {
                $user = DB::table('tj_user_app')->where('id', $cId)->first();
                if (!$user) continue;

                $credits = DB::table('tj_transaction')
                    ->where('id_user_app', $cId)
                    ->where(function ($q) {
                        $q->where('type', 'credit')
                          ->orWhere('deduction_type', 1);
                    })
                    ->sum('amount');

                $debits = DB::table('tj_transaction')
                    ->where('id_user_app', $cId)
                    ->where(function ($q) {
                        $q->where('type', 'debit')
                          ->orWhere('deduction_type', 0);
                    })
                    ->sum('amount');

                $calculatedBalance = round(max(0, floatval($credits) - floatval($debits)), 2);
                $currentBalance = round(floatval($user->amount ?? 0), 2);

                $this->line("Customer #{$cId} ({$user->prenom} {$user->nom}): Current Balance = ₹{$currentBalance}, Calculated from History = ₹{$calculatedBalance}");

                // Update user amount to match genuine calculated balance
                DB::table('tj_user_app')->where('id', $cId)->update([
                    'amount'   => $calculatedBalance,
                    'modifier' => Carbon::now('Asia/Kolkata')->format('Y-m-d H:i:s'),
                ]);
                $this->info("✓ Customer #{$cId} wallet balance set to ₹{$calculatedBalance}");
            }

            foreach (array_keys($affectedDriverIds) as $dId) {
                $driver = DB::table('tj_conducteur')->where('id', $dId)->first();
                if (!$driver) continue;

                $credits = DB::table('tj_conducteur_transaction')
                    ->where('id_conducteur', $dId)
                    ->where(function ($q) {
                        $q->where('type', 'credit')
                          ->orWhere('deduction_type', 1);
                    })
                    ->sum('amount');

                $debits = DB::table('tj_conducteur_transaction')
                    ->where('id_conducteur', $dId)
                    ->where(function ($q) {
                        $q->where('type', 'debit')
                          ->orWhere('deduction_type', 0);
                    })
                    ->sum('amount');

                $calculatedBalance = round(max(0, floatval($credits) - floatval($debits)), 2);
                $currentBalance = round(floatval($driver->amount ?? 0), 2);

                $this->line("Driver #{$dId} ({$driver->prenom} {$driver->nom}): Current Balance = ₹{$currentBalance}, Calculated from History = ₹{$calculatedBalance}");

                DB::table('tj_conducteur')->where('id', $dId)->update([
                    'amount'   => $calculatedBalance,
                    'modifier' => Carbon::now('Asia/Kolkata')->format('Y-m-d H:i:s'),
                ]);
                $this->info("✓ Driver #{$dId} wallet balance set to ₹{$calculatedBalance}");
            }

            $this->info("Complete! Removed {$customerDuplicatesRemoved} customer duplicate rows and {$driverDuplicatesRemoved} driver duplicate rows.");
        }

        return 0;
    }
}
