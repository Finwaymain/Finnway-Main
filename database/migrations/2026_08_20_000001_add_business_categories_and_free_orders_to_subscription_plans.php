<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            if (!Schema::hasColumn('subscription_plans', 'business_categories')) {
                $table->json('business_categories')->nullable()->after('service_permissions');
            }
            if (!Schema::hasColumn('subscription_plans', 'category_free_orders')) {
                $table->json('category_free_orders')->nullable()->after('business_categories');
            }
        });
    }

    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            if (Schema::hasColumn('subscription_plans', 'business_categories')) {
                $table->dropColumn('business_categories');
            }
            if (Schema::hasColumn('subscription_plans', 'category_free_orders')) {
                $table->dropColumn('category_free_orders');
            }
        });
    }
};