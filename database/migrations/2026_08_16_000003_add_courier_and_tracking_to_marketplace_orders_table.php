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
        Schema::table('marketplace_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('marketplace_orders', 'courier_name')) {
                $table->string('courier_name')->nullable()->after('status');
            }
            if (!Schema::hasColumn('marketplace_orders', 'tracking_id')) {
                $table->string('tracking_id')->nullable()->after('courier_name');
            }
            if (!Schema::hasColumn('marketplace_orders', 'delivery_days')) {
                $table->integer('delivery_days')->default(3)->after('tracking_id');
            }
            if (!Schema::hasColumn('marketplace_orders', 'status_notes')) {
                $table->string('status_notes')->nullable()->after('delivery_days');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketplace_orders', function (Blueprint $table) {
            if (Schema::hasColumn('marketplace_orders', 'courier_name')) {
                $table->dropColumn('courier_name');
            }
            if (Schema::hasColumn('marketplace_orders', 'tracking_id')) {
                $table->dropColumn('tracking_id');
            }
            if (Schema::hasColumn('marketplace_orders', 'delivery_days')) {
                $table->dropColumn('delivery_days');
            }
            if (Schema::hasColumn('marketplace_orders', 'status_notes')) {
                $table->dropColumn('status_notes');
            }
        });
    }
};
