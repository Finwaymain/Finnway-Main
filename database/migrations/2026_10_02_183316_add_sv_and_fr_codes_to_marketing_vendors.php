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
        Schema::table('marketing_vendors', function (Blueprint $table) {
            if (!Schema::hasColumn('marketing_vendors', 'sub_vendor_code')) {
                $table->string('sub_vendor_code', 30)->nullable()->index()->after('vendor_code');
            }
            if (!Schema::hasColumn('marketing_vendors', 'freelancer_code')) {
                $table->string('freelancer_code', 30)->nullable()->index()->after('sub_vendor_code');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_vendors', function (Blueprint $table) {
            if (Schema::hasColumn('marketing_vendors', 'sub_vendor_code')) {
                $table->dropColumn('sub_vendor_code');
            }
            if (Schema::hasColumn('marketing_vendors', 'freelancer_code')) {
                $table->dropColumn('freelancer_code');
            }
        });
    }
};
