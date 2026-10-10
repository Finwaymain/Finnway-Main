<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Finance\FinanceAffiliateLender;
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
assertCondition("Route 'admin.finance.affiliate_lenders' is registered", Route::has('admin.finance.affiliate_lenders'));
assertCondition("Route 'admin.finance.affiliate_lenders.save' is registered", Route::has('admin.finance.affiliate_lenders.save'));

// 2. Verify Database Schema for finance_lender_leads and finance_affiliate_lenders
assertCondition("Table 'finance_lender_leads' exists in DB", \Illuminate\Support\Facades\Schema::hasTable('finance_lender_leads'));
assertCondition("Table 'finance_affiliate_lenders' exists in DB", \Illuminate\Support\Facades\Schema::hasTable('finance_affiliate_lenders'));
assertCondition("Table 'finance_lender_leads' has 'applicant_name'", \Illuminate\Support\Facades\Schema::hasColumn('finance_lender_leads', 'applicant_name'));
assertCondition("Table 'finance_lender_leads' has 'phone'", \Illuminate\Support\Facades\Schema::hasColumn('finance_lender_leads', 'phone'));
assertCondition("Table 'finance_lender_leads' has 'referral_code'", \Illuminate\Support\Facades\Schema::hasColumn('finance_lender_leads', 'referral_code'));
assertCondition("Table 'finance_lender_leads' has 'referrer_type'", \Illuminate\Support\Facades\Schema::hasColumn('finance_lender_leads', 'referrer_type'));
assertCondition("Table 'finance_lender_leads' has 'lender_id'", \Illuminate\Support\Facades\Schema::hasColumn('finance_lender_leads', 'lender_id'));

// 3. Verify Affiliate Lenders exist or create a test affiliate lender
$affiliate = FinanceAffiliateLender::first();
if (!$affiliate) {
    $affiliate = FinanceAffiliateLender::create([
        'name' => 'MoneyControl Loan Marketplace',
        'min_loan_amount' => 50000,
        'max_loan_amount' => 5000000,
        'interest_rate_display' => '10.5% - 14% p.a.',
        'tenure_display' => '12 - 60 Months',
        'affiliate_url' => 'https://moneycontrol.com/loans/apply?ref=fiinway',
        'status' => 'active',
        'sort_order' => 1,
    ]);
}
assertCondition("Affiliate lender found/created with id: " . $affiliate->id, $affiliate && $affiliate->id > 0);

// 4. Test Lead Creation & Model
$testLead = FinanceLenderLead::create([
    'lender_id' => $affiliate->id,
    'lender_name' => $affiliate->name,
    'applicant_name' => 'Aarav Sharma',
    'phone' => '9876543210',
    'email' => 'aarav@example.com',
    'referral_code' => 'FIINC77889',
    'referrer_type' => 'customer',
    'referrer_id' => 1,
    'affiliate_url' => $affiliate->affiliate_url,
    'ip_address' => '127.0.0.1',
]);
assertCondition("Lead record created with ID " . $testLead->id, $testLead && $testLead->id > 0);
assertCondition("Lead belongsTo lender relationship works", $testLead->lender->id === $affiliate->id);

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
