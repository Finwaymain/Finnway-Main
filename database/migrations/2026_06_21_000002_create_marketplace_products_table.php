<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('marketplace_products', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->integer('stock_quantity')->default(1);
            $table->unsignedBigInteger('user_id'); 
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('subcategory_id')->nullable();
            $table->string('status')->default('pending_verification'); 
            $table->string('condition')->default('New'); 
            $table->string('delivery_type')->default('Local'); 
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('marketplace_products');
    }
};
