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
        Schema::table('marketplace_products', function (Blueprint $table) {
            if (!Schema::hasColumn('marketplace_products', 'brand_name')) {
                $table->string('brand_name')->nullable()->after('title');
            }
            if (!Schema::hasColumn('marketplace_products', 'condition_detail')) {
                $table->string('condition_detail')->nullable()->after('condition');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketplace_products', function (Blueprint $table) {
            if (Schema::hasColumn('marketplace_products', 'brand_name')) {
                $table->dropColumn('brand_name');
            }
            if (Schema::hasColumn('marketplace_products', 'condition_detail')) {
                $table->dropColumn('condition_detail');
            }
        });
    }
};
