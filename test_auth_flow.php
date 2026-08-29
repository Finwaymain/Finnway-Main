<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\API\v1\AuthOtpController;
use Illuminate\Support\Facades\DB;

echo "=== STARTING AUTH & REFERRAL AUTOMATED TEST ===\n\n";

$controller = new AuthOtpController();

$phoneA = '+919876543210';
$phoneB = '+919876543211';
$mpinA  = '1234';
$mpinB  = '5678';

// Clean up existing test users
echo "[1] Cleaning up old test data for $phoneA and $phoneB...\n";
DB::table('tj_user_app')->whereIn('phone', [$phoneA, $phoneB])->delete();
DB::table('tj_conducteur')->whereIn('phone', [$phoneA, $phoneB])->delete();
DB::table('common_user_base')->whereIn('user_id', function($q) use ($phoneA, $phoneB) {
    $q->select('id')->from('tj_user_app')->whereIn('phone', [$phoneA, $phoneB]);
})->delete();
echo "Cleaned up successfully.\n\n";

// Test 1: Register User A (Referrer)
echo "[2] Registering User A ($phoneA)...\n";
$reqA = Request::create('/api/v1/auth/register-simple', 'POST', [
    'phone' => $phoneA,
    'otp' => '1234',
    'mpin' => $mpinA,
    'firstname' => 'TestUserA',
    'lastname' => 'Referrer',
    'user_cat' => 'customer',
]);

$resA = $controller->registerSimple($reqA);
$dataA = json_decode($resA->getContent(), true);
echo "Register User A Result: " . json_encode($dataA, JSON_PRETTY_PRINT) . "\n\n";

if (($dataA['success'] ?? '') !== 'success') {
    echo "❌ User A Registration Failed!\n";
    exit(1);
}

$userA_id = $dataA['data']['id'];

// Get Referral Code for User A
$referralRowA = DB::table('referral')->where('user_id', $userA_id)->first();
$codeA = $referralRowA->referral_code ?? null;
echo "User A Referral Code in DB: " . ($codeA ?? 'NONE') . "\n\n";

// Test 2: Register User B (Referred by User A)
echo "[3] Registering User B ($phoneB) using User A's referral code ($codeA)...\n";
$reqB = Request::create('/api/v1/auth/register-simple', 'POST', [
    'phone' => $phoneB,
    'otp' => '1234',
    'mpin' => $mpinB,
    'firstname' => 'TestUserB',
    'lastname' => 'Referred',
    'user_cat' => 'customer',
    'referral_code' => $codeA,
]);

$resB = $controller->registerSimple($reqB);
$dataB = json_decode($resB->getContent(), true);
echo "Register User B Result: " . json_encode($dataB, JSON_PRETTY_PRINT) . "\n\n";

if (($dataB['success'] ?? '') !== 'success') {
    echo "❌ User B Registration Failed!\n";
    exit(1);
}

$userB_id = $dataB['data']['id'];

// Check referral connection in DB
$referralRowB = DB::table('referral')->where('user_id', $userB_id)->first();
echo "User B Referral Record in DB: " . json_encode($referralRowB, JSON_PRETTY_PRINT) . "\n\n";

// Test 3: Login User A by MPIN
echo "[4] Testing Login by MPIN for User A (Referrer)...\n";
$loginReqA = Request::create('/api/v1/auth/login-by-mpin', 'POST', [
    'phone' => $phoneA,
    'mpin' => $mpinA,
    'user_cat' => 'customer',
]);

$loginResA = $controller->loginByMpin($loginReqA);
$loginDataA = json_decode($loginResA->getContent(), true);
echo "User A Login Result: " . json_encode($loginDataA, JSON_PRETTY_PRINT) . "\n\n";

// Test 4: Login User B by MPIN
echo "[5] Testing Login by MPIN for User B (Referred)...\n";
$loginReqB = Request::create('/api/v1/auth/login-by-mpin', 'POST', [
    'phone' => $phoneB,
    'mpin' => $mpinB,
    'user_cat' => 'customer',
]);

$loginResB = $controller->loginByMpin($loginReqB);
$loginDataB = json_decode($loginResB->getContent(), true);
echo "User B Login Result: " . json_encode($loginDataB, JSON_PRETTY_PRINT) . "\n\n";

// Test 5: Driver Registration & Login
echo "[6] Testing Driver Registration & MPIN Login...\n";
$driverPhone = '+919876543212';
DB::table('tj_conducteur')->where('phone', $driverPhone)->delete();

$driverReq = Request::create('/api/v1/auth/register-simple', 'POST', [
    'phone' => $driverPhone,
    'otp' => '1234',
    'mpin' => '9999',
    'firstname' => 'DriverTester',
    'lastname' => 'Driver',
    'user_cat' => 'driver',
    'referral_code' => $codeA,
]);

$driverRes = $controller->registerSimple($driverReq);
$driverData = json_decode($driverRes->getContent(), true);
echo "Driver Registration Result: " . json_encode($driverData, JSON_PRETTY_PRINT) . "\n\n";

$driverLoginReq = Request::create('/api/v1/auth/login-by-mpin', 'POST', [
    'phone' => $driverPhone,
    'mpin' => '9999',
    'user_cat' => 'driver',
]);

$driverLoginRes = $controller->loginByMpin($driverLoginReq);
$driverLoginData = json_decode($driverLoginRes->getContent(), true);
echo "Driver Login Result: " . json_encode($driverLoginData, JSON_PRETTY_PRINT) . "\n\n";

echo "=== ALL AUTOMATED TESTS COMPLETED ===\n";
