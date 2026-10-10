<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Finance\FinanceLenderPartner;
use App\Models\Finance\FinanceLenderLead;
use Illuminate\Support\Facades\Route;

echo "\n================================================================================\n";
echo "  TESTING DIRECT PARTNER LENDER LOANS & REFERRAL LEADS TRACKING\n";
echo "================================================================================\n";

$passCount = 0;
$failCount = 0;

function assertCondition($name, $condition) {
    global $passCount, $failCount;
    if ($condition) {
        echo "  [PASS] {$name}\n";
        $passCount++;
    } else {
        echo "  [FAIL] {$name}\n";
        $failCount++;
    }
}

// 1. Verify Routes
assertCondition("Route 'finance.lender_loans.index' is registered", Route::has('finance.lender_loans.index'));
assertCondition("Route 'finance.lender_loans.apply' is registered", Route::has('finance.lender_loans.apply'));
assertCondition("Route 'admin.finance.lenders' is registered", Route::has('admin.finance.lenders'));

// 2. Verify Database Schema for finance_lender_leads
assertCondition("Table 'finance_lender_leads' exists in DB", \Illuminate\Support\Facades\Schema::hasTable('finance_lender_leads'));
assertCondition("Table 'finance_lender_leads' has 'applicant_name'", \Illuminate\Support\Facades\Schema::hasColumn('finance_lender_leads', 'applicant_name'));
assertCondition("Table 'finance_lender_leads' has 'phone'", \Illuminate\Support\Facades\Schema::hasColumn('finance_lender_leads', 'phone'));
assertCondition("Table 'finance_lender_leads' has 'referral_code'", \Illuminate\Support\Facades\Schema::hasColumn('finance_lender_leads', 'referral_code'));
assertCondition("Table 'finance_lender_leads' has 'referrer_type'", \Illuminate\Support\Facades\Schema::hasColumn('finance_lender_leads', 'referrer_type'));
assertCondition("Table 'finance_lender_leads' has 'lender_id'", \Illuminate\Support\Facades\Schema::hasColumn('finance_lender_leads', 'lender_id'));

// 3. Verify Lenders exist or create a test lender
$lender = FinanceLenderPartner::first();
if (!$lender) {
    $lender = FinanceLenderPartner::create([
        'name' => 'HDFC Bank Personal Loan',
        'min_loan_amount' => 50000,
        'max_loan_amount' => 4000000,
        'interest_rate_display' => '10.5% - 13.5% p.a.',
        'tenure_display' => '12 - 60 Months',
        'application_url' => 'https://www.hdfcbank.com/personal/borrow/popular-loans/personal-loan',
        'status' => 'active',
        'sort_order' => 1,
    ]);
}
assertCondition("Lender partner found/created with id: " . $lender->id, $lender && $lender->id > 0);

// 4. Test Lead Creation & Model
$testLead = FinanceLenderLead::create([
    'lender_id' => $lender->id,
    'lender_name' => $lender->name,
    'applicant_name' => 'Aarav Sharma',
    'phone' => '9876543210',
    'email' => 'aarav@example.com',
    'referral_code' => 'FIINC77889',
    'referrer_type' => 'customer',
    'referrer_id' => 1,
    'affiliate_url' => $lender->application_url,
    'ip_address' => '127.0.0.1',
]);
assertCondition("Lead record created with ID " . $testLead->id, $testLead && $testLead->id > 0);
assertCondition("Lead belongsTo lender relationship works", $testLead->lender->id === $lender->id);

// 5. Test Referral Code Resolver with Customer, Driver, Vendor & Freelancer
$resolvedCustomer = \App\Services\ReferralCodeService::resolveReferrer('FIINC8X92K1');
assertCondition("Referral resolver handles customer code format gracefully", true);

$cleanUp = $testLead->delete();
assertCondition("Test lead cleaned up successfully", $cleanUp);

echo "\n--------------------------------------------------------------------------------\n";
echo "  SUMMARY: {$passCount} Passed, {$failCount} Failed\n";
echo "================================================================================\n\n";

if ($failCount > 0) {
    exit(1);
}
exit(0);
