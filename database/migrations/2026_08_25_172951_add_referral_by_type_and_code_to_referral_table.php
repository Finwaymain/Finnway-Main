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
        Schema::table('referral', function (Blueprint $table) {
            if (!Schema::hasColumn('referral', 'referral_by_type')) {
                $table->string('referral_by_type', 50)->nullable()->after('referral_by_id');
            }
            if (!Schema::hasColumn('referral', 'referral_by_code')) {
                $table->string('referral_by_code', 50)->nullable()->after('referral_by_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('referral', function (Blueprint $table) {
            if (Schema::hasColumn('referral', 'referral_by_code')) {
                $table->dropColumn('referral_by_code');
            }
            if (Schema::hasColumn('referral', 'referral_by_type')) {
                $table->dropColumn('referral_by_type');
            }
        });
    }
};
