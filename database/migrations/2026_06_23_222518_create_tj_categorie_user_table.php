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
        Schema::create('tj_categorie_user', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->dateTime('creer')->nullable();
            $table->dateTime('modifier')->nullable();
            $table->softDeletes();

            $table->foreign('parent_id')->references('id')->on('tj_categorie_user')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tj_categorie_user');
    }
};
