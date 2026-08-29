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
                if (!Schema::hasColumn('marketplace_products', 'seller_city')) {
                    $table->string('seller_city')->nullable()->after('delivery_type');
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
                if (Schema::hasColumn('marketplace_products', 'seller_city')) {
                    $table->dropColumn('seller_city');
                }
            });
        }
    }
};
