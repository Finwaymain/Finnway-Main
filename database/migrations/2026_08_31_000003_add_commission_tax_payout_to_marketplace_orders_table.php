<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketplace_orders', function (Blueprint $table) {
            // Ensure core tracking and billing columns exist safely
            if (!Schema::hasColumn('marketplace_orders', 'seller_id')) {
                $table->unsignedBigInteger('seller_id')->nullable();
            }
            if (!Schema::hasColumn('marketplace_orders', 'subtotal')) {
                $table->decimal('subtotal', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('marketplace_orders', 'delivery_charge')) {
                $table->decimal('delivery_charge', 10, 2)->default(0.00);
            }
            if (!Schema::hasColumn('marketplace_orders', 'payment_method')) {
                $table->string('payment_method')->default('wallet');
            }
            if (!Schema::hasColumn('marketplace_orders', 'payment_status')) {
                $table->string('payment_status')->default('pending');
            }
            if (!Schema::hasColumn('marketplace_orders', 'txn_id')) {
                $table->string('txn_id')->nullable();
            }
            if (!Schema::hasColumn('marketplace_orders', 'contact_name')) {
                $table->string('contact_name')->nullable();
            }
            if (!Schema::hasColumn('marketplace_orders', 'city')) {
                $table->string('city')->nullable();
            }
            if (!Schema::hasColumn('marketplace_orders', 'pincode')) {
                $table->string('pincode')->nullable();
            }
            if (!Schema::hasColumn('marketplace_orders', 'courier_name')) {
                $table->string('courier_name')->nullable();
            }
            if (!Schema::hasColumn('marketplace_orders', 'tracking_id')) {
                $table->string('tracking_id')->nullable();
            }

            // Tax fields
            if (!Schema::hasColumn('marketplace_orders', 'tax_name')) {
                $table->string('tax_name')->nullable();
            }
            if (!Schema::hasColumn('marketplace_orders', 'tax_rate')) {
                $table->decimal('tax_rate', 5, 2)->default(0.00);
            }
            if (!Schema::hasColumn('marketplace_orders', 'tax_amount')) {
                $table->decimal('tax_amount', 10, 2)->default(0.00);
            }

            // Commission & Escrow Payout fields
            if (!Schema::hasColumn('marketplace_orders', 'admin_commission_type')) {
                $table->string('admin_commission_type')->default('percentage');
            }
            if (!Schema::hasColumn('marketplace_orders', 'admin_commission_rate')) {
                $table->decimal('admin_commission_rate', 5, 2)->default(0.00);
            }
            if (!Schema::hasColumn('marketplace_orders', 'admin_commission_amount')) {
                $table->decimal('admin_commission_amount', 10, 2)->default(0.00);
            }
            if (!Schema::hasColumn('marketplace_orders', 'seller_payout_amount')) {
                $table->decimal('seller_payout_amount', 10, 2)->default(0.00);
            }
            if (!Schema::hasColumn('marketplace_orders', 'payout_status')) {
                $table->string('payout_status')->default('pending');
            }
            if (!Schema::hasColumn('marketplace_orders', 'payout_released_at')) {
                $table->timestamp('payout_released_at')->nullable();
            }
            if (!Schema::hasColumn('marketplace_orders', 'payout_released_by')) {
                $table->unsignedBigInteger('payout_released_by')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('marketplace_orders', function (Blueprint $table) {
            $colsToDrop = [];
            $allCols = [
                'tax_name', 'tax_rate', 'tax_amount',
                'admin_commission_type', 'admin_commission_rate', 'admin_commission_amount',
                'seller_payout_amount', 'payout_status', 'payout_released_at', 'payout_released_by'
            ];
            foreach ($allCols as $col) {
                if (Schema::hasColumn('marketplace_orders', $col)) {
                    $colsToDrop[] = $col;
                }
            }
            if (!empty($colsToDrop)) {
                $table->dropColumn($colsToDrop);
            }
        });
    }
};
