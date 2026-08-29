<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('driver_service_skills')) {
            Schema::create('driver_service_skills', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('driver_id');
                $table->unsignedBigInteger('provider_category_id');
                $table->unsignedBigInteger('skill_id');
                $table->timestamps();

                $table->unique(['driver_id', 'provider_category_id', 'skill_id'], 'driver_provider_skill_unique');
                $table->index(['driver_id', 'provider_category_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_service_skills');
    }
};
