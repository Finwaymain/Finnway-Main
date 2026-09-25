<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Unified Finance Customer Master (Links Customer or Driver)
        if (!Schema::hasTable('finance_customers')) {
            Schema::create('finance_customers', function (Blueprint $table) {
                $table->id();
                $table->string('user_type', 20)->default('customer'); // 'customer' or 'driver'
                $table->unsignedBigInteger('user_id')->nullable()->index(); // references tj_user_app.id
                $table->unsignedBigInteger('driver_id')->nullable()->index(); // references tj_conducteur.id
                $table->string('phone', 20)->index();
                $table->string('name', 100)->nullable();
                $table->string('email', 100)->nullable();
                $table->date('dob')->nullable();
                $table->string('gender', 20)->nullable();
                $table->string('pan', 20)->nullable()->index();
                $table->string('aadhaar', 20)->nullable()->index();
                $table->text('address')->nullable();
                $table->string('city', 100)->nullable();
                $table->string('state', 100)->nullable();
                $table->string('pincode', 20)->nullable();
                $table->string('employment_type', 50)->nullable();
                $table->string('company_name', 100)->nullable();
                $table->decimal('monthly_income', 12, 2)->nullable();
                $table->decimal('existing_emi', 12, 2)->nullable();
                $table->string('cibil_category', 20)->default('none'); // 'low', 'good', 'none'
                $table->string('kyc_status', 20)->default('pending'); // 'pending', 'verified', 'rejected'
                $table->timestamp('kyc_verified_at')->nullable();
                $table->string('account_status', 20)->default('active'); // 'active', 'blocked', 'suspended'
                $table->timestamps();
            });
        }

        // 2. Common Document Vault (3-5 Day Smart Reuse Engine)
        if (!Schema::hasTable('finance_documents')) {
            Schema::create('finance_documents', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('customer_id')->index();
                $table->string('document_type', 50)->index(); // 'pan', 'aadhaar', 'bank_statement', 'salary_slip', 'student_id', etc.
                $table->string('document_number', 100)->nullable();
                $table->string('file_path', 255);
                $table->string('file_name', 255)->nullable();
                $table->string('status', 30)->default('pending'); // 'pending', 'verified', 'rejected', 'reupload_required', 'expired'
                $table->timestamp('verified_at')->nullable();
                $table->unsignedBigInteger('verified_by')->nullable();
                $table->text('admin_remark')->nullable();
                $table->boolean('is_reusable')->default(true);
                $table->timestamp('reuse_valid_until')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
            });
        }

        // 3. Document Requests
        if (!Schema::hasTable('finance_document_requests')) {
            Schema::create('finance_document_requests', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('customer_id')->index();
                $table->unsignedBigInteger('application_id')->nullable()->index();
                $table->json('requested_documents');
                $table->text('admin_remark');
                $table->string('status', 30)->default('pending'); // 'pending', 'submitted', 'fulfilled'
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
            });
        }

        // 4. Finance Loan Products Master
        if (!Schema::hasTable('finance_loan_products')) {
            Schema::create('finance_loan_products', function (Blueprint $table) {
                $table->id();
                $table->string('code', 50)->unique();
                $table->string('name', 100);
                $table->string('category', 50); // 'cash_loan', 'business_loan', 'virtual_loan', 'zero_cibil', 'student_credit'
                $table->decimal('min_amount', 12, 2)->default(0);
                $table->decimal('max_amount', 12, 2)->default(0);
                $table->integer('min_tenure_months')->default(12);
                $table->integer('max_tenure_months')->default(60);
                $table->decimal('interest_rate_p_a', 5, 2)->default(0);
                $table->boolean('is_interest_free')->default(false);
                $table->string('processing_fee_type', 20)->default('fixed'); // 'fixed' or 'percentage'
                $table->decimal('processing_fee_value', 10, 2)->default(0);
                $table->json('processing_fee_slabs')->nullable();
                $table->decimal('daily_repayment_amount', 10, 2)->nullable();
                $table->decimal('daily_usage_limit', 10, 2)->nullable();
                $table->integer('grace_period_days')->default(0);
                $table->decimal('late_fee', 10, 2)->default(0);
                $table->decimal('penalty_rate', 5, 2)->default(0);
                $table->boolean('auto_verify')->default(false);
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        // 5. Lender Partner Master (HDFC, ICICI, etc.)
        if (!Schema::hasTable('finance_lender_partners')) {
            Schema::create('finance_lender_partners', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->string('logo', 255)->nullable();
                $table->json('loan_types')->nullable(); // ['cash_loan', 'business_loan']
                $table->decimal('min_loan_amount', 12, 2)->default(100000);
                $table->decimal('max_loan_amount', 12, 2)->default(5000000);
                $table->string('interest_rate_display', 50)->default('10.5% p.a.');
                $table->string('tenure_display', 50)->default('12 - 60 Months');
                $table->string('processing_fee_display', 100)->default('As applicable');
                $table->string('application_url', 255);
                $table->string('status', 20)->default('active');
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        // 6. Loan Applications (26-step lifecycle)
        if (!Schema::hasTable('finance_loan_applications')) {
            Schema::create('finance_loan_applications', function (Blueprint $table) {
                $table->id();
                $table->string('application_number', 50)->unique();
                $table->unsignedBigInteger('customer_id')->index();
                $table->unsignedBigInteger('product_id')->nullable()->index();
                $table->string('user_type', 20)->default('customer');
                $table->string('applicant_name', 100);
                $table->string('applicant_phone', 20)->index();
                $table->string('loan_category', 50)->index(); // 'low_cibil', 'good_cibil', 'business_loan', 'virtual_loan', etc.
                $table->decimal('requested_amount', 12, 2);
                $table->decimal('indicative_amount', 12, 2)->nullable();
                $table->decimal('approved_amount', 12, 2)->nullable();
                $table->integer('tenure_months')->nullable();
                $table->decimal('estimated_emi', 10, 2)->nullable();
                $table->decimal('estimated_total_repayment', 12, 2)->nullable();
                $table->decimal('processing_fee_amount', 10, 2)->default(0);
                $table->decimal('processing_fee_tax', 10, 2)->default(0);
                $table->decimal('processing_fee_total', 10, 2)->default(0);
                $table->string('processing_fee_status', 20)->default('pending');
                $table->string('processing_fee_payment_method', 50)->nullable();
                $table->string('processing_fee_txn_id', 100)->nullable();
                $table->unsignedBigInteger('selected_lender_id')->nullable();
                $table->string('selected_lender_name', 100)->nullable();
                $table->timestamp('partner_selection_time')->nullable();
                $table->string('process_completion_proof_url', 255)->nullable();
                $table->timestamp('proof_submitted_at')->nullable();
                $table->text('proof_remarks')->nullable();
                $table->string('agent_selfie_url', 255)->nullable();
                $table->timestamp('agent_selfie_submitted_at')->nullable();
                $table->string('disbursement_bank_name', 100)->nullable();
                $table->string('disbursement_account_name', 100)->nullable();
                $table->string('disbursement_account_number', 50)->nullable();
                $table->string('disbursement_ifsc', 20)->nullable();
                $table->string('disbursement_account_type', 20)->nullable();
                $table->string('disbursement_status', 30)->default('not_requested');
                $table->string('disbursement_txn_ref', 100)->nullable();
                $table->timestamp('disbursed_at')->nullable();
                $table->string('application_status', 50)->default('APPLICATION_CREATED');
                $table->text('rejection_reason')->nullable();
                $table->timestamp('reapply_locked_until')->nullable();
                $table->text('admin_remarks')->nullable();
                $table->json('business_details')->nullable();
                $table->json('student_details')->nullable();
                $table->timestamps();
            });
        }

        // 7. Finance Wallets (Segregated Ledgers: virtual_loan, student_credit)
        if (!Schema::hasTable('finance_wallets')) {
            Schema::create('finance_wallets', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('customer_id')->index();
                $table->string('wallet_type', 30)->index(); // 'virtual_loan', 'student_credit'
                $table->decimal('approved_limit', 12, 2)->default(0);
                $table->decimal('available_balance', 12, 2)->default(0);
                $table->decimal('used_amount', 12, 2)->default(0);
                $table->decimal('daily_usage_limit', 10, 2)->default(0);
                $table->decimal('used_today', 10, 2)->default(0);
                $table->string('today_usage_permission', 20)->default('ACTIVE'); // 'ACTIVE' or 'LOCKED'
                $table->string('status', 20)->default('active'); // 'active', 'locked', 'suspended', 'closed'
                $table->timestamp('valid_until')->nullable();
                $table->timestamps();
            });
        }

        // 8. Daily Recovery Schedules (Doc 5: Daily Usage Lock Engine)
        if (!Schema::hasTable('finance_daily_schedules')) {
            Schema::create('finance_daily_schedules', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('application_id')->index();
                $table->unsignedBigInteger('customer_id')->index();
                $table->date('schedule_date')->index();
                $table->integer('day_number')->default(1);
                $table->decimal('emi_amount', 10, 2);
                $table->decimal('late_charges', 10, 2)->default(0);
                $table->decimal('penalty_amount', 10, 2)->default(0);
                $table->decimal('total_due', 10, 2);
                $table->decimal('paid_amount', 10, 2)->default(0);
                $table->string('status', 20)->default('pending'); // 'paid', 'pending', 'overdue'
                $table->timestamp('paid_at')->nullable();
                $table->string('payment_method', 50)->nullable();
                $table->string('txn_id', 100)->nullable();
                $table->timestamps();
            });
        }

        // 9. Finance Audit Transactions Ledger
        if (!Schema::hasTable('finance_transactions')) {
            Schema::create('finance_transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('customer_id')->index();
                $table->unsignedBigInteger('application_id')->nullable()->index();
                $table->unsignedBigInteger('wallet_id')->nullable()->index();
                $table->string('txn_number', 50)->unique();
                $table->string('txn_type', 50); // 'fee_payment', 'credit_disbursement', 'merchant_payment', 'daily_repayment', etc.
                $table->decimal('amount', 12, 2);
                $table->string('direction', 10)->default('debit'); // 'debit' or 'credit'
                $table->decimal('balance_after', 12, 2)->default(0);
                $table->string('payment_method', 50)->nullable();
                $table->string('payment_gateway_ref', 100)->nullable();
                $table->string('receiver_name', 100)->nullable();
                $table->string('receiver_type', 50)->nullable();
                $table->string('status', 20)->default('success');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_transactions');
        Schema::dropIfExists('finance_daily_schedules');
        Schema::dropIfExists('finance_wallets');
        Schema::dropIfExists('finance_loan_applications');
        Schema::dropIfExists('finance_lender_partners');
        Schema::dropIfExists('finance_loan_products');
        Schema::dropIfExists('finance_document_requests');
        Schema::dropIfExists('finance_documents');
        Schema::dropIfExists('finance_customers');
    }
};
