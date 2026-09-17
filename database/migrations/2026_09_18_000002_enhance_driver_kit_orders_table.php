<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('driver_kit_orders')) {
            Schema::table('driver_kit_orders', function (Blueprint $table) {
                if (!Schema::hasColumn('driver_kit_orders', 'selected_size')) {
                    $table->string('selected_size', 20)->nullable()->after('tshirt_size');
                }
                if (!Schema::hasColumn('driver_kit_orders', 'selected_color')) {
                    $table->string('selected_color', 50)->nullable()->after('selected_size');
                }
                if (!Schema::hasColumn('driver_kit_orders', 'pincode')) {
                    $table->string('pincode', 20)->nullable()->after('shipping_address');
                }
                if (!Schema::hasColumn('driver_kit_orders', 'tracking_code')) {
                    $table->string('tracking_code', 100)->nullable()->after('tracking_number');
                }
                if (!Schema::hasColumn('driver_kit_orders', 'tracking_url')) {
                    $table->text('tracking_url')->nullable()->after('tracking_code');
                }
                if (!Schema::hasColumn('driver_kit_orders', 'courier_partner_logo')) {
                    $table->string('courier_partner_logo', 255)->nullable()->after('courier_partner');
                }
                if (!Schema::hasColumn('driver_kit_orders', 'expected_delivery_date')) {
                    $table->string('expected_delivery_date', 100)->nullable()->after('courier_partner_logo');
                }
                if (!Schema::hasColumn('driver_kit_orders', 'delivery_partner_name')) {
                    $table->string('delivery_partner_name', 150)->nullable()->after('expected_delivery_date');
                }
                if (!Schema::hasColumn('driver_kit_orders', 'delivery_partner_phone')) {
                    $table->string('delivery_partner_phone', 30)->nullable()->after('delivery_partner_name');
                }
                if (!Schema::hasColumn('driver_kit_orders', 'delivery_partner_vehicle')) {
                    $table->string('delivery_partner_vehicle', 50)->nullable()->after('delivery_partner_phone');
                }
                if (!Schema::hasColumn('driver_kit_orders', 'delivery_partner_id')) {
                    $table->string('delivery_partner_id', 50)->nullable()->after('delivery_partner_vehicle');
                }
                if (!Schema::hasColumn('driver_kit_orders', 'status_timeline')) {
                    $table->json('status_timeline')->nullable()->after('delivery_partner_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('driver_kit_orders')) {
            Schema::table('driver_kit_orders', function (Blueprint $table) {
                $table->dropColumn([
                    'selected_size', 'selected_color', 'pincode',
                    'tracking_code', 'tracking_url', 'courier_partner_logo',
                    'expected_delivery_date', 'delivery_partner_name',
                    'delivery_partner_phone', 'delivery_partner_vehicle',
                    'delivery_partner_id', 'status_timeline'
                ]);
            });
        }
    }
};
