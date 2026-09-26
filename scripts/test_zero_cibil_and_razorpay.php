<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Finance\FinanceCustomer;
use App\Models\Finance\FinanceLoanApplication;
use App\Models\Finance\FinanceDocument;
use App\Models\Finance\FinanceTransaction;
use App\Http\Controllers\Finance\FinanceWebController;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

echo "========================================================\n";
echo "  TESTING ZERO-CIBIL FLOW, KYC UPLOADS & RAZORPAY GATEWAY \n";
echo "========================================================\n\n";

Storage::fake('public');
$testPhone = '919876500001';

// Cleanup previous test data
$customer = FinanceCustomer::where('phone', $testPhone)->first();
if ($customer) {
    FinanceLoanApplication::where('customer_id', $customer->id)->delete();
    FinanceDocument::where('customer_id', $customer->id)->delete();
    $customer->delete();
}

$controller = new FinanceWebController();

// 1. Submit Zero-CIBIL KYC with Files
echo "Step 1: Submitting Zero-CIBIL KYC with Aadhaar & PAN files...\n";
$dummyFile1 = UploadedFile::fake()->create('aadhaar_front.jpg', 100, 'image/jpeg');
$dummyFile2 = UploadedFile::fake()->create('aadhaar_back.jpg', 100, 'image/jpeg');
$dummyFile3 = UploadedFile::fake()->create('pan_card.jpg', 100, 'image/jpeg');

$reqKyc = Request::create('/finance/zero-cibil/save-kyc', 'POST', [
    'phone' => $testPhone,
    'applicant_name' => 'Rahul Verma',
    'pan_number' => 'ABCDE9999Z',
    'aadhaar_number' => '123456789012'
], [], [
    'aadhaar_front' => $dummyFile1,
    'aadhaar_back' => $dummyFile2,
    'pan_card' => $dummyFile3
]);

$controller->saveZeroCibilKyc($reqKyc);

$cust = FinanceCustomer::where('phone', $testPhone)->first();
assert($cust !== null, "Customer should be created");
assert($cust->name === 'Rahul Verma', "Customer name should match");
assert($cust->pan === 'ABCDE9999Z', "PAN should match");

$docsCount = FinanceDocument::where('customer_id', $cust->id)->count();
assert($docsCount === 3, "Exactly 3 KYC documents should be saved in DB, got: " . $docsCount);
echo " [PASS] Customer profile and 3 KYC documents successfully saved in DB.\n";

// 2. Select Amount Rs 35,000
echo "\nStep 2: Selecting Credit Limit Rs 35,000...\n";
$reqAmount = Request::create('/finance/zero-cibil/save-amount', 'POST', [
    'phone' => $testPhone,
    'amount' => 35000
]);
$controller->saveZeroCibilAmount($reqAmount);

$appRow = FinanceLoanApplication::where('customer_id', $cust->id)->latest('id')->first();
assert($appRow !== null, "Application should be created in DB");
assert(floatval($appRow->requested_amount) === 35000.0, "Requested amount must be 35000");
assert($appRow->application_status === 'DRAFT', "Initial status must be DRAFT");
assert(floatval($appRow->processing_fee_base) === 2500.0, "Admin product fee for Zero-CIBIL is 2500");
assert(floatval($appRow->processing_fee_total) === 2950.0, "Total fee with 18% GST should be 2950");
echo " [PASS] Application created in DB with amount Rs 35,000 and Admin product fee Rs " . number_format($appRow->processing_fee_total, 2) . "\n";

// 3. Verify Razorpay Payment Callback
echo "\nStep 3: Simulating Razorpay Payment Gateway Verification...\n";
$razorpayPaymentId = 'pay_rzp_test_' . time();
$reqPay = Request::create('/finance/fee-payment/verify', 'POST', [], [], [], [
    'CONTENT_TYPE' => 'application/json'
], json_encode([
    'phone' => $testPhone,
    'application_id' => $appRow->id,
    'payment_id' => $razorpayPaymentId,
    'amount' => 2950.0,
    'next_url' => '/finance/zero-cibil/pending?phone=' . $testPhone
]));

$respPay = $controller->verifyFeePayment($reqPay);
$payData = json_decode($respPay->getContent(), true);

assert($payData['success'] === true, "Payment verification must return success");
$appRow->refresh();
assert($appRow->fee_payment_status === 'paid', "Fee payment status must be 'paid'");
assert($appRow->application_status === 'UNDERWRITING', "Status must transition to UNDERWRITING, not auto approved");
assert($appRow->processing_fee_txn_id === $razorpayPaymentId, "Txn ID must match Razorpay payment ID");

$txn = FinanceTransaction::where('application_id', $appRow->id)->latest('id')->first();
assert($txn !== null, "FinanceTransaction record must be created in DB");
assert($txn->payment_method === 'razorpay', "Payment method in transaction must be razorpay");
echo " [PASS] Razorpay fee payment verified. Application status is UNDERWRITING (sent to admin review desk).\n";

// 4. Verify Active Wallet Screen is Protected from Premature Auto-Approval
echo "\nStep 4: Checking that user cannot bypass review to fake active wallet...\n";
$reqWallet = Request::create('/finance/zero-cibil/wallet-active', 'GET', [
    'phone' => $testPhone
]);
$respWallet = $controller->zeroCibilWalletActive($reqWallet);

assert($respWallet instanceof Illuminate\Http\RedirectResponse, "Must redirect back to pending review screen when not approved");
assert(strpos($respWallet->getTargetUrl(), 'pending') !== false, "Target URL must redirect to pending");
echo " [PASS] Security Gate Verified: User CANNOT jump to active wallet until admin approves.\n";

// 5. Simulate Admin Approval in Underwriting Desk
echo "\nStep 5: Simulating Admin Approval in Admin Underwriting Pipeline...\n";
$appRow->update(['application_status' => 'LOAN_APPROVED']);

// Create wallet as admin would upon approval
\App\Models\Finance\FinanceWallet::create([
    'customer_id' => $cust->id,
    'wallet_type' => 'virtual_loan',
    'approved_limit' => 35000,
    'available_balance' => 35000,
    'daily_usage_limit' => 5000,
    'today_usage_permission' => 'ACTIVE',
    'status' => 'active'
]);

$respWalletApproved = $controller->zeroCibilWalletActive($reqWallet);
assert(!($respWalletApproved instanceof Illuminate\Http\RedirectResponse), "Once approved, wallet screen must render successfully");
echo " [PASS] Once approved by Admin, user successfully accesses Active Wallet.\n";

echo "\n========================================================\n";
echo "  ALL ZERO-CIBIL & RAZORPAY TESTS COMPLETED (100% PASS)!  \n";
echo "========================================================\n";
