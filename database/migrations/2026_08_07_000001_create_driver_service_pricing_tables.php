<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('driver_service_pricing')) {
            Schema::create('driver_service_pricing', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('driver_id');
                $table->unsignedBigInteger('category_id');
                $table->decimal('visiting_charge', 10, 2)->default(0);
                $table->timestamps();

                $table->unique(['driver_id', 'category_id']);
                $table->index('driver_id');
            });
        }

        if (!Schema::hasTable('driver_service_items')) {
            Schema::create('driver_service_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('driver_id');
                $table->unsignedBigInteger('category_id');
                $table->string('service_name');
                $table->decimal('price', 10, 2)->default(0);
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();

                $table->index(['driver_id', 'category_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_service_items');
        Schema::dropIfExists('driver_service_pricing');
    }
};
