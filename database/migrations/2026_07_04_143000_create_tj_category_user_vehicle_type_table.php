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
        Schema::create('tj_category_user_vehicle_type', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_user_id');
            $table->integer('vehicle_type_id');

            $table->foreign('category_user_id', 'fk_cat_user_type')->references('id')->on('tj_categorie_user')->onDelete('cascade');
            $table->foreign('vehicle_type_id', 'fk_veh_type_cat')->references('id')->on('tj_type_vehicule')->onDelete('cascade');
            
            $table->unique(['category_user_id', 'vehicle_type_id'], 'uidx_cat_veh');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tj_category_user_vehicle_type');
    }
};
