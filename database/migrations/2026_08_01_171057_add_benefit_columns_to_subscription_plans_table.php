<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            // Plan tier
            $table->enum('plan_tier', ['basic', 'professional', 'premium_plus'])->default('basic')->after('type');

            // Cashback
            $table->enum('sender_cashback_type', ['percentage', 'flat'])->default('percentage')->after('plan_tier');
            $table->decimal('sender_cashback_value', 8, 2)->default(0)->after('sender_cashback_type');
            $table->enum('receiver_cashback_type', ['percentage', 'flat'])->default('percentage')->after('sender_cashback_value');
            $table->decimal('receiver_cashback_value', 8, 2)->default(0)->after('receiver_cashback_type');

            // Service Discounts
            $table->decimal('discount_home_service', 5, 2)->default(0)->after('receiver_cashback_value');
            $table->decimal('discount_travel', 5, 2)->default(0)->after('discount_home_service');
            $table->decimal('discount_hotel', 5, 2)->default(0)->after('discount_travel');
            $table->decimal('discount_food', 5, 2)->default(0)->after('discount_hotel');
            $table->decimal('discount_medical', 5, 2)->default(0)->after('discount_food');
            $table->decimal('discount_marketplace', 5, 2)->default(0)->after('discount_medical');
            $table->decimal('shopping_discount', 5, 2)->default(0)->after('discount_marketplace');

            // Free Ride / Booking Quota
            $table->integer('free_ride_limit')->default(0)->after('shopping_discount');
            $table->enum('free_ride_reset', ['monthly', 'quarterly', 'yearly'])->default('monthly')->after('free_ride_limit');

            // Wallet Increment / Decrement
            $table->decimal('wallet_increment_value', 8, 2)->default(0)->after('free_ride_reset');
            $table->enum('wallet_increment_period', ['daily', 'weekly', 'monthly'])->default('daily')->after('wallet_increment_value');
            $table->decimal('wallet_decrement_value', 8, 2)->default(0)->after('wallet_increment_period');
            $table->enum('wallet_decrement_period', ['daily', 'weekly', 'monthly'])->default('daily')->after('wallet_decrement_value');

            // Referral Bonus
            $table->enum('referral_bonus_type', ['percentage', 'flat'])->default('flat')->after('wallet_decrement_period');
            $table->decimal('referral_bonus_value', 8, 2)->default(0)->after('referral_bonus_type');

            // Loan
            $table->boolean('loan_enabled')->default(false)->after('referral_bonus_value');
            $table->decimal('loan_max_amount', 12, 2)->default(0)->after('loan_enabled');
            $table->boolean('interest_free_loan_enabled')->default(false)->after('loan_max_amount');
            $table->decimal('interest_free_loan_limit', 12, 2)->default(0)->after('interest_free_loan_enabled');

            // Service Permission Matrix (JSON: which services this tier can access)
            $table->json('service_permissions')->nullable()->after('interest_free_loan_limit');
        });
    }

    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn([
                'plan_tier',
                'sender_cashback_type', 'sender_cashback_value',
                'receiver_cashback_type', 'receiver_cashback_value',
                'discount_home_service', 'discount_travel', 'discount_hotel',
                'discount_food', 'discount_medical', 'discount_marketplace',
                'shopping_discount',
                'free_ride_limit', 'free_ride_reset',
                'wallet_increment_value', 'wallet_increment_period',
                'wallet_decrement_value', 'wallet_decrement_period',
                'referral_bonus_type', 'referral_bonus_value',
                'loan_enabled', 'loan_max_amount',
                'interest_free_loan_enabled', 'interest_free_loan_limit',
                'service_permissions',
            ]);
        });
    }
};
