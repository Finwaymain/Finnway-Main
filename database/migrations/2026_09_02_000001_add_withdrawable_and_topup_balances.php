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
        // 1. Add withdrawable_balance and topup_balance to tj_user_app
        if (Schema::hasTable('tj_user_app')) {
            Schema::table('tj_user_app', function (Blueprint $table) {
                if (!Schema::hasColumn('tj_user_app', 'withdrawable_balance')) {
                    $table->decimal('withdrawable_balance', 12, 2)->default(0.00)->after('amount');
                }
                if (!Schema::hasColumn('tj_user_app', 'topup_balance')) {
                    $table->decimal('topup_balance', 12, 2)->default(0.00)->after('withdrawable_balance');
                }
            });
        }

        // 2. Add withdrawable_balance and topup_balance to tj_conducteur
        if (Schema::hasTable('tj_conducteur')) {
            Schema::table('tj_conducteur', function (Blueprint $table) {
                if (!Schema::hasColumn('tj_conducteur', 'withdrawable_balance')) {
                    $table->decimal('withdrawable_balance', 12, 2)->default(0.00)->after('amount');
                }
                if (!Schema::hasColumn('tj_conducteur', 'topup_balance')) {
                    $table->decimal('topup_balance', 12, 2)->default(0.00)->after('withdrawable_balance');
                }
            });
        }

        // 3. Add wallet_bucket to transaction tables
        if (Schema::hasTable('tj_transaction')) {
            Schema::table('tj_transaction', function (Blueprint $table) {
                if (!Schema::hasColumn('tj_transaction', 'wallet_bucket')) {
                    $table->string('wallet_bucket', 20)->default('topup')->after('amount');
                }
            });
        }

        if (Schema::hasTable('tj_conducteur_transaction')) {
            Schema::table('tj_conducteur_transaction', function (Blueprint $table) {
                if (!Schema::hasColumn('tj_conducteur_transaction', 'wallet_bucket')) {
                    $table->string('wallet_bucket', 20)->default('earning')->after('amount');
                }
            });
        }

        // 4. Initialize existing balances:
        // For drivers: existing balance is primarily earned through rides and services
        DB::statement("UPDATE tj_conducteur SET withdrawable_balance = GREATEST(0, amount), topup_balance = 0 WHERE withdrawable_balance = 0 AND topup_balance = 0 AND amount > 0");

        // For users: existing earnings are withdrawable, remainder is top-up
        DB::statement("UPDATE tj_user_app SET withdrawable_balance = LEAST(GREATEST(0, amount), GREATEST(0, earn_amount)), topup_balance = GREATEST(0, amount - LEAST(GREATEST(0, amount), GREATEST(0, earn_amount))) WHERE withdrawable_balance = 0 AND topup_balance = 0 AND amount > 0");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('tj_user_app')) {
            Schema::table('tj_user_app', function (Blueprint $table) {
                if (Schema::hasColumn('tj_user_app', 'withdrawable_balance')) {
                    $table->dropColumn('withdrawable_balance');
                }
                if (Schema::hasColumn('tj_user_app', 'topup_balance')) {
                    $table->dropColumn('topup_balance');
                }
            });
        }

        if (Schema::hasTable('tj_conducteur')) {
            Schema::table('tj_conducteur', function (Blueprint $table) {
                if (Schema::hasColumn('tj_conducteur', 'withdrawable_balance')) {
                    $table->dropColumn('withdrawable_balance');
                }
                if (Schema::hasColumn('tj_conducteur', 'topup_balance')) {
                    $table->dropColumn('topup_balance');
                }
            });
        }

        if (Schema::hasTable('tj_transaction')) {
            Schema::table('tj_transaction', function (Blueprint $table) {
                if (Schema::hasColumn('tj_transaction', 'wallet_bucket')) {
                    $table->dropColumn('wallet_bucket');
                }
            });
        }

        if (Schema::hasTable('tj_conducteur_transaction')) {
            Schema::table('tj_conducteur_transaction', function (Blueprint $table) {
                if (Schema::hasColumn('tj_conducteur_transaction', 'wallet_bucket')) {
                    $table->dropColumn('wallet_bucket');
                }
            });
        }
    }
};
