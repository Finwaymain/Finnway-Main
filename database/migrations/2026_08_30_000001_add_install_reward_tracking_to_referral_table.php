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
        if (Schema::hasTable('referral')) {
            Schema::table('referral', function (Blueprint $table) {
                if (!Schema::hasColumn('referral', 'app_install_reward_paid')) {
                    $table->boolean('app_install_reward_paid')->default(0)->after('code_used');
                }
                if (!Schema::hasColumn('referral', 'app_install_reward_amount')) {
                    $table->decimal('app_install_reward_amount', 10, 2)->nullable()->after('app_install_reward_paid');
                }
                if (!Schema::hasColumn('referral', 'app_install_reward_date')) {
                    $table->dateTime('app_install_reward_date')->nullable()->after('app_install_reward_amount');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('referral')) {
            Schema::table('referral', function (Blueprint $table) {
                if (Schema::hasColumn('referral', 'app_install_reward_date')) {
                    $table->dropColumn('app_install_reward_date');
                }
                if (Schema::hasColumn('referral', 'app_install_reward_amount')) {
                    $table->dropColumn('app_install_reward_amount');
                }
                if (Schema::hasColumn('referral', 'app_install_reward_paid')) {
                    $table->dropColumn('app_install_reward_paid');
                }
            });
        }
    }
};
