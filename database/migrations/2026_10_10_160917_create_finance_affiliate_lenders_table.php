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
        if (!Schema::hasTable('finance_affiliate_lenders')) {
            Schema::create('finance_affiliate_lenders', function (Blueprint $table) {
                $table->id();
                $table->string('name', 191);
                $table->string('logo', 255)->nullable();
                $table->decimal('min_loan_amount', 12, 2)->default(50000);
                $table->decimal('max_loan_amount', 12, 2)->default(5000000);
                $table->string('interest_rate_display', 100)->default('10.5% - 14% p.a.');
                $table->string('tenure_display', 100)->default('12 - 60 Months');
                $table->string('processing_fee_display', 100)->default('As applicable');
                $table->text('affiliate_url'); // External affiliate / partner referral link
                $table->string('status', 20)->default('active'); // active / inactive
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_affiliate_lenders');
    }
};
