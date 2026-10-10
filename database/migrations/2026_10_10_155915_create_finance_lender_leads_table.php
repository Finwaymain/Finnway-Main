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
        if (!Schema::hasTable('finance_lender_leads')) {
            Schema::create('finance_lender_leads', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('lender_id')->nullable()->index();
                $table->string('lender_name', 191)->nullable();
                $table->string('applicant_name', 191);
                $table->string('phone', 30)->index();
                $table->string('email', 191)->nullable();
                $table->string('referral_code', 50)->nullable()->index();
                $table->string('referrer_type', 50)->nullable();
                $table->unsignedBigInteger('referrer_id')->nullable()->index();
                $table->string('affiliate_url', 1000)->nullable();
                $table->string('ip_address', 60)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_lender_leads');
    }
};
