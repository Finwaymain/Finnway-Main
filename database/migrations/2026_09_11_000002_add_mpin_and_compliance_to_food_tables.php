<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('food_owners')) {
            Schema::table('food_owners', function (Blueprint $table) {
                if (!Schema::hasColumn('food_owners', 'mpin')) {
                    $table->string('mpin', 100)->nullable()->after('password');
                }
            });
        }

        if (Schema::hasTable('food_restaurants')) {
            Schema::table('food_restaurants', function (Blueprint $table) {
                if (!Schema::hasColumn('food_restaurants', 'doc_status')) {
                    $table->json('doc_status')->nullable()->after('rejection_reason');
                }
                if (!Schema::hasColumn('food_restaurants', 'doc_notes')) {
                    $table->text('doc_notes')->nullable()->after('doc_status');
                }
                if (!Schema::hasColumn('food_restaurants', 'pure_veg')) {
                    $table->boolean('pure_veg')->default(false)->after('dine_in_available');
                }
                if (!Schema::hasColumn('food_restaurants', 'custom_commission_rate')) {
                    $table->decimal('custom_commission_rate', 5, 2)->nullable()->after('approved_by');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('food_owners')) {
            Schema::table('food_owners', function (Blueprint $table) {
                if (Schema::hasColumn('food_owners', 'mpin')) {
                    $table->dropColumn('mpin');
                }
            });
        }

        if (Schema::hasTable('food_restaurants')) {
            Schema::table('food_restaurants', function (Blueprint $table) {
                if (Schema::hasColumn('food_restaurants', 'doc_status')) {
                    $table->dropColumn(['doc_status', 'doc_notes', 'pure_veg', 'custom_commission_rate']);
                }
            });
        }
    }
};
