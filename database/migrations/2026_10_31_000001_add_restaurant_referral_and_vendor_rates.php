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
        // 1. Add rate_per_restaurant to marketing_vendors table
        if (Schema::hasTable('marketing_vendors')) {
            Schema::table('marketing_vendors', function (Blueprint $table) {
                if (!Schema::hasColumn('marketing_vendors', 'rate_per_restaurant')) {
                    $table->decimal('rate_per_restaurant', 10, 2)->default(0.00)->after('rate_per_business');
                }
            });
        }

        // 2. Add referral_code and ref_by to food_owners table
        if (Schema::hasTable('food_owners')) {
            Schema::table('food_owners', function (Blueprint $table) {
                if (!Schema::hasColumn('food_owners', 'referral_code')) {
                    $table->string('referral_code', 30)->nullable()->unique();
                }
                if (!Schema::hasColumn('food_owners', 'ref_by')) {
                    $table->string('ref_by', 50)->nullable();
                }
            });
        }

        // 3. Add referral_code and ref_by to food_restaurants table
        if (Schema::hasTable('food_restaurants')) {
            Schema::table('food_restaurants', function (Blueprint $table) {
                if (!Schema::hasColumn('food_restaurants', 'referral_code')) {
                    $table->string('referral_code', 30)->nullable();
                }
                if (!Schema::hasColumn('food_restaurants', 'ref_by')) {
                    $table->string('ref_by', 50)->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('marketing_vendors')) {
            Schema::table('marketing_vendors', function (Blueprint $table) {
                if (Schema::hasColumn('marketing_vendors', 'rate_per_restaurant')) {
                    $table->dropColumn('rate_per_restaurant');
                }
            });
        }

        if (Schema::hasTable('food_owners')) {
            Schema::table('food_owners', function (Blueprint $table) {
                if (Schema::hasColumn('food_owners', 'ref_by')) {
                    $table->dropColumn('ref_by');
                }
                if (Schema::hasColumn('food_owners', 'referral_code')) {
                    $table->dropColumn('referral_code');
                }
            });
        }

        if (Schema::hasTable('food_restaurants')) {
            Schema::table('food_restaurants', function (Blueprint $table) {
                if (Schema::hasColumn('food_restaurants', 'ref_by')) {
                    $table->dropColumn('ref_by');
                }
                if (Schema::hasColumn('food_restaurants', 'referral_code')) {
                    $table->dropColumn('referral_code');
                }
            });
        }
    }
};
