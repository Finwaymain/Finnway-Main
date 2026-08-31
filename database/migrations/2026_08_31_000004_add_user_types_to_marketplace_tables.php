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
        if (Schema::hasTable('marketplace_products') && !Schema::hasColumn('marketplace_products', 'user_type')) {
            Schema::table('marketplace_products', function (Blueprint $table) {
                $table->string('user_type')->default('customer')->after('user_id')->index();
            });
        }

        if (Schema::hasTable('marketplace_orders')) {
            Schema::table('marketplace_orders', function (Blueprint $table) {
                if (!Schema::hasColumn('marketplace_orders', 'buyer_type')) {
                    $table->string('buyer_type')->default('customer')->after('user_id')->index();
                }
                if (!Schema::hasColumn('marketplace_orders', 'seller_type')) {
                    $table->string('seller_type')->default('customer')->after('seller_id')->index();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('marketplace_products') && Schema::hasColumn('marketplace_products', 'user_type')) {
            Schema::table('marketplace_products', function (Blueprint $table) {
                $table->dropColumn('user_type');
            });
        }

        if (Schema::hasTable('marketplace_orders')) {
            Schema::table('marketplace_orders', function (Blueprint $table) {
                if (Schema::hasColumn('marketplace_orders', 'buyer_type')) {
                    $table->dropColumn('buyer_type');
                }
                if (Schema::hasColumn('marketplace_orders', 'seller_type')) {
                    $table->dropColumn('seller_type');
                }
            });
        }
    }
};
