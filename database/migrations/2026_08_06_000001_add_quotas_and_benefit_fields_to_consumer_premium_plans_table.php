<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('consumer_premium_plans')) {
            Schema::table('consumer_premium_plans', function (Blueprint $table) {
                if (!Schema::hasColumn('consumer_premium_plans', 'free_shipping_count')) {
                    $table->integer('free_shipping_count')->default(0);
                }
                if (!Schema::hasColumn('consumer_premium_plans', 'free_ride_limit')) {
                    $table->integer('free_ride_limit')->default(0);
                }
                if (!Schema::hasColumn('consumer_premium_plans', 'quota_hotel_booking')) {
                    $table->integer('quota_hotel_booking')->default(0);
                }
                if (!Schema::hasColumn('consumer_premium_plans', 'quota_home_service')) {
                    $table->integer('quota_home_service')->default(0);
                }
                if (!Schema::hasColumn('consumer_premium_plans', 'quota_shopping')) {
                    $table->integer('quota_shopping')->default(0);
                }
                if (!Schema::hasColumn('consumer_premium_plans', 'quota_food')) {
                    $table->integer('quota_food')->default(0);
                }
                if (!Schema::hasColumn('consumer_premium_plans', 'quota_medical')) {
                    $table->integer('quota_medical')->default(0);
                }
                if (!Schema::hasColumn('consumer_premium_plans', 'min_order_amount_benefit')) {
                    $table->decimal('min_order_amount_benefit', 8, 2)->default(0);
                }
                if (!Schema::hasColumn('consumer_premium_plans', 'wallet_monthly_bonus')) {
                    $table->decimal('wallet_monthly_bonus', 8, 2)->default(0);
                }
                if (!Schema::hasColumn('consumer_premium_plans', 'annual_voucher_value')) {
                    $table->decimal('annual_voucher_value', 8, 2)->default(0);
                }
                if (!Schema::hasColumn('consumer_premium_plans', 'discount_delivery')) {
                    $table->decimal('discount_delivery', 8, 2)->default(0);
                }
                if (!Schema::hasColumn('consumer_premium_plans', 'discount_transaction')) {
                    $table->decimal('discount_transaction', 8, 2)->default(0);
                }
                if (!Schema::hasColumn('consumer_premium_plans', 'loan_enabled')) {
                    $table->boolean('loan_enabled')->default(false);
                }
                if (!Schema::hasColumn('consumer_premium_plans', 'loan_max_amount')) {
                    $table->decimal('loan_max_amount', 8, 2)->default(0);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('consumer_premium_plans')) {
            Schema::table('consumer_premium_plans', function (Blueprint $table) {
                $columns = [
                    'free_shipping_count', 'free_ride_limit', 'quota_hotel_booking',
                    'quota_home_service', 'quota_shopping', 'quota_food', 'quota_medical',
                    'min_order_amount_benefit', 'wallet_monthly_bonus', 'annual_voucher_value',
                    'discount_delivery', 'discount_transaction', 'loan_enabled', 'loan_max_amount'
                ];
                foreach ($columns as $col) {
                    if (Schema::hasColumn('consumer_premium_plans', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
