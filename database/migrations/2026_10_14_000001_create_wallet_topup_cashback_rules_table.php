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
        if (!Schema::hasTable('wallet_topup_cashback_rules')) {
            Schema::create('wallet_topup_cashback_rules', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->enum('cashback_type', ['percentage', 'flat'])->default('percentage');
                $table->decimal('cashback_value', 10, 2); // e.g. 10.00 (%) or 50.00 (Flat)
                $table->decimal('min_topup_amount', 10, 2)->default(100.00); // Minimum amount required to qualify
                $table->decimal('max_topup_amount', 10, 2)->nullable(); // Optional ceiling for qualifying
                $table->decimal('max_cashback_amount', 10, 2)->nullable(); // "Upto" cap for percentage
                $table->enum('target_user_type', ['all', 'customer', 'driver'])->default('all');
                $table->integer('usage_limit_per_user')->nullable(); // null = unlimited
                $table->integer('priority')->default(0); // higher evaluates first
                $table->boolean('is_active')->default(true);
                $table->dateTime('start_date')->nullable();
                $table->dateTime('end_date')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('wallet_topup_cashback_logs')) {
            Schema::create('wallet_topup_cashback_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('rule_id')->nullable();
                $table->unsignedBigInteger('user_id');
                $table->enum('user_type', ['customer', 'driver'])->default('customer');
                $table->decimal('topup_amount', 10, 2);
                $table->decimal('cashback_amount', 10, 2);
                $table->string('txn_id')->nullable();
                $table->string('status')->default('credited');
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'user_type']);
                $table->index('rule_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_topup_cashback_logs');
        Schema::dropIfExists('wallet_topup_cashback_rules');
    }
};
