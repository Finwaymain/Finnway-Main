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
        Schema::table('tj_tax', function (Blueprint $table) {
            // Stores which payment methods this tax applies to.
            // Comma-separated values: "cash", "upi", "wallet", "online"
            // Default "cash,upi,wallet,online" = applies to ALL methods (backward-compatible)
            $table->string('applicable_on')->nullable()->default('cash,upi,wallet,online')->after('country');
        });

        // Seed existing records that have no value
        DB::table('tj_tax')->whereNull('applicable_on')->update(['applicable_on' => 'cash,upi,wallet,online']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tj_tax', function (Blueprint $table) {
            $table->dropColumn('applicable_on');
        });
    }
};
