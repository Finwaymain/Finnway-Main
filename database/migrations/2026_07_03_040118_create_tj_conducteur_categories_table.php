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
        Schema::create('tj_conducteur_categories', function (Blueprint $table) {
            $table->id();
            $table->integer('driver_id');
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('subcategory_id')->nullable();
            
            $table->foreign('driver_id')->references('id')->on('tj_conducteur')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('tj_categorie_user')->onDelete('cascade');
            $table->foreign('subcategory_id')->references('id')->on('tj_categorie_user')->onDelete('set null');
            
            // Ensure unique combination per driver and category
            $table->unique(['driver_id', 'category_id']);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tj_conducteur_categories');
    }
};
