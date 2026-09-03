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
        if (Schema::hasTable('marketplace_products') && !Schema::hasColumn('marketplace_products', 'seller_phone')) {
            Schema::table('marketplace_products', function (Blueprint $table) {
                $table->string('seller_phone', 50)->nullable()->after('user_type')->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('marketplace_products') && Schema::hasColumn('marketplace_products', 'seller_phone')) {
            Schema::table('marketplace_products', function (Blueprint $table) {
                $table->dropColumn('seller_phone');
            });
        }
    }
};
