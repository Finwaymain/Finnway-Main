<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProcessWalletGrowth extends Command
{
    protected $signature = 'wallet:growth';
    protected $description = 'Process automated daily/weekly/monthly wallet growth interest/bonus crediting';

    public function handle()
    {
        $this->info('Starting Wallet Growth processing...');

        $growthEnabled = DB::table('api_key_settings')->where('key_name', 'wallet_growth_enabled')->value('key_value');
        if ($growthEnabled === 'false') {
            $this->info('Wallet Growth is currently disabled.');
            return 0;
        }

        $rate = floatval(DB::table('api_key_settings')->where('key_name', 'wallet_growth_rate')->value('key_value') ?? 0.10);
        $mode = DB::table('api_key_settings')->where('key_name', 'wallet_growth_mode')->value('key_value') ?? 'percentage';

        $processedCount = 0;

        // Process Consumer Users (tj_user_app)
        if (Schema::hasTable('tj_user_app') && Schema::hasColumn('tj_user_app', 'amount')) {
            $consumers = DB::table('tj_user_app')->where('amount', '>', 0)->get();
            foreach ($consumers as $user) {
                $growthAmount = ($mode === 'percentage') ? round(($user->amount * ($rate / 100)), 2) : $rate;
                if ($growthAmount > 0) {
                    DB::table('tj_user_app')->where('id', $user->id)->increment('amount', $growthAmount);

                    if (Schema::hasTable('tbl_earning')) {
                        DB::table('tbl_earning')->insert([
                            'earn_wallet' => $growthAmount,
                            'description' => 'Wallet Growth Credit',
                            'date'        => now()->format('Y-m-d'),
                            'time'        => now()->format('H:i:s'),
                            'created_at'  => now(),
                            'updated_at'  => now(),
                        ]);
                    }

                    $processedCount++;
                }
            }
        }

        // Process Business Users (tj_conducteur)
        if (Schema::hasTable('tj_conducteur') && Schema::hasColumn('tj_conducteur', 'amount')) {
            $drivers = DB::table('tj_conducteur')->where('amount', '>', 0)->get();
            foreach ($drivers as $driver) {
                $growthAmount = ($mode === 'percentage') ? round(($driver->amount * ($rate / 100)), 2) : $rate;
                if ($growthAmount > 0) {
                    DB::table('tj_conducteur')->where('id', $driver->id)->increment('amount', $growthAmount);

                    if (Schema::hasTable('tbl_earning')) {
                        DB::table('tbl_earning')->insert([
                            'earn_wallet' => $growthAmount,
                            'description' => 'Wallet Growth Credit',
                            'date'        => now()->format('Y-m-d'),
                            'time'        => now()->format('H:i:s'),
                            'created_at'  => now(),
                            'updated_at'  => now(),
                        ]);
                    }

                    $processedCount++;
                }
            }
        }

        $this->info("Wallet Growth completed! Credited {$processedCount} users.");
        return 0;
    }
}
