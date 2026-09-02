<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Specifically repair Test User (+919669454554)
        $testUser = DB::table('tj_user_app')
            ->where('phone', 'like', '%9669454554%')
            ->first();

        if ($testUser) {
            $currentAmount = floatval($testUser->amount);
            // Calculate exact net earnings from marketplace orders
            $mpEarnings = floatval(DB::table('marketplace_orders')
                ->where(function($q) use ($testUser) {
                    $q->where('seller_id', $testUser->id)
                      ->orWhere('seller_phone', 'like', '%9669454554%');
                })
                ->where('payout_status', 'released')
                ->sum('seller_payout_amount'));

            if ($mpEarnings <= 0) {
                $mpEarnings = 142.50; // exact net seller payout from the 150 rs item
            }

            $topup = max(0, $currentAmount - $mpEarnings);
            DB::table('tj_user_app')
                ->where('id', $testUser->id)
                ->update([
                    'withdrawable_balance' => $mpEarnings,
                    'topup_balance'        => $topup,
                    'earn_amount'          => $mpEarnings,
                ]);
        }

        // 2. Also repair any other users whose topup_balance was reduced to 350
        DB::table('tj_user_app')
            ->where('topup_balance', 350.00)
            ->where('withdrawable_balance', 292.50)
            ->update([
                'topup_balance'        => 500.00,
                'withdrawable_balance' => 142.50,
                'earn_amount'          => 142.50,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
