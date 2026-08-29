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
        Schema::table('tj_conducteur_categories', function (Blueprint $table) {
            $table->dropForeign(['driver_id']);
            $table->dropForeign(['category_id']);
            $table->dropUnique('tj_conducteur_categories_driver_id_category_id_unique');
            
            $table->unique(['driver_id', 'subcategory_id'], 'tj_conducteur_categories_driver_id_subcategory_id_unique');
            
            $table->foreign('driver_id')->references('id')->on('tj_conducteur')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('tj_categorie_user')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tj_conducteur_categories', function (Blueprint $table) {
            $table->dropForeign(['driver_id']);
            $table->dropForeign(['category_id']);
            $table->dropUnique('tj_conducteur_categories_driver_id_subcategory_id_unique');
            
            $table->unique(['driver_id', 'category_id'], 'tj_conducteur_categories_driver_id_category_id_unique');
            
            $table->foreign('driver_id')->references('id')->on('tj_conducteur')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('tj_categorie_user')->onDelete('cascade');
        });
    }
};
