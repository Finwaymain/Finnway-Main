<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('service_requests') && !Schema::hasColumn('service_requests', 'service_address')) {
            Schema::table('service_requests', function (Blueprint $table) {
                $table->text('service_address')->nullable()->after('address_type');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('service_requests') && Schema::hasColumn('service_requests', 'service_address')) {
            Schema::table('service_requests', function (Blueprint $table) {
                $table->dropColumn('service_address');
            });
        }
    }
};
