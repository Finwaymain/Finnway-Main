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
                if (!Schema::hasColumn('food_owners', 'image')) {
                    $table->string('image')->nullable()->after('email');
                }
                if (!Schema::hasColumn('food_owners', 'pan_number')) {
                    $table->string('pan_number', 50)->nullable()->after('image');
                }
            });
        }

        if (Schema::hasTable('food_restaurants')) {
            Schema::table('food_restaurants', function (Blueprint $table) {
                if (!Schema::hasColumn('food_restaurants', 'owner_image')) {
                    $table->string('owner_image')->nullable()->after('owner_email');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('food_owners')) {
            Schema::table('food_owners', function (Blueprint $table) {
                if (Schema::hasColumn('food_owners', 'image')) {
                    $table->dropColumn(['image', 'pan_number']);
                }
            });
        }

        if (Schema::hasTable('food_restaurants')) {
            Schema::table('food_restaurants', function (Blueprint $table) {
                if (Schema::hasColumn('food_restaurants', 'owner_image')) {
                    $table->dropColumn('owner_image');
                }
            });
        }
    }
};
