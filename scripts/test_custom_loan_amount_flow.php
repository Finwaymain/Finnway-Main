<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Finance\FinanceCustomer;
use App\Models\Finance\FinanceLoanApplication;
use Illuminate\Http\Request;
use App\Http\Controllers\Finance\FinanceWebController;
use App\Http\Controllers\Admin\FinanceAdminController;

echo "========================================================\n";
echo "  TESTING CUSTOM LOAN AMOUNT FLOW & DYNAMIC DATA BINDING \n";
echo "========================================================\n\n";

$testPhone = '9876543210';

// 1. Clean previous test record if any
$customer = FinanceCustomer::where('phone', $testPhone)->first();
if ($customer) {
    FinanceLoanApplication::where('customer_id', $customer->id)->delete();
}

$controller = new FinanceWebController();

// 2. Submit Step 03 with custom loan amount Rs 25,000
echo "Step 1: Submitting Step 03 with custom amount Rs 25,000...\n";
$reqS03 = Request::create('/finance/cash-loan/save-step', 'POST', [
    'step' => 's03',
    'phone' => $testPhone,
    'applicant_name' => 'Amit Sharma',
    'dob' => '1995-05-15',
    'email' => 'amit.sharma@example.com',
    'pan_number' => 'ABCDE1234F',
    'monthly_income' => 35000,
    'requested_amount' => 25000,
    'loan_purpose' => 'Medical Expense'
]);

$respS03 = $controller->saveCashLoanStep($reqS03);
$appRow = FinanceLoanApplication::where('applicant_phone', $testPhone)->latest()->first();

assert($appRow !== null, "Application row should exist in database");
assert(floatval($appRow->requested_amount) === 25000.0, "Application requested_amount in DB must be exactly 25000, got: " . $appRow->requested_amount);
echo " [PASS] DB requested_amount saved as: Rs " . number_format($appRow->requested_amount) . "\n";

// 3. Submit Step 05 with tenure 18 months and confirmed 25,000
echo "\nStep 2: Submitting Step 05 with custom amount Rs 25,000 and tenure 18 months...\n";
$reqS05 = Request::create('/finance/cash-loan/save-step', 'POST', [
    'step' => 's05',
    'phone' => $testPhone,
    'amount' => 25000,
    'tenure' => 18
]);
$respS05 = $controller->saveCashLoanStep($reqS05);
$appRow->refresh();

assert(floatval($appRow->requested_amount) === 25000.0, "Application requested_amount after s05 must remain 25000");
assert(intval($appRow->tenure_months) === 18, "Tenure must be 18 months");
assert(floatval($appRow->processing_fee_base) === 999.0, "Processing fee base for 25k (2% with min 999) must be 999");
echo " [PASS] Step 05 updated: Amount = Rs " . number_format($appRow->requested_amount) . ", Tenure = " . $appRow->tenure_months . " months, Processing Fee = Rs " . number_format($appRow->processing_fee_total, 2) . "\n";

// 4. Verify context resolution for Step 06 (EMI Calculation)
echo "\nStep 3: Checking EMI calculation view data context...\n";
$reqS06 = Request::create('/finance/cash-loan/emi', 'GET', [
    'phone' => $testPhone
]);
$viewS06 = $controller->cashLoanEmi($reqS06);
$viewData = $viewS06->getData();

assert($viewData['amount'] === 25000.0, "Context amount passed to view must be 25000");
assert($viewData['tenure'] === 18, "Context tenure passed to view must be 18");
assert($viewData['emi'] > 0 && $viewData['emi'] < 2000, "Monthly EMI for 25k/18m at 9% should be approx Rs 1,490, got: " . $viewData['emi']);
assert($viewData['hasLender'] === true, "Cash loan must flag hasLender = true");
echo " [PASS] EMI screen context verified: Amount = Rs " . number_format($viewData['amount']) . ", Tenure = " . $viewData['tenure'] . ", Monthly EMI = Rs " . number_format($viewData['emi']) . ", hasLender = " . ($viewData['hasLender'] ? 'true' : 'false') . "\n";

// 5. Verify Step 10 context (App Generated & Flow Branching)
echo "\nStep 4: Checking Application Generated screen & dynamic flow branching...\n";
$reqS10 = Request::create('/finance/cash-loan/app-generated', 'GET', [
    'phone' => $testPhone
]);
$viewS10 = $controller->cashLoanApplicationGen($reqS10);
$viewDataS10 = $viewS10->getData();
assert($viewDataS10['amount'] === 25000.0, "App generated amount must be 25000");
assert($viewDataS10['hasLender'] === true, "App generated hasLender must be true for cash loan");
echo " [PASS] Step 10 generated application: Amount = Rs " . number_format($viewDataS10['amount']) . ", hasLender = true -> branches to Partner Dashboard\n";

// 6. Verify Admin Panel sees exact 25,000 requested
echo "\nStep 5: Verifying Admin Panel pipeline displays exact custom amount...\n";
$adminApp = FinanceLoanApplication::where('applicant_phone', $testPhone)->first();
assert($adminApp !== null, "Admin query must find application");
assert(floatval($adminApp->requested_amount) === 25000.0, "Admin view must show 25000, NOT 1,00,000 or 2,00,000");
echo " [PASS] Admin Panel pipeline displays: Applicant = " . $adminApp->applicant_name . ", Requested Amount = Rs " . number_format($adminApp->requested_amount) . "\n";

// 7. Verify Flow B (Zero-CIBIL) context has hasLender = false
echo "\nStep 6: Verifying Flow B (Zero-CIBIL) strictly skips lenders...\n";
$reqZero = Request::create('/finance/zero-cibil/fee-payment', 'GET', [
    'phone' => $testPhone,
    'amount' => 15000
]);
$viewZero = $controller->zeroCibilFeePayment($reqZero);
$zeroData = $viewZero->getData();
assert($zeroData['amount'] === 15000.0, "Zero-CIBIL amount must be 15000");
assert($zeroData['hasLender'] === false, "Zero-CIBIL hasLender must be FALSE");
assert($zeroData['totalFee'] > 0, "Zero-CIBIL total fee must be dynamically calculated");
echo " [PASS] Flow B verified: Amount = Rs " . number_format($zeroData['amount']) . ", hasLender = false (Zero external lenders), Total Fee = Rs " . number_format($zeroData['totalFee'], 2) . "\n";

echo "\n========================================================\n";
echo "  ALL VERIFICATION TESTS COMPLETED SUCCESSFULLY (100% PASS)!\n";
echo "========================================================\n";
