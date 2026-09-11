<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // food_cuisines — admin-managed master list
        if (!Schema::hasTable('food_cuisines')) {
            Schema::create('food_cuisines', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();        // e.g. "North Indian"
                $table->string('slug')->unique();        // e.g. "north-indian"
                $table->string('icon')->nullable();      // emoji or icon name
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });

            // Seed default cuisines
            $defaults = [
                ['name' => 'North Indian',      'slug' => 'north-indian',      'icon' => '🍛'],
                ['name' => 'South Indian',      'slug' => 'south-indian',      'icon' => '🥘'],
                ['name' => 'Chinese',           'slug' => 'chinese',           'icon' => '🍜'],
                ['name' => 'Continental',       'slug' => 'continental',       'icon' => '🍝'],
                ['name' => 'Italian',           'slug' => 'italian',           'icon' => '🍕'],
                ['name' => 'Mughlai',           'slug' => 'mughlai',           'icon' => '🍖'],
                ['name' => 'Biryani',           'slug' => 'biryani',           'icon' => '🍚'],
                ['name' => 'Street Food',       'slug' => 'street-food',       'icon' => '🌮'],
                ['name' => 'Fast Food',         'slug' => 'fast-food',         'icon' => '🍔'],
                ['name' => 'Beverages',         'slug' => 'beverages',         'icon' => '☕'],
                ['name' => 'Bakery & Desserts', 'slug' => 'bakery-desserts',   'icon' => '🎂'],
                ['name' => 'Healthy / Salads',  'slug' => 'healthy-salads',    'icon' => '🥗'],
                ['name' => 'Seafood',           'slug' => 'seafood',           'icon' => '🦐'],
                ['name' => 'Breakfast',         'slug' => 'breakfast',         'icon' => '🍳'],
                ['name' => 'Gujarati',          'slug' => 'gujarati',          'icon' => '🫙'],
                ['name' => 'Bengali',           'slug' => 'bengali',           'icon' => '🐟'],
                ['name' => 'Punjabi',           'slug' => 'punjabi',           'icon' => '🥙'],
                ['name' => 'Rajasthani',        'slug' => 'rajasthani',        'icon' => '🌶️'],
                ['name' => 'Andhra / Telangana','slug' => 'andhra-telangana',  'icon' => '🌶'],
                ['name' => 'Maharashtrian',     'slug' => 'maharashtrian',     'icon' => '🥜'],
            ];
            foreach ($defaults as $i => $row) {
                DB::table('food_cuisines')->insert(array_merge($row, [
                    'sort_order' => $i + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        // Add cuisines JSON column to food_restaurants
        if (Schema::hasTable('food_restaurants') && !Schema::hasColumn('food_restaurants', 'cuisines')) {
            Schema::table('food_restaurants', function (Blueprint $table) {
                $table->json('cuisines')->nullable()->after('sub_category');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('food_cuisines');

        if (Schema::hasTable('food_restaurants') && Schema::hasColumn('food_restaurants', 'cuisines')) {
            Schema::table('food_restaurants', function (Blueprint $table) {
                $table->dropColumn('cuisines');
            });
        }
    }
};
