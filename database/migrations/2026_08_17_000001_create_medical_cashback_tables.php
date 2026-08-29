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
        if (!Schema::hasTable('tj_medical_cards')) {
            Schema::create('tj_medical_cards', function (Blueprint $table) {
                $table->id();
                $table->string('user_id');
                $table->string('user_type')->default('customer'); // customer or driver
                $table->string('card_type'); // CARE CREDIT, OPD CREDIT, MEDICASH
                $table->string('aadhaar_number')->nullable();
                $table->decimal('price', 10, 2)->default(0);
                $table->decimal('claim_limit', 10, 2)->default(0);
                $table->decimal('used_amount', 10, 2)->default(0);
                $table->decimal('remaining_amount', 10, 2)->default(0);
                $table->integer('claims_count')->default(0);
                $table->integer('max_claims')->default(5);
                $table->string('payment_method')->default('razorpay'); // wallet or razorpay
                $table->string('payment_txn_id')->nullable();
                $table->string('status')->default('active'); // active, expired, exhausted
                $table->timestamp('expires_at')->nullable();
                $table->timestamp('creer')->useCurrent();
                $table->timestamp('modifier')->useCurrent();
            });
        }

        if (!Schema::hasTable('tj_medical_claims')) {
            Schema::create('tj_medical_claims', function (Blueprint $table) {
                $table->id();
                $table->string('claim_id')->unique(); // e.g. CLM12567890
                $table->string('user_id');
                $table->string('user_type')->default('customer'); // customer or driver
                $table->unsignedBigInteger('card_id')->nullable();
                $table->string('card_type')->nullable();
                $table->decimal('expense_amount', 10, 2)->default(0);
                $table->decimal('requested_amount', 10, 2)->default(0);
                $table->decimal('approved_amount', 10, 2)->default(0);
                $table->string('status')->default('pending'); // pending, under_review, approved, rejected, need_reupload, completed
                $table->string('prescription_doc')->nullable();
                $table->string('diagnostic_doc')->nullable();
                $table->string('cash_memo_doc')->nullable();
                $table->text('reupload_reason')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->text('approval_reason')->nullable();
                $table->string('wallet_txn_id')->nullable();
                $table->timestamp('settled_at')->nullable();
                $table->timestamp('creer')->useCurrent();
                $table->timestamp('modifier')->useCurrent();
            });
        }

        if (!Schema::hasTable('tj_medical_expenses')) {
            Schema::create('tj_medical_expenses', function (Blueprint $table) {
                $table->id();
                $table->string('user_id');
                $table->string('user_type')->default('customer');
                $table->string('merchant_name')->nullable();
                $table->string('category')->nullable(); // clinic, hospital, pharmacy, lab, etc.
                $table->decimal('amount', 10, 2)->default(0);
                $table->string('txn_id')->nullable();
                $table->timestamp('creer')->useCurrent();
            });
        }

        if (!Schema::hasTable('tj_medical_settings')) {
            Schema::create('tj_medical_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->timestamp('updated_at')->useCurrent();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tj_medical_settings');
        Schema::dropIfExists('tj_medical_expenses');
        Schema::dropIfExists('tj_medical_claims');
        Schema::dropIfExists('tj_medical_cards');
    }
};
