<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Production-safe fix for missing discount_transaction column.
 * Safe to run even if earlier migrations were skipped or partially applied.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('subscription_plans') && !Schema::hasColumn('subscription_plans', 'discount_transaction')) {
            Schema::table('subscription_plans', function (Blueprint $table) {
                $table->decimal('discount_transaction', 8, 2)->default(0);
            });
        }

        if (Schema::hasTable('consumer_premium_plans') && !Schema::hasColumn('consumer_premium_plans', 'discount_transaction')) {
            Schema::table('consumer_premium_plans', function (Blueprint $table) {
                $table->decimal('discount_transaction', 8, 2)->default(0);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('subscription_plans') && Schema::hasColumn('subscription_plans', 'discount_transaction')) {
            Schema::table('subscription_plans', function (Blueprint $table) {
                $table->dropColumn('discount_transaction');
            });
        }

        if (Schema::hasTable('consumer_premium_plans') && Schema::hasColumn('consumer_premium_plans', 'discount_transaction')) {
            Schema::table('consumer_premium_plans', function (Blueprint $table) {
                $table->dropColumn('discount_transaction');
            });
        }
    }
};
