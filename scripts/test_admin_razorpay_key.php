<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PaymentSettings;
use App\Http\Controllers\Finance\FinanceWebController;
use Illuminate\Http\Request;

echo "========================================================\n";
echo "  TESTING ADMIN-CONFIGURED RAZORPAY KEY RESOLUTION \n";
echo "========================================================\n\n";

$setting = PaymentSettings::where('id_payment_method', 13)->first();
assert($setting !== null, "PaymentSettings for Razorpay (id_payment_method 13) should exist in DB");
$originalKey = $setting->key;

// 1. Simulate Admin setting key in Admin Panel -> Settings -> Payment -> Razorpay
$adminKey = 'rzp_live_admin_panel_sample_key_' . time();
echo "Step 1: Admin configures Razorpay key in Admin Panel to: {$adminKey}\n";
$setting->key = $adminKey;
$setting->save();

// 2. Resolve Context in Finance Controller
echo "Step 2: Resolving context in user-facing fee payment screen...\n";
$controller = new FinanceWebController();
$req = Request::create('/finance/zero-cibil/fee-payment', 'GET', [
    'phone' => '9876543210'
]);
$view = $controller->zeroCibilFeePayment($req);
$viewData = $view->getData();

assert($viewData['razorpayKey'] === $adminKey, "User side MUST receive exact Razorpay key configured by admin in Admin Panel. Expected: {$adminKey}, Got: " . $viewData['razorpayKey']);
echo " [PASS] User screen received exact Admin Panel Razorpay key: " . $viewData['razorpayKey'] . "\n";

// 3. Restore original key
$setting->key = $originalKey;
$setting->save();
echo " [PASS] Restored original DB setting.\n";

echo "\n========================================================\n";
echo "  ADMIN RAZORPAY KEY VERIFICATION PASSED (100%)!\n";
echo "========================================================\n";
