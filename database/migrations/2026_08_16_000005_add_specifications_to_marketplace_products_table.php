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
        if (Schema::hasTable('marketplace_products')) {
            Schema::table('marketplace_products', function (Blueprint $table) {
                if (!Schema::hasColumn('marketplace_products', 'specifications')) {
                    $table->text('specifications')->nullable()->after('condition_detail');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('marketplace_products')) {
            Schema::table('marketplace_products', function (Blueprint $table) {
                if (Schema::hasColumn('marketplace_products', 'specifications')) {
                    $table->dropColumn('specifications');
                }
            });
        }
    }
};
