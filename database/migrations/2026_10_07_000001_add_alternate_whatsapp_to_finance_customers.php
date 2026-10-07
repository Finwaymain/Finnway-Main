<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finance_customers', function (Blueprint $table) {
            if (!Schema::hasColumn('finance_customers', 'alternate_phone')) {
                $table->string('alternate_phone', 20)->nullable()->after('phone');
            }
            if (!Schema::hasColumn('finance_customers', 'whatsapp_phone')) {
                $table->string('whatsapp_phone', 20)->nullable()->after('alternate_phone');
            }
        });
    }

    public function down(): void
    {
        Schema::table('finance_customers', function (Blueprint $table) {
            $table->dropColumn(['alternate_phone', 'whatsapp_phone']);
        });
    }
};
