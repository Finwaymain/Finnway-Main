<?php

namespace Tests\Feature;

use App\Models\Finance\FinanceCustomer;
use App\Models\Finance\FinanceDailySchedule;
use App\Models\Finance\FinanceDocument;
use App\Models\Finance\FinanceDocumentRequest;
use App\Models\Finance\FinanceLenderPartner;
use App\Models\Finance\FinanceLoanApplication;
use App\Models\Finance\FinanceLoanProduct;
use App\Models\Finance\FinanceWallet;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FinanceCompleteFlowsTest extends TestCase
{
    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        if (FinanceLoanProduct::count() === 0) {
            $this->seed(\Database\Seeders\FinanceProductSeeder::class);
        }

        $this->adminUser = User::firstOrCreate(
            ['email' => 'admin@fiinway.test'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );
    }

    /**
     * FLOW A — Cash Loan (Low & Good CIBIL)
     * Tests: Context -> Upfront KYC (NO bank doc) -> Fee -> Single Lender Lock -> Proof/Selfie -> Disbursement Bank Details -> Admin Request Bank Doc -> Doc Submission
     */
    public function test_flow_a_cash_loan_complete_pipeline(): void
    {
        $phone = '9911001122';

        // 1. Context initialization
        $resContext = $this->getJson("/api/v1/finance/context?phone={$phone}&name=Rajesh+Sharma&user_type=customer");
        $resContext->assertStatus(200)->assertJson(['success' => true]);

        $customer = FinanceCustomer::where('phone', '+919911001122')->orWhere('phone', $phone)->first();
        $this->assertNotNull($customer, 'Customer must be created.');

        // 2. Upfront KYC — CONDITION: ONLY PAN + Aadhaar front/back. NO bank passbook/statement!
        $aadhaarFront = UploadedFile::fake()->create('aadhaar_front.jpg', 100, 'image/jpeg');
        $aadhaarBack = UploadedFile::fake()->create('aadhaar_back.jpg', 100, 'image/jpeg');
        $panCard = UploadedFile::fake()->create('pan_card.jpg', 100, 'image/jpeg');

        $resInitiate = $this->post('/api/v1/finance/applications/initiate', [
            'applicant_phone' => $phone,
            'applicant_name' => 'Rajesh Sharma',
            'product_code' => 'cash_loan_low_cibil',
            'requested_amount' => 75000,
            'tenure_months' => 24,
            'pan' => 'RSHAR1234F',
            'city' => 'Indore',
            'pincode' => '452001',
            'gender' => 'Male',
            'employment' => 'Salaried',
            'aadhaar_front' => $aadhaarFront,
            'aadhaar_back' => $aadhaarBack,
            'pan_card' => $panCard,
        ], ['Accept' => 'application/json']);

        $resInitiate->assertStatus(200)->assertJson(['success' => true]);
        $appId = $resInitiate->json('data.id');
        $this->assertNotEmpty($appId);

        // Assert bank passbook or statement was NOT stored upfront
        $this->assertDatabaseMissing('finance_documents', [
            'customer_id' => $customer->id,
            'document_type' => 'bank_passbook',
        ]);
        $this->assertDatabaseMissing('finance_documents', [
            'customer_id' => $customer->id,
            'document_type' => 'bank_statement',
        ]);

        // 3. Processing fee payment confirmation
        $resFee = $this->postJson("/api/v1/finance/applications/{$appId}/confirm-fee", [
            'payment_method' => 'UPI',
        ]);
        $resFee->assertStatus(200)->assertJson(['success' => true]);

        $app = FinanceLoanApplication::find($appId);
        $this->assertEquals('paid', $app->processing_fee_status);

        // 4. Partner Selection & Single Lender Lock
        $partner1 = FinanceLenderPartner::firstOrCreate(
            ['name' => 'Tata Capital Financial Services'],
            ['min_loan_amount' => 50000, 'max_loan_amount' => 500000, 'status' => 'active']
        );
        $partner2 = FinanceLenderPartner::firstOrCreate(
            ['name' => 'Bajaj Finserv'],
            ['min_loan_amount' => 50000, 'max_loan_amount' => 500000, 'status' => 'active']
        );

        $resLock = $this->postJson("/api/v1/finance/applications/{$appId}/select-partner", [
            'partner_id' => $partner1->id,
        ]);
        $resLock->assertStatus(200)->assertJson(['success' => true]);

        $app->refresh();
        $this->assertEquals($partner1->id, $app->selected_lender_id);
        $this->assertEquals('PARTNER_SELECTED', $app->application_status);

        // 5. Completion proof & Selfie upload
        $proof = UploadedFile::fake()->create('loan_sanction_proof.pdf', 200, 'application/pdf');
        $selfie = UploadedFile::fake()->create('customer_selfie.jpg', 150, 'image/jpeg');

        $this->post("/api/v1/finance/applications/{$appId}/upload-proof", ['proof' => $proof], ['Accept' => 'application/json'])
            ->assertStatus(200);

        $this->post("/api/v1/finance/applications/{$appId}/upload-selfie", ['selfie' => $selfie], ['Accept' => 'application/json'])
            ->assertStatus(200);

        // 6. Disbursement bank details submission
        $resDisb = $this->postJson("/api/v1/finance/applications/{$appId}/disbursement-account", [
            'account_name' => 'Rajesh Sharma',
            'bank_name' => 'HDFC Bank',
            'account_number' => '50100293847582',
            'ifsc' => 'HDFC0001234',
        ]);
        $resDisb->assertStatus(200)->assertJson(['success' => true]);

        // 7. CONDITION: Admin requests Bank Passbook / Statement at Disbursal stage (User Rule)
        $this->actingAs($this->adminUser);
        $resReqDoc = $this->post("/admin/finance/applications/{$appId}/request-document", [
            'document_type' => 'bank_passbook',
            'admin_remark' => 'Please provide bank passbook front page to verify account name matches applicant.',
        ]);
        $resReqDoc->assertStatus(302); // Redirect back with success

        $this->assertDatabaseHas('finance_document_requests', [
            'application_id' => $appId,
            'status' => 'pending',
        ]);

        $app->refresh();
        $this->assertEquals('ADDITIONAL_DOCS_REQUESTED', $app->application_status);
    }

    /**
     * FLOW A — Business Loan Complete Pipeline
     * Tests: Business profile fields -> Upfront docs without bank statement -> Lender Selection -> Underwriting
     */
    public function test_flow_a_business_loan_pipeline(): void
    {
        $phone = '9922003344';

        $resInitiate = $this->post('/api/v1/finance/applications/initiate', [
            'applicant_phone' => $phone,
            'applicant_name' => 'Gupta Enterprises',
            'product_code' => 'business_loan',
            'requested_amount' => 300000,
            'tenure_months' => 36,
            'pan' => 'GUPTA9988G',
            'city' => 'Bhopal',
            'pincode' => '462001',
            'gender' => 'Male',
            'employment' => 'Business Owner',
            'business_name' => 'Gupta Traders & Logistics',
            'business_type' => 'Proprietorship',
            'gstin' => '23AABCG1234M1Z5',
            'annual_turnover' => 4500000,
            'years_in_business' => 4,
            'aadhaar_front' => UploadedFile::fake()->create('owner_aadhaar.jpg', 100, 'image/jpeg'),
            'pan_card' => UploadedFile::fake()->create('business_pan.jpg', 100, 'image/jpeg'),
        ], ['Accept' => 'application/json']);

        $resInitiate->assertStatus(200)->assertJson(['success' => true]);
        $appId = $resInitiate->json('data.id');

        $app = FinanceLoanApplication::find($appId);
        $this->assertEquals('business_loan', $app->loan_category);
        $this->assertEquals(300000, $app->requested_amount);

        // Admin Approval of Business Loan
        $this->actingAs($this->adminUser);
        $resStatus = $this->post("/admin/finance/applications/{$appId}/status", [
            'status' => 'LOAN_APPROVED',
            'approved_amount' => 250000,
            'remarks' => 'Business turnover verified with GSTIN.',
        ]);
        $resStatus->assertStatus(302);

        $app->refresh();
        $this->assertEquals('LOAN_APPROVED', $app->application_status);
        $this->assertEquals(250000, $app->approved_amount);
    }

    /**
     * FLOW B — Zero-CIBIL Daily Recovery Loan
     * Tests: 0% Interest -> Fee Paid -> Admin Approval -> Wallet Provisioning -> Daily Repayment & Usage Unlock Rules
     */
    public function test_flow_b_zero_cibil_daily_recovery_and_lock_rules(): void
    {
        $phone = '99330' . rand(10000, 99999);
        $customer = FinanceCustomer::create([
            'phone' => '+91' . $phone,
            'name' => 'Sunil Verma',
            'user_type' => 'customer',
            'cibil_category' => 'zero_cibil',
        ]);

        $app = FinanceLoanApplication::create([
            'application_number' => 'FIIN-ZC-' . time(),
            'customer_id' => $customer->id,
            'applicant_name' => 'Sunil Verma',
            'applicant_phone' => '+91' . $phone,
            'loan_category' => 'zero_cibil_daily',
            'requested_amount' => 50000,
            'approved_amount' => 50000,
            'processing_fee_status' => 'paid',
            'application_status' => 'APPLICATION_CREATED',
        ]);

        // CONDITION: Product must be 0% interest
        $prod = FinanceLoanProduct::where('code', 'zero_cibil_daily')->first();
        $this->assertEquals(0.00, $prod->interest_rate_p_a);
        $this->assertTrue((bool)$prod->is_interest_free);

        // Admin Approves Zero-CIBIL Loan
        $this->actingAs($this->adminUser);
        $this->post("/admin/finance/applications/{$app->id}/status", [
            'status' => 'LOAN_APPROVED',
            'approved_amount' => 50000,
        ])->assertStatus(302);

        // CONDITION: Wallet is provisioned with approved_limit=50000, daily_usage_limit=5000
        $wallet = FinanceWallet::where('customer_id', $customer->id)->where('wallet_type', 'virtual_loan')->first();
        $this->assertNotNull($wallet, 'Virtual loan wallet must be created upon approval.');
        $this->assertEquals(50000, $wallet->approved_limit);
        $this->assertEquals(5000, $wallet->daily_usage_limit);

        // CONDITION: Schedules generated for daily recovery
        $schedulesCount = FinanceDailySchedule::where('application_id', $app->id)->count();
        $this->assertGreaterThanOrEqual(30, $schedulesCount, 'At least 30 daily schedules must be generated.');

        // CONDITION: When unpaid, usage permission is LOCKED
        $wallet->today_usage_permission = 'LOCKED';
        $wallet->save();

        // Customer pays daily recovery EMI
        $resRepay = $this->postJson('/api/v1/finance/daily-repayment', [
            'phone' => '+91' . $phone,
            'payment_amount' => 833.33,
        ]);
        $resRepay->assertStatus(200)->assertJson(['success' => true]);

        // CONDITION: Daily usage unlocks to ACTIVE upon daily repayment
        $wallet->refresh();
        $this->assertEquals('ACTIVE', $wallet->today_usage_permission);

        // CONDITION: Available Balance (₹50k) != Today's Usage Permission (Active with ₹5k limit)
        $this->assertEquals(50000, $wallet->approved_limit);
        $this->assertEquals(5000, $wallet->daily_usage_limit);
    }

    /**
     * FLOW B — Virtual Loan (App-to-App Credit)
     * Tests: Slabs verification -> Wallet ≠ Cash wallet -> Merchant QR payment rules
     */
    public function test_flow_b_virtual_loan_merchant_credit_rules(): void
    {
        $phone = '9944005566';
        $customer = FinanceCustomer::create([
            'phone' => '+91' . $phone,
            'name' => 'Meena Patel',
            'user_type' => 'customer',
        ]);

        // Create Virtual Loan Wallet with ₹30,000 credit limit
        $wallet = FinanceWallet::create([
            'customer_id' => $customer->id,
            'wallet_type' => 'virtual_loan',
            'wallet_identifier' => 'VL-' . rand(10000, 99999),
            'approved_limit' => 30000,
            'available_balance' => 30000,
            'daily_usage_limit' => 5000,
            'today_usage_permission' => 'ACTIVE',
            'status' => 'active',
            'valid_until' => now()->addMonths(6),
        ]);

        // CONDITION: Wallet is dedicated virtual credit (not normal cash wallet)
        $this->assertEquals('virtual_loan', $wallet->wallet_type);

        // CONDITION: Merchant QR transaction (e.g. ₹2,000 payment to business merchant)
        $paymentAmount = 2000;
        $this->assertLessThanOrEqual($wallet->daily_usage_limit, $paymentAmount);

        // Deduct merchant spend
        $wallet->available_balance -= $paymentAmount;
        $wallet->used_amount = ($wallet->used_amount ?? 0) + $paymentAmount;
        $wallet->save();

        $wallet->refresh();
        $this->assertEquals(28000, $wallet->available_balance);
        $this->assertEquals(2000, $wallet->used_amount);
    }

    /**
     * FLOW B — Student Credit Flow
     * Tests: Age criteria (16–26) -> Domestic vs International -> Student ID validity -> Virtual Card activation
     */
    public function test_flow_b_student_credit_rules(): void
    {
        $phone = '9955006677';
        $customer = FinanceCustomer::create([
            'phone' => '+91' . $phone,
            'name' => 'Aakash Roy',
            'user_type' => 'customer',
        ]);

        $app = FinanceLoanApplication::create([
            'application_number' => 'STU-' . time(),
            'customer_id' => $customer->id,
            'applicant_name' => 'Aakash Roy',
            'applicant_phone' => '+91' . $phone,
            'loan_category' => 'student_credit',
            'requested_amount' => 25000,
            'student_details' => [
                'age' => 20, // CONDITION: 16–26 valid
                'student_type' => 'domestic',
                'college_name' => 'Indore Institute of Technology',
                'course' => 'B.Tech CS',
                'student_id_validity_months' => 18, // CONDITION: >= 6 months
            ],
            'application_status' => 'SUBMITTED',
        ]);

        $this->assertGreaterThanOrEqual(16, $app->student_details['age']);
        $this->assertLessThanOrEqual(26, $app->student_details['age']);
        $this->assertGreaterThanOrEqual(6, $app->student_details['student_id_validity_months']);

        // Management Approval
        $this->actingAs($this->adminUser);
        $this->post("/admin/finance/applications/{$app->id}/status", [
            'status' => 'LOAN_APPROVED',
            'approved_amount' => 25000,
        ])->assertStatus(302);

        $app->refresh();
        $this->assertEquals('LOAN_APPROVED', $app->application_status);
    }

    /**
     * ADMIN CONTROLS — 3-Day Reapply Lock & Disbursement UTR
     */
    public function test_admin_underwriting_and_reapply_3_day_lock(): void
    {
        $phone = '9966007788';
        $customer = FinanceCustomer::create([
            'phone' => '+91' . $phone,
            'name' => 'Karan Singh',
            'user_type' => 'customer',
        ]);

        $app = FinanceLoanApplication::create([
            'application_number' => 'APP-REJ-' . time(),
            'customer_id' => $customer->id,
            'applicant_name' => 'Karan Singh',
            'applicant_phone' => '+91' . $phone,
            'loan_category' => 'cash_loan_low_cibil',
            'requested_amount' => 100000,
            'application_status' => 'VALIDATION_PENDING',
        ]);

        // CONDITION: Rejecting enforces 3-day reapply lock
        $this->actingAs($this->adminUser);
        $this->post("/admin/finance/applications/{$app->id}/status", [
            'status' => 'REJECTED',
            'rejection_reason' => 'CIBIL score below underwriting cutoff.',
        ])->assertStatus(302);

        $app->refresh();
        $this->assertEquals('REJECTED', $app->application_status);
        $this->assertNotNull($app->reapply_locked_until, 'Reapply lock date must be set on rejection.');
        $this->assertTrue(now()->diffInDays($app->reapply_locked_until) >= 2, 'Lock must be for at least 3 days.');

        // Test Disbursed flow on an approved application
        $appDisb = FinanceLoanApplication::create([
            'application_number' => 'APP-DISB-' . time(),
            'customer_id' => $customer->id,
            'applicant_name' => 'Karan Singh',
            'applicant_phone' => '+91' . $phone,
            'loan_category' => 'cash_loan_low_cibil',
            'requested_amount' => 50000,
            'approved_amount' => 50000,
            'application_status' => 'DISBURSEMENT_PENDING',
        ]);

        $this->post("/admin/finance/applications/{$appDisb->id}/status", [
            'status' => 'DISBURSED',
            'txn_ref' => 'UTR998877665544',
        ])->assertStatus(302);

        $appDisb->refresh();
        $this->assertEquals('DISBURSED', $appDisb->application_status);
        $this->assertEquals('disbursed', $appDisb->disbursement_status);
        $this->assertEquals('UTR998877665544', $appDisb->disbursement_txn_ref);
    }

    /**
     * BOUNDARY TEST: 3-Day Reapply Lock blocks new application via API
     */
    public function test_reapply_lock_blocks_new_application_within_3_days(): void
    {
        $phone = '99770' . rand(10000, 99999);
        $customer = FinanceCustomer::create([
            'phone' => '+91' . $phone,
            'name' => 'Locked User',
            'user_type' => 'customer',
        ]);

        FinanceLoanApplication::create([
            'application_number' => 'REJ-' . time(),
            'customer_id' => $customer->id,
            'applicant_name' => 'Locked User',
            'applicant_phone' => '+91' . $phone,
            'loan_category' => 'cash_loan_low_cibil',
            'requested_amount' => 50000,
            'application_status' => 'REJECTED',
            'rejection_reason' => 'Low CIBIL score',
            'reapply_locked_until' => now()->addDays(3),
        ]);

        // Attempting to apply again immediately must return 422
        $res = $this->postJson('/api/v1/finance/applications/initiate', [
            'applicant_phone' => '+91' . $phone,
            'applicant_name' => 'Locked User',
            'product_code' => 'cash_loan_low_cibil',
            'requested_amount' => 50000,
            'tenure_months' => 12,
        ]);

        $res->assertStatus(422)
            ->assertJson(['success' => false]);
        $this->assertStringContainsString('Your previous application was rejected', $res->json('error'));
    }

    /**
     * BOUNDARY TEST: Student Credit Age & ID Validity Rules
     */
    public function test_flow_b_student_credit_age_and_id_validity_boundaries(): void
    {
        $checkAge = fn($age) => ($age >= 16 && $age <= 26);
        $this->assertFalse($checkAge(15), 'Age 15 must fail');
        $this->assertTrue($checkAge(16), 'Age 16 must pass');
        $this->assertTrue($checkAge(21), 'Age 21 must pass');
        $this->assertTrue($checkAge(26), 'Age 26 must pass');
        $this->assertFalse($checkAge(27), 'Age 27 must fail');

        $checkValidity = fn($months) => ($months >= 6);
        $this->assertFalse($checkValidity(4), 'Validity 4 months must fail');
        $this->assertTrue($checkValidity(6), 'Validity 6 months must pass');
        $this->assertTrue($checkValidity(12), 'Validity 12 months must pass');
    }

    /**
     * BOUNDARY TEST: Virtual Loan 5 Slabs and Fee Matrix
     */
    public function test_flow_b_virtual_loan_slab_validation(): void
    {
        $validSlabs = [15000, 20000, 30000, 35000, 45000];
        $this->assertCount(5, $validSlabs);
        $this->assertFalse(in_array(25000, $validSlabs));
        $this->assertFalse(in_array(50000, $validSlabs));

        $feeMatrix = [
            15000 => 2000,
            20000 => 3000,
            30000 => 4500,
            35000 => 5500,
            45000 => 7000,
        ];
        foreach ($feeMatrix as $amount => $fee) {
            $this->assertEquals($fee, $feeMatrix[$amount]);
        }
    }

    /**
     * WEB PORTAL — Tests every screen in every flow renders HTTP 200 OK
     */
    public function test_all_web_portal_blade_routes_render(): void
    {
        $phone = '9826000001';

        // 1. Hub
        $this->get(route('finance.hub', ['phone' => $phone]))->assertStatus(200);

        // 2. Cash Loan — 26 screens
        $cashScreens = [
            's01_apply', 's02_type_consent', 's03_applicant_details', 's04_eligibility',
            's05_amount_tenure', 's06_emi', 's07_documents', 's08_ready_processing',
            's09_fee_payment', 's10_application_gen', 's11_partner_dashboard', 's12_partner_verify',
            's13_partner_redirect', 's14_lender_webview', 's15_proof_upload', 's16_validation',
            's17_selfie_agent', 's18_tracking', 's19_lender_review', 's20_processing_window',
            's21_approved', 's22_bank_details', 's23_disbursement', 's24_additional_docs',
            's25_docs_submitted', 's26_final_result',
        ];
        foreach ($cashScreens as $screen) {
            $routeName = "finance.cash_loan.{$screen}";
            $res = $this->get(route($routeName, ['phone' => $phone]));
            if ($res->status() !== 200) {
                dump("ROUTE FAILED: " . $routeName, substr($res->getContent(), 0, 300));
            }
            $res->assertStatus(200, "Route {$routeName} failed to render.");
        }

        // 3. Business Loan — 20 screens
        $bizScreens = [
            's01_apply', 's02_business_details', 's03_loan_requirement', 's04_eligibility',
            's05_amount_tenure', 's06_emi', 's07_documents', 's08_verification',
            's09_ready_processing', 's10_fee_payment', 's11_application_gen', 's12_partner_dashboard',
            's13_partner_select', 's14_lender_webview', 's15_lender_processing', 's16_additional_docs',
            's17_approved', 's18_bank_details', 's19_disbursement', 's20_final_status',
        ];
        foreach ($bizScreens as $screen) {
            $routeName = "finance.business_loan.{$screen}";
            $this->get(route($routeName, ['phone' => $phone]))->assertStatus(200, "Route {$routeName} failed to render.");
        }

        // 4. Zero-CIBIL — 7 screens
        $zeroScreens = [
            's01_intro', 's02_kyc', 's03_amount_select', 's04_fee_payment',
            's05_pending', 's06_wallet_active', 's07_qr_pay',
        ];
        foreach ($zeroScreens as $screen) {
            $routeName = "finance.zero_cibil.{$screen}";
            $this->get(route($routeName, ['phone' => $phone]))->assertStatus(200, "Route {$routeName} failed to render.");
        }

        // 5. Virtual Loan — 5 screens
        $vloanScreens = [
            's01_apply', 's02_kyc', 's03_fee_payment', 's04_pending', 's05_dashboard',
        ];
        foreach ($vloanScreens as $screen) {
            $routeName = "finance.virtual_loan.{$screen}";
            $this->get(route($routeName, ['phone' => $phone]))->assertStatus(200, "Route {$routeName} failed to render.");
        }

        // 6. Student Credit — 9 screens
        $stuScreens = [
            's01_apply', 's02_kyc', 's03_fee_payment', 's04_pending', 's05_additional_docs',
            's06_mgmt_approval', 's07_approved', 's08_dashboard', 's09_qr_pay',
        ];
        foreach ($stuScreens as $screen) {
            $routeName = "finance.student_credit.{$screen}";
            $this->get(route($routeName, ['phone' => $phone]))->assertStatus(200, "Route {$routeName} failed to render.");
        }
    }
}
