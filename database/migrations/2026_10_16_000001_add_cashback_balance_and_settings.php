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
        // 1. Add cashback_balance to tj_user_app if not present
        if (Schema::hasTable('tj_user_app')) {
            Schema::table('tj_user_app', function (Blueprint $table) {
                if (!Schema::hasColumn('tj_user_app', 'cashback_balance')) {
                    $table->decimal('cashback_balance', 10, 2)->default(0.00)->after('amount');
                }
            });
        }

        // 2. Add cashback_balance to tj_conducteur if not present
        if (Schema::hasTable('tj_conducteur')) {
            Schema::table('tj_conducteur', function (Blueprint $table) {
                if (!Schema::hasColumn('tj_conducteur', 'cashback_balance')) {
                    $table->decimal('cashback_balance', 10, 2)->default(0.00)->after('amount');
                }
            });
        }

        // 3. Add min_cashback_redeem_threshold to tj_settings if not present
        if (Schema::hasTable('tj_settings')) {
            Schema::table('tj_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('tj_settings', 'min_cashback_redeem_threshold')) {
                    $table->decimal('min_cashback_redeem_threshold', 10, 2)->default(500.00);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('tj_user_app')) {
            Schema::table('tj_user_app', function (Blueprint $table) {
                if (Schema::hasColumn('tj_user_app', 'cashback_balance')) {
                    $table->dropColumn('cashback_balance');
                }
            });
        }

        if (Schema::hasTable('tj_conducteur')) {
            Schema::table('tj_conducteur', function (Blueprint $table) {
                if (Schema::hasColumn('tj_conducteur', 'cashback_balance')) {
                    $table->dropColumn('cashback_balance');
                }
            });
        }

        if (Schema::hasTable('tj_settings')) {
            Schema::table('tj_settings', function (Blueprint $table) {
                if (Schema::hasColumn('tj_settings', 'min_cashback_redeem_threshold')) {
                    $table->dropColumn('min_cashback_redeem_threshold');
                }
            });
        }
    }
};
