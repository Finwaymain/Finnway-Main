<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consumer_premium_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 10, 2)->default(0);
            $table->integer('validity_days')->default(365);
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');

            // Cashback
            $table->enum('sender_cashback_type', ['percentage', 'flat'])->default('percentage');
            $table->decimal('sender_cashback_value', 8, 2)->default(0);
            $table->enum('receiver_cashback_type', ['percentage', 'flat'])->default('percentage');
            $table->decimal('receiver_cashback_value', 8, 2)->default(0);

            // Service Discounts (%)
            $table->decimal('discount_cab', 5, 2)->default(0);
            $table->decimal('discount_bike', 5, 2)->default(0);
            $table->decimal('discount_home_service', 5, 2)->default(0);
            $table->decimal('discount_food', 5, 2)->default(0);
            $table->decimal('discount_travel', 5, 2)->default(0);
            $table->decimal('discount_hotel', 5, 2)->default(0);
            $table->decimal('discount_healthcare', 5, 2)->default(0);
            $table->decimal('discount_marketplace', 5, 2)->default(0);

            // Shipping
            $table->boolean('free_shipping')->default(false);
            $table->decimal('shipping_min_order', 10, 2)->default(0);

            // Loan Eligibility
            $table->boolean('loan_personal')->default(false);
            $table->boolean('loan_business')->default(false);
            $table->boolean('loan_credit_card')->default(false);
            $table->boolean('loan_interest_free')->default(false);
            $table->boolean('loan_virtual')->default(false);
            $table->decimal('virtual_credit_limit', 10, 2)->default(15000);

            $table->integer('display_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consumer_premium_plans');
    }
};
