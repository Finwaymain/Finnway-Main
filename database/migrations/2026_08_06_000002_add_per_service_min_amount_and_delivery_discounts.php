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
        $tables = ['consumer_premium_plans', 'subscription_plans'];

        foreach ($tables as $tbl) {
            if (Schema::hasTable($tbl)) {
                Schema::table($tbl, function (Blueprint $table) use ($tbl) {
                    $minAmountCols = [
                        'min_amount_hotel',
                        'min_amount_home_service',
                        'min_amount_shopping',
                        'min_amount_food',
                        'min_amount_travel',
                        'min_amount_medical',
                        'min_amount_cab'
                    ];
                    foreach ($minAmountCols as $col) {
                        if (!Schema::hasColumn($tbl, $col)) {
                            $table->decimal($col, 8, 2)->default(0);
                        }
                    }

                    $deliveryDiscountCols = [
                        'discount_delivery_food',
                        'discount_delivery_shopping',
                        'discount_delivery_home_service',
                        'discount_delivery_medical',
                        'discount_delivery_parcel'
                    ];
                    foreach ($deliveryDiscountCols as $col) {
                        if (!Schema::hasColumn($tbl, $col)) {
                            $table->decimal($col, 8, 2)->default(0);
                        }
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['consumer_premium_plans', 'subscription_plans'];
        $cols = [
            'min_amount_hotel', 'min_amount_home_service', 'min_amount_shopping',
            'min_amount_food', 'min_amount_travel', 'min_amount_medical', 'min_amount_cab',
            'discount_delivery_food', 'discount_delivery_shopping',
            'discount_delivery_home_service', 'discount_delivery_medical', 'discount_delivery_parcel'
        ];

        foreach ($tables as $tbl) {
            if (Schema::hasTable($tbl)) {
                Schema::table($tbl, function (Blueprint $table) use ($tbl, $cols) {
                    foreach ($cols as $col) {
                        if (Schema::hasColumn($tbl, $col)) {
                            $table->dropColumn($col);
                        }
                    }
                });
            }
        }
    }
};
