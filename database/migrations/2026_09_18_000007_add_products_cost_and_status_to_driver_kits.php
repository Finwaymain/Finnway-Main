<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('driver_kits')) {
            Schema::table('driver_kits', function (Blueprint $table) {
                if (!Schema::hasColumn('driver_kits', 'cost_price')) {
                    $table->decimal('cost_price', 10, 2)->default(0.00)->after('mrp');
                }
                if (!Schema::hasColumn('driver_kits', 'products')) {
                    $table->json('products')->nullable()->after('items_included');
                }
                if (!Schema::hasColumn('driver_kits', 'status')) {
                    $table->string('status', 20)->default('published')->after('is_active');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('driver_kits')) {
            Schema::table('driver_kits', function (Blueprint $table) {
                if (Schema::hasColumn('driver_kits', 'cost_price')) {
                    $table->dropColumn('cost_price');
                }
                if (Schema::hasColumn('driver_kits', 'products')) {
                    $table->dropColumn('products');
                }
                if (Schema::hasColumn('driver_kits', 'status')) {
                    $table->dropColumn('status');
                }
            });
        }
    }
};
