<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('marketplace_orders')) {
            Schema::table('marketplace_orders', function (Blueprint $table) {
                if (!Schema::hasColumn('marketplace_orders', 'order_number')) {
                    $table->string('order_number')->nullable()->unique()->after('id');
                }
                if (!Schema::hasColumn('marketplace_orders', 'purchase_id')) {
                    $table->string('purchase_id')->nullable()->index()->after('order_number');
                }
                if (!Schema::hasColumn('marketplace_orders', 'buyer_phone')) {
                    $table->string('buyer_phone')->nullable()->index()->after('phone');
                }
                if (!Schema::hasColumn('marketplace_orders', 'seller_phone')) {
                    $table->string('seller_phone')->nullable()->index()->after('buyer_phone');
                }
            });

            // Backfill existing orders with unique order_number & purchase_id
            $orders = DB::table('marketplace_orders')->whereNull('order_number')->orWhere('order_number', '')->get();
            foreach ($orders as $ord) {
                $uniqueNum = 'FW-ORD-' . str_pad($ord->id, 5, '0', STR_PAD_LEFT);
                $purchaseId = 'FWMP-' . date('Ymd', strtotime($ord->created_at ?? 'now')) . '-' . str_pad($ord->id, 4, '0', STR_PAD_LEFT);
                DB::table('marketplace_orders')->where('id', $ord->id)->update([
                    'order_number' => $uniqueNum,
                    'purchase_id'  => $purchaseId,
                    'buyer_phone'  => $ord->phone ?? '',
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('marketplace_orders')) {
            Schema::table('marketplace_orders', function (Blueprint $table) {
                if (Schema::hasColumn('marketplace_orders', 'order_number')) {
                    $table->dropColumn('order_number');
                }
                if (Schema::hasColumn('marketplace_orders', 'purchase_id')) {
                    $table->dropColumn('purchase_id');
                }
                if (Schema::hasColumn('marketplace_orders', 'buyer_phone')) {
                    $table->dropColumn('buyer_phone');
                }
                if (Schema::hasColumn('marketplace_orders', 'seller_phone')) {
                    $table->dropColumn('seller_phone');
                }
            });
        }
    }
};
