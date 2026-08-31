<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketplace_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('marketplace_orders', 'tax_name')) {
                $table->string('tax_name')->nullable()->after('subtotal');
            }
            if (!Schema::hasColumn('marketplace_orders', 'tax_rate')) {
                $table->decimal('tax_rate', 5, 2)->default(0.00)->after('tax_name');
            }
            if (!Schema::hasColumn('marketplace_orders', 'tax_amount')) {
                $table->decimal('tax_amount', 10, 2)->default(0.00)->after('tax_rate');
            }
            if (!Schema::hasColumn('marketplace_orders', 'admin_commission_type')) {
                $table->string('admin_commission_type')->default('percentage')->after('total_amount');
            }
            if (!Schema::hasColumn('marketplace_orders', 'admin_commission_rate')) {
                $table->decimal('admin_commission_rate', 5, 2)->default(0.00)->after('admin_commission_type');
            }
            if (!Schema::hasColumn('marketplace_orders', 'admin_commission_amount')) {
                $table->decimal('admin_commission_amount', 10, 2)->default(0.00)->after('admin_commission_rate');
            }
            if (!Schema::hasColumn('marketplace_orders', 'seller_payout_amount')) {
                $table->decimal('seller_payout_amount', 10, 2)->default(0.00)->after('admin_commission_amount');
            }
            if (!Schema::hasColumn('marketplace_orders', 'payout_status')) {
                $table->string('payout_status')->default('pending')->after('seller_payout_amount');
            }
            if (!Schema::hasColumn('marketplace_orders', 'payout_released_at')) {
                $table->timestamp('payout_released_at')->nullable()->after('payout_status');
            }
            if (!Schema::hasColumn('marketplace_orders', 'payout_released_by')) {
                $table->unsignedBigInteger('payout_released_by')->nullable()->after('payout_released_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('marketplace_orders', function (Blueprint $table) {
            $table->dropColumn([
                'tax_name',
                'tax_rate',
                'tax_amount',
                'admin_commission_type',
                'admin_commission_rate',
                'admin_commission_amount',
                'seller_payout_amount',
                'payout_status',
                'payout_released_at',
                'payout_released_by'
            ]);
        });
    }
};
