<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finance_loan_applications', function (Blueprint $table) {
            if (!Schema::hasColumn('finance_loan_applications', 'flow_type')) {
                $table->enum('flow_type', ['external_bank', 'internal_wallet'])
                      ->default('external_bank')
                      ->after('loan_category')
                      ->comment('external_bank = Flow A (Cash/Business Loan via NBFC); internal_wallet = Flow B (Zero-CIBIL/Virtual/Student)');
            }
            if (!Schema::hasColumn('finance_loan_applications', 'partner_lock_status')) {
                $table->enum('partner_lock_status', ['unlocked', 'locked'])
                      ->default('unlocked')
                      ->after('selected_lender_name')
                      ->comment('locked = user has selected a lender partner, others are excluded');
            }
            if (!Schema::hasColumn('finance_loan_applications', 'applicant_details')) {
                $table->json('applicant_details')
                      ->nullable()
                      ->after('business_details')
                      ->comment('JSON: name, DOB, employment_type, income, address etc.');
            }
            if (!Schema::hasColumn('finance_loan_applications', 'cibil_type')) {
                $table->string('cibil_type', 10)
                      ->nullable()
                      ->after('flow_type')
                      ->comment('low | good — only for cash_loan products');
            }
        });
    }

    public function down(): void
    {
        Schema::table('finance_loan_applications', function (Blueprint $table) {
            $table->dropColumn(['flow_type', 'partner_lock_status', 'applicant_details', 'cibil_type']);
        });
    }
};
