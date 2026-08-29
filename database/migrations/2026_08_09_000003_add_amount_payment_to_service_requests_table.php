<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('service_requests')) {
            return;
        }

        Schema::table('service_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('service_requests', 'amount')) {
                $table->decimal('amount', 10, 2)->nullable()->after('status');
            }
            if (!Schema::hasColumn('service_requests', 'payment_status')) {
                $table->string('payment_status', 20)->nullable()->default('pending')->after('amount');
            }
            if (!Schema::hasColumn('service_requests', 'price_breakdown')) {
                $table->text('price_breakdown')->nullable()->after('payment_status');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('service_requests')) {
            return;
        }

        Schema::table('service_requests', function (Blueprint $table) {
            foreach (['price_breakdown', 'payment_status', 'amount'] as $column) {
                if (Schema::hasColumn('service_requests', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
