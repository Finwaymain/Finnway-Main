<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('marketplace_commission_settings')) {
            Schema::create('marketplace_commission_settings', function (Blueprint $table) {
                $table->id();
                $table->string('commission_type')->default('percentage');
                $table->decimal('commission_value', 10, 2)->default(5.00);
                $table->boolean('is_active')->default(true);
                $table->decimal('min_order_amount', 10, 2)->default(0.00);
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_commission_settings');
    }
};
