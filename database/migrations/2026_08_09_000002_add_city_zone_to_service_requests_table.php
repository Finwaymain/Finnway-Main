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
            if (!Schema::hasColumn('service_requests', 'city')) {
                $table->string('city')->nullable()->after('service_address');
            }
            if (!Schema::hasColumn('service_requests', 'zone_id')) {
                $table->unsignedBigInteger('zone_id')->nullable()->after('city');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('service_requests')) {
            return;
        }

        Schema::table('service_requests', function (Blueprint $table) {
            if (Schema::hasColumn('service_requests', 'zone_id')) {
                $table->dropColumn('zone_id');
            }
            if (Schema::hasColumn('service_requests', 'city')) {
                $table->dropColumn('city');
            }
        });
    }
};
