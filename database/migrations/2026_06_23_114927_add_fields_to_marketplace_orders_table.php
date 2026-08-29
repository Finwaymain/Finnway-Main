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
            $table->text('delivery_address')->nullable()->after('total_amount');
            $table->string('phone')->nullable()->after('delivery_address');
            $table->integer('delivery_days')->nullable()->after('status');
            $table->string('status_notes')->nullable()->after('delivery_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketplace_orders', function (Blueprint $table) {
            $table->dropColumn(['delivery_address', 'phone', 'delivery_days', 'status_notes']);
        });
    }
};
