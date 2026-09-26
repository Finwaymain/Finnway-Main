<?php

namespace Tests\Feature;

use App\Models\Finance\FinanceCustomer;
use App\Models\Finance\FinanceDocument;
use App\Models\Finance\FinanceLenderPartner;
use App\Models\Finance\FinanceLoanApplication;
use App\Models\Finance\FinanceLoanProduct;
use App\Models\Finance\FinanceWallet;
use App\Models\Finance\FinanceDailySchedule;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FinanceEcosystemTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /**
     * Test 1: Finance Context Initialization
     */
    public function test_finance_context_initialization(): void
    {
        $response = $this->getJson('/api/v1/finance/context?phone=9826000001&name=Test+Driver&user_type=driver');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('finance_customers', [
            'name' => 'Test Driver',
            'user_type' => 'driver',
        ]);
    }

    /**
     * Test 2: Product Listing & Eligibility Calculator
     */
    public function test_product_listing_and_eligibility_calculator(): void
    {
        // 1. Products
        $prodRes = $this->getJson('/api/v1/finance/products');
        $prodRes->assertStatus(200)
            ->assertJson(['success' => true]);

        // 2. Eligibility
        $calcRes = $this->postJson('/api/v1/finance/calculate-eligibility', [
            'product_code' => 'zero_cibil_daily',
            'requested_amount' => 30000,
            'tenure_months' => 24,
        ]);

        $calcRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'product_code' => 'zero_cibil_daily',
                    'requested_amount' => 30000,
                ],
            ]);
    }

    /**
     * Test 3: Application Initiation with ALL 4 Mandatory KYC Documents
     */
    public function test_application_initiation_with_all_4_kyc_documents(): void
    {
        $dummyFront = UploadedFile::fake()->create('aadhaar_front.jpg', 150, 'image/jpeg');
        $dummyBack = UploadedFile::fake()->create('aadhaar_back.jpg', 150, 'image/jpeg');
        $dummyPan = UploadedFile::fake()->create('pan_card.jpg', 150, 'image/jpeg');
        $dummyPassbook = UploadedFile::fake()->create('bank_passbook.jpg', 150, 'image/jpeg');

        $response = $this->post('/api/v1/finance/applications/initiate', [
            'applicant_phone' => '9826112233',
            'applicant_name' => 'Vikas Malviya',
            'product_code' => 'zero_cibil_daily',
            'requested_amount' => 30000,
            'tenure_months' => 24,
            'pan' => 'VMALV1234K',
            'city' => 'Ujjain',
            'pincode' => '456001',
            'gender' => 'Male',
            'employment' => 'Driver Partner',
            'aadhaar_front' => $dummyFront,
            'aadhaar_back' => $dummyBack,
            'pan_card' => $dummyPan,
            'bank_passbook' => $dummyPassbook,
        ], ['Accept' => 'application/json']);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Application created successfully.',
            ]);

        $appData = $response->json('data');
        $this->assertNotEmpty($appData['application_number']);
        $this->assertEquals('30000.00', $appData['requested_amount']);

        // Verify customer created
        $customer = FinanceCustomer::where('phone', '+919826112233')->first();
        $this->assertNotNull($customer);
        $this->assertEquals('VMALV1234K', $customer->pan);

        // Verify all 4 documents are stored in database
        $this->assertDatabaseHas('finance_documents', [
            'customer_id' => $customer->id,
            'document_type' => 'aadhaar_front',
        ]);
        $this->assertDatabaseHas('finance_documents', [
            'customer_id' => $customer->id,
            'document_type' => 'aadhaar_back',
        ]);
        $this->assertDatabaseHas('finance_documents', [
            'customer_id' => $customer->id,
            'document_type' => 'pan_card',
        ]);
        $this->assertDatabaseHas('finance_documents', [
            'customer_id' => $customer->id,
            'document_type' => 'bank_passbook',
        ]);
    }

    /**
     * Test 4: Processing Fee Payment Confirmation
     */
    public function test_processing_fee_payment_confirmation(): void
    {
        $app = FinanceLoanApplication::firstOrCreate(
            ['application_number' => 'FIIN-TEST-FEE-001'],
            [
                'customer_id' => 1,
                'user_type' => 'driver',
                'applicant_name' => 'Fee Test Applicant',
                'applicant_phone' => '+919826000001',
                'loan_category' => 'zero_cibil_daily',
                'requested_amount' => 30000,
                'processing_fee_amount' => 1999,
                'processing_fee_tax' => 359.82,
                'processing_fee_total' => 2358.82,
                'processing_fee_status' => 'pending',
                'application_status' => 'APPLICATION_CREATED',
            ]
        );

        $response = $this->postJson("/api/v1/finance/applications/{$app->id}/confirm-fee", [
            'payment_method' => 'UPI',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Processing fee payment recorded.',
            ]);

        $this->assertDatabaseHas('finance_loan_applications', [
            'id' => $app->id,
            'processing_fee_status' => 'paid',
            'application_status' => 'FEE_PAID',
        ]);
    }

    /**
     * Test 5: Single Lender Partner Lock
     */
    public function test_single_lender_partner_lock(): void
    {
        $partner = FinanceLenderPartner::firstOrCreate(
            ['name' => 'HDFC Bank Limited'],
            [
                'min_loan_amount' => 50000,
                'max_loan_amount' => 4000000,
                'interest_rate_display' => '10.5% p.a.',
                'tenure_display' => '12 - 60 Months',
                'application_url' => 'https://www.hdfcbank.com',
                'status' => 'active',
            ]
        );

        $app = FinanceLoanApplication::firstOrCreate(
            ['application_number' => 'FIIN-TEST-LOCK-001'],
            [
                'customer_id' => 1,
                'user_type' => 'driver',
                'applicant_name' => 'Lock Test Applicant',
                'applicant_phone' => '+919826000001',
                'loan_category' => 'cash_loan_low_cibil',
                'requested_amount' => 50000,
                'application_status' => 'FEE_PAID',
            ]
        );

        $response = $this->postJson("/api/v1/finance/applications/{$app->id}/select-partner", [
            'partner_id' => $partner->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Partner selected and locked. Proceed to partner portal.',
            ]);

        $this->assertDatabaseHas('finance_loan_applications', [
            'id' => $app->id,
            'selected_lender_id' => $partner->id,
            'application_status' => 'PARTNER_SELECTED',
        ]);
    }

    /**
     * Test 6: Proof & Selfie Upload
     */
    public function test_process_completion_proof_and_selfie_upload(): void
    {
        $app = FinanceLoanApplication::firstOrCreate(
            ['application_number' => 'FIIN-TEST-PROOF-001'],
            [
                'customer_id' => 1,
                'user_type' => 'driver',
                'applicant_name' => 'Proof Test Applicant',
                'applicant_phone' => '+919826000001',
                'loan_category' => 'zero_cibil_daily',
                'requested_amount' => 30000,
                'application_status' => 'PARTNER_SELECTED',
            ]
        );

        // Upload proof
        $proofFile = UploadedFile::fake()->create('completion_proof.jpg', 120, 'image/jpeg');
        $resProof = $this->post("/api/v1/finance/applications/{$app->id}/upload-proof", [
            'proof' => $proofFile,
            'remarks' => 'Completed on bank portal',
        ], ['Accept' => 'application/json']);

        $resProof->assertStatus(200)
            ->assertJson(['success' => true]);

        // Upload selfie
        $selfieFile = UploadedFile::fake()->create('live_selfie.jpg', 120, 'image/jpeg');
        $resSelfie = $this->post("/api/v1/finance/applications/{$app->id}/upload-selfie", [
            'selfie' => $selfieFile,
        ], ['Accept' => 'application/json']);

        $resSelfie->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('finance_loan_applications', [
            'id' => $app->id,
            'application_status' => 'AGENT_VERIFIED',
        ]);
    }

    /**
     * Test 7: Disbursement Account Submission
     */
    public function test_disbursement_account_submission(): void
    {
        $app = FinanceLoanApplication::firstOrCreate(
            ['application_number' => 'FIIN-TEST-DISB-001'],
            [
                'customer_id' => 1,
                'user_type' => 'driver',
                'applicant_name' => 'Disb Test Applicant',
                'applicant_phone' => '+919826000001',
                'loan_category' => 'zero_cibil_daily',
                'requested_amount' => 30000,
                'application_status' => 'PROOF_SUBMITTED',
            ]
        );

        $response = $this->postJson("/api/v1/finance/applications/{$app->id}/disbursement-account", [
            'account_name' => 'Vikas Malviya',
            'bank_name' => 'State Bank of India',
            'account_number' => '30928475932',
            'ifsc' => 'SBIN0001234',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Disbursement details submitted.',
            ]);

        $this->assertDatabaseHas('finance_loan_applications', [
            'id' => $app->id,
            'disbursement_bank_name' => 'State Bank of India',
            'disbursement_account_number' => '30928475932',
            'disbursement_ifsc' => 'SBIN0001234',
            'disbursement_status' => 'processing',
        ]);
    }

    /**
     * Test 8: Daily Repayment & Usage Unlock Engine
     */
    public function test_daily_repayment_and_usage_unlock_engine(): void
    {
        $customer = FinanceCustomer::firstOrCreate(
            ['phone' => '+919826000001'],
            ['name' => 'Vikas Malviya', 'user_type' => 'driver']
        );

        $wallet = FinanceWallet::updateOrCreate(
            ['customer_id' => $customer->id, 'wallet_type' => 'virtual_loan'],
            [
                'approved_limit' => 30000,
                'available_balance' => 20000,
                'daily_usage_limit' => 5000,
                'today_usage_permission' => 'LOCKED', // Initially locked
                'status' => 'active',
            ]
        );

        FinanceDailySchedule::updateOrCreate(
            [
                'customer_id' => $customer->id,
                'schedule_date' => date('Y-m-d'),
            ],
            [
                'application_id' => 1,
                'principal_amount' => 800,
                'interest_amount' => 200,
                'total_due' => 1000,
                'paid_amount' => 0,
                'status' => 'pending',
            ]
        );

        $response = $this->postJson('/api/v1/finance/daily-repayment', [
            'phone' => '9826000001',
            'payment_amount' => 1000.00,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // Assert wallet is now ACTIVE
        $wallet->refresh();
        $this->assertEquals('ACTIVE', $wallet->today_usage_permission);
    }
}
