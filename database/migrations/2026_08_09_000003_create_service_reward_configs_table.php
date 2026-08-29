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
        if (!Schema::hasTable('service_reward_configs')) {
            Schema::create('service_reward_configs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('category_id')->nullable()->index();
                $table->string('service_name')->nullable();
                $table->string('service_slug')->nullable()->index();
                $table->string('reward_mode')->default('percentage');
                $table->string('business_value')->default('2%');
                $table->string('customer_value')->default('2%');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_reward_configs');
    }
};
