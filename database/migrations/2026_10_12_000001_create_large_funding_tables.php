<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Large Funding Applications Table (₹2 Crore to ₹100 Crore)
        if (!Schema::hasTable('large_funding_applications')) {
            Schema::create('large_funding_applications', function (Blueprint $table) {
                $table->id();
                $table->string('application_number', 50)->unique();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->unsignedBigInteger('customer_id')->nullable()->index();
                $table->string('mobile_number', 20)->index();
                $table->boolean('is_otp_verified')->default(true);

                // Form 1 — Applicant Details
                $table->string('applicant_name', 150);
                $table->string('email', 150);
                $table->string('applicant_role', 60); // Founder, Co-Founder, Director, Partner, Proprietor, Authorised Representative
                $table->string('pan_number', 20)->index();
                $table->string('city', 100);
                $table->string('state', 100);
                $table->string('pincode', 20);

                // Form 2 — Business Details
                $table->string('business_name', 200);
                $table->string('business_type', 60); // Startup / New Business, Existing Business
                $table->string('business_category', 100); // Technology / IT, Fintech, etc.
                $table->text('business_description');
                $table->string('business_registration_status', 30); // Registered, Not Registered
                $table->date('registration_date')->nullable();
                $table->string('website_or_app_link', 255)->nullable();
                $table->text('business_address');

                // Form 3 — Founder / Partner Details
                $table->integer('total_founders_count')->default(1);
                $table->json('founders_json')->nullable();
                $table->string('main_applicant_name', 150)->nullable();

                // Form 4 — Funding Requirement
                $table->string('required_funding_amount', 60); // ₹2 Crore – ₹5 Crore, etc.
                $table->string('funding_type', 60); // Private Investor Funding, Equity, Business Loan, etc.
                $table->string('funding_purpose', 100); // Business Expansion, Working Capital, etc.
                $table->string('funding_timeline', 60); // Immediately, Within 1–3 Months, etc.
                $table->text('expected_use_of_funds');

                // Form 5 — Basic Business Financial Details
                $table->string('business_stage', 60); // Idea Stage, Revenue Generating, etc.
                $table->string('business_start_date', 60);
                $table->string('monthly_revenue', 60); // No Revenue Yet, Below ₹5 Lakh, etc.
                $table->string('annual_turnover', 100)->nullable();
                $table->string('current_profit_loss', 100)->nullable();
                $table->text('existing_loan_details')->nullable();
                $table->string('expected_revenue_after_funding', 100)->nullable();

                // Form 6 — Bank Account Details
                $table->string('bank_account_holder_name', 150)->nullable();
                $table->string('bank_account_number', 60)->nullable();
                $table->string('bank_ifsc', 30)->nullable();
                $table->string('bank_name', 150)->nullable();

                // Form 7 — Consent
                $table->boolean('consent_info_accurate')->default(true);
                $table->boolean('consent_fiinway_review')->default(true);
                $table->boolean('consent_share_with_investors')->default(true);
                $table->boolean('consent_terms_policy')->default(true);
                $table->boolean('consent_no_guarantee')->default(true);
                $table->timestamp('submitted_at')->nullable();

                // Pipeline Stages (1 to 8)
                // 1=Submitted, 2=Doc Review, 3=Fee Payment, 4=Forwarded to Investors, 5=Discussion, 6=Final Offer, 7=Approved, 8=Disbursed
                $table->tinyInteger('current_stage')->default(1);
                $table->string('application_status', 40)->default('SUBMITTED'); // SUBMITTED, UNDER_REVIEW, DOCUMENTS_VERIFIED, FEE_PENDING, FEE_PAID, FORWARDED_TO_INVESTORS, FUNDING_DISCUSSION, FINAL_OFFER, FUND_APPROVED, DISBURSED, REJECTED

                // Service Fee Management
                $table->decimal('service_fee_base', 12, 2)->default(25000.00);
                $table->decimal('service_fee_gst', 12, 2)->default(4500.00);
                $table->decimal('service_fee_total', 12, 2)->default(29500.00);
                $table->timestamp('fee_deadline')->nullable();
                $table->string('fee_payment_status', 20)->default('pending'); // pending, paid, waived
                $table->timestamp('fee_paid_at')->nullable();
                $table->string('fee_payment_ref', 100)->nullable();
                $table->string('fee_payment_method', 50)->nullable();

                // Milestone details & approval
                $table->decimal('approved_amount', 15, 2)->nullable();
                $table->string('approved_investor_name', 150)->nullable();
                $table->string('approval_letter_doc', 255)->nullable();
                $table->string('disbursement_utr', 100)->nullable();
                $table->timestamp('disbursed_at')->nullable();
                $table->text('admin_notes')->nullable();
                $table->text('rejection_reason')->nullable();

                $table->timestamps();
            });
        }

        // 2. Large Funding Documents Table
        if (!Schema::hasTable('large_funding_documents')) {
            Schema::create('large_funding_documents', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('application_id')->index();
                $table->string('document_type', 60)->index(); // bank_cheque, pan_card, identity_proof, pitch_deck, fund_plan, company_reg, gst_cert, itr_financials, cap_table, other
                $table->string('file_path', 255);
                $table->string('original_name', 255)->nullable();
                $table->string('file_size', 50)->nullable();
                $table->string('status', 30)->default('pending'); // pending, verified, rejected
                $table->text('admin_feedback')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->unsignedBigInteger('reviewed_by')->nullable();
                $table->timestamps();

                $table->foreign('application_id')->references('id')->on('large_funding_applications')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('large_funding_documents');
        Schema::dropIfExists('large_funding_applications');
    }
};
