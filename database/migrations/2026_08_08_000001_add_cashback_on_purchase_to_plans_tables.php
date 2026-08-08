<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('subscription_plans') && ! Schema::hasColumn('subscription_plans', 'cashback_on_purchase')) {
            Schema::table('subscription_plans', function (Blueprint $table) {
                $table->decimal('cashback_on_purchase', 10, 2)->default(0)->after('receiver_cashback_value');
            });
        }

        if (Schema::hasTable('consumer_premium_plans') && ! Schema::hasColumn('consumer_premium_plans', 'cashback_on_purchase')) {
            Schema::table('consumer_premium_plans', function (Blueprint $table) {
                $table->decimal('cashback_on_purchase', 10, 2)->default(0)->after('receiver_cashback_value');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('subscription_plans') && Schema::hasColumn('subscription_plans', 'cashback_on_purchase')) {
            Schema::table('subscription_plans', function (Blueprint $table) {
                $table->dropColumn('cashback_on_purchase');
            });
        }

        if (Schema::hasTable('consumer_premium_plans') && Schema::hasColumn('consumer_premium_plans', 'cashback_on_purchase')) {
            Schema::table('consumer_premium_plans', function (Blueprint $table) {
                $table->dropColumn('cashback_on_purchase');
            });
        }
    }
};
