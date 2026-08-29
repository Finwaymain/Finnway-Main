<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('service_reward_configs')) {
            $defaultRules = [
                [
                    'service_name'   => 'App Install',
                    'service_slug'   => 'app_install',
                    'reward_mode'    => 'flat',
                    'business_value' => '10',
                    'customer_value' => '10',
                    'is_active'      => true,
                ],
                [
                    'service_name'   => 'Registration',
                    'service_slug'   => 'registration',
                    'reward_mode'    => 'flat',
                    'business_value' => '10',
                    'customer_value' => '10',
                    'is_active'      => true,
                ],
                [
                    'service_name'   => 'Marketplace Order',
                    'service_slug'   => 'marketplace_order',
                    'reward_mode'    => 'percentage',
                    'business_value' => '2%',
                    'customer_value' => '1%',
                    'is_active'      => true,
                ],
                [
                    'service_name'   => 'Medical Cashback Card',
                    'service_slug'   => 'medical_cashback',
                    'reward_mode'    => 'flat',
                    'business_value' => '25',
                    'customer_value' => '25',
                    'is_active'      => true,
                ],
                [
                    'service_name'   => 'Service Booking',
                    'service_slug'   => 'service_booking',
                    'reward_mode'    => 'percentage',
                    'business_value' => '2%',
                    'customer_value' => '2%',
                    'is_active'      => true,
                ],
            ];

            foreach ($defaultRules as $rule) {
                DB::table('service_reward_configs')->updateOrInsert(
                    ['service_slug' => $rule['service_slug']],
                    array_merge($rule, ['created_at' => now(), 'updated_at' => now()])
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
