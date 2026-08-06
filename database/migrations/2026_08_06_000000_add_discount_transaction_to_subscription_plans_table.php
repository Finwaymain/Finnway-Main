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
        if (Schema::hasTable('subscription_plans') && !Schema::hasColumn('subscription_plans', 'discount_transaction')) {
            Schema::table('subscription_plans', function (Blueprint $table) {
                $table->decimal('discount_transaction', 8, 2)->default(0)->after('discount_marketplace');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('subscription_plans') && Schema::hasColumn('subscription_plans', 'discount_transaction')) {
            Schema::table('subscription_plans', function (Blueprint $table) {
                $table->dropColumn('discount_transaction');
            });
        }
    }
};
