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
        if (Schema::hasTable('service_requests')) {
            Schema::table('service_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('service_requests', 'tax')) {
                    $table->text('tax')->nullable()->after('amount');
                }
                if (!Schema::hasColumn('service_requests', 'tax_amount')) {
                    $table->decimal('tax_amount', 10, 2)->default(0.00)->after('tax');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('service_requests')) {
            Schema::table('service_requests', function (Blueprint $table) {
                if (Schema::hasColumn('service_requests', 'tax_amount')) {
                    $table->dropColumn('tax_amount');
                }
                if (Schema::hasColumn('service_requests', 'tax')) {
                    $table->dropColumn('tax');
                }
            });
        }
    }
};
