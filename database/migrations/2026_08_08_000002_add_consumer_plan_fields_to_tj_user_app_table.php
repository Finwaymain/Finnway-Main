<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tj_user_app')) {
            Schema::table('tj_user_app', function (Blueprint $table) {
                if (! Schema::hasColumn('tj_user_app', 'consumer_plan_id')) {
                    $table->unsignedBigInteger('consumer_plan_id')->nullable();
                }
                if (! Schema::hasColumn('tj_user_app', 'consumer_plan_expiry_date')) {
                    $table->dateTime('consumer_plan_expiry_date')->nullable();
                }
                if (! Schema::hasColumn('tj_user_app', 'consumer_plan')) {
                    $table->longText('consumer_plan')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tj_user_app')) {
            Schema::table('tj_user_app', function (Blueprint $table) {
                if (Schema::hasColumn('tj_user_app', 'consumer_plan')) {
                    $table->dropColumn('consumer_plan');
                }
                if (Schema::hasColumn('tj_user_app', 'consumer_plan_expiry_date')) {
                    $table->dropColumn('consumer_plan_expiry_date');
                }
                if (Schema::hasColumn('tj_user_app', 'consumer_plan_id')) {
                    $table->dropColumn('consumer_plan_id');
                }
            });
        }
    }
};
