<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=================================================================\n";
echo "  FIINWAY SERVICE REFERRAL CASHBACK TEST SUITE\n";
echo "=================================================================\n\n";

$passCount = 0;
$failCount = 0;

// ─────────────────────────────────────────────────────────────
// Step 1: Find or select Referrer User A (e.g. user_id = 3)
// ─────────────────────────────────────────────────────────────
$userA = \Illuminate\Support\Facades\DB::table('tj_user_app')->where('id', 3)->first();
if (!$userA) {
    $userA = \Illuminate\Support\Facades\DB::table('tj_user_app')->orderBy('id')->first();
}
$referrerId = $userA->id;
$initialWalletA = (float)$userA->amount;

$userACode = \Illuminate\Support\Facades\DB::table('referral')->where('user_id', $referrerId)->value('referral_code')
    ?: ('FIIN' . str_pad((string)$referrerId, 6, '0', STR_PAD_LEFT));

echo "--- STEP 1: Referrer User A (ID: $referrerId) ---\n";
echo "  Referral Code   : $userACode\n";
echo "  Initial Wallet  : Rs.$initialWalletA\n\n";

// ─────────────────────────────────────────────────────────────
// Step 2: Create Consumer Referee User B
// ─────────────────────────────────────────────────────────────
$phoneB = '+9197' . rand(10000000, 99999999);
$userDataB = [
    'nom' => 'ConsumerB',
    'prenom' => 'Test',
    'phone' => $phoneB,
    'mdp' => md5('1234'),
    'statut' => 'yes',
    'creer' => date('Y-m-d H:i:s'),
    'amount' => 0.00
];
if (\Illuminate\Support\Facades\Schema::hasColumn('tj_user_app', 'referral_code')) {
    $userDataB['referral_code'] = 'TEMP';
}
$userBId = \Illuminate\Support\Facades\DB::table('tj_user_app')->insertGetId($userDataB);
$userBCode = 'FIIN' . str_pad((string)$userBId, 6, '0', STR_PAD_LEFT);

if (\Illuminate\Support\Facades\Schema::hasColumn('tj_user_app', 'referral_code')) {
    \Illuminate\Support\Facades\DB::table('tj_user_app')->where('id', $userBId)->update(['referral_code' => $userBCode]);
}

\Illuminate\Support\Facades\DB::table('referral')->updateOrInsert(
    ['user_id' => $userBId],
    [
        'referral_code' => $userBCode,
        'referral_by_id' => $referrerId,
        'user_type' => 'customer',
        'code_used' => 'true',
        'creer' => date('Y-m-d H:i:s'),
    ]
);

echo "--- STEP 2: Consumer User B Joined (ID: $userBId) ---\n";
echo "  Phone           : $phoneB\n";
echo "  Referred by     : User A ($userACode)\n\n";

// ─────────────────────────────────────────────────────────────
// Step 3: Consumer User B Uses a Service (₹500 booking)
// ─────────────────────────────────────────────────────────────
$serviceAmount = 500.00;
echo "--- STEP 3: Consumer User B Completed Service Booking (Rs.$serviceAmount) ---\n";

$rewardRes = \App\Services\ReferralRewardService::processReward(
    $userBId,
    'customer',
    'service_booking',
    $serviceAmount,
    'Home AC Service Booking'
);

$walletAfterService = (float)\Illuminate\Support\Facades\DB::table('tj_user_app')->where('id', $referrerId)->value('amount');
$serviceRewardEarned = $walletAfterService - $initialWalletA;

echo "  Reward Processed: " . (!empty($rewardRes['reward_processed']) ? 'YES' : 'NO') . "\n";
echo "  Reward Mode     : " . ($rewardRes['reward_mode'] ?? 'N/A') . "\n";
echo "  Reward Value    : " . ($rewardRes['reward_value'] ?? 'N/A') . "\n";
echo "  Reward Amount   : Rs." . ($rewardRes['reward_amount'] ?? 0) . "\n";
echo "  User A Wallet   : Rs.$initialWalletA -> Rs.$walletAfterService (Earned: Rs.$serviceRewardEarned)\n";

if (!empty($rewardRes['reward_processed']) && $serviceRewardEarned > 0) {
    echo "  [PASS] User A received dynamic cashback (Rs.$serviceRewardEarned) when Consumer B used service\n";
    $passCount++;
} else {
    echo "  [FAIL] User A did not receive cashback for Consumer B service\n";
    $failCount++;
}

// ─────────────────────────────────────────────────────────────
// Step 4: Create Driver Referee D
// ─────────────────────────────────────────────────────────────
$phoneD = '+9198' . rand(10000000, 99999999);
$driverDataD = [
    'nom' => 'DriverD',
    'prenom' => 'Partner',
    'phone' => $phoneD,
    'mdp' => md5('1234'),
    'statut' => 'yes',
    'creer' => date('Y-m-d H:i:s'),
    'amount' => 0.00
];
if (\Illuminate\Support\Facades\Schema::hasColumn('tj_conducteur', 'referral_code')) {
    $driverDataD['referral_code'] = 'TEMP';
}
$driverDId = \Illuminate\Support\Facades\DB::table('tj_conducteur')->insertGetId($driverDataD);
$driverDCode = 'FIIN' . str_pad((string)$driverDId, 6, '0', STR_PAD_LEFT);

if (\Illuminate\Support\Facades\Schema::hasColumn('tj_conducteur', 'referral_code')) {
    \Illuminate\Support\Facades\DB::table('tj_conducteur')->where('id', $driverDId)->update(['referral_code' => $driverDCode]);
}

\Illuminate\Support\Facades\DB::table('referral')->updateOrInsert(
    ['user_id' => $driverDId],
    [
        'referral_code' => $driverDCode,
        'referral_by_id' => $referrerId,
        'user_type' => 'driver',
        'code_used' => 'true',
        'creer' => date('Y-m-d H:i:s'),
    ]
);

echo "\n--- STEP 4: Driver Partner D Joined (ID: $driverDId) ---\n";
echo "  Phone           : $phoneD\n";
echo "  Referred by     : User A ($userACode)\n\n";

// ─────────────────────────────────────────────────────────────
// Step 5: Driver D Completes a Transport Ride (₹1,000 ride)
// ─────────────────────────────────────────────────────────────
$rideAmount = 1000.00;
echo "--- STEP 5: Driver D Completed Ride Service (Rs.$rideAmount) ---\n";

$driverRewardRes = \App\Services\ReferralRewardService::processReward(
    $driverDId,
    'driver',
    'cab_driver',
    $rideAmount,
    'Cab Ride Service'
);

$walletAfterDriverRide = (float)\Illuminate\Support\Facades\DB::table('tj_user_app')->where('id', $referrerId)->value('amount');
$driverRewardEarned = $walletAfterDriverRide - $walletAfterService;

echo "  Reward Processed: " . (!empty($driverRewardRes['reward_processed']) ? 'YES' : 'NO') . "\n";
echo "  Reward Mode     : " . ($driverRewardRes['reward_mode'] ?? 'N/A') . "\n";
echo "  Reward Value    : " . ($driverRewardRes['reward_value'] ?? 'N/A') . "\n";
echo "  Reward Amount   : Rs." . ($driverRewardRes['reward_amount'] ?? 0) . "\n";
echo "  User A Wallet   : Rs.$walletAfterService -> Rs.$walletAfterDriverRide (Earned: Rs.$driverRewardEarned)\n";

if (!empty($driverRewardRes['reward_processed']) && $driverRewardEarned > 0) {
    echo "  [PASS] User A received dynamic cashback (Rs.$driverRewardEarned) when Driver D completed a service\n";
    $passCount++;
} else {
    echo "  [FAIL] User A did not receive cashback for Driver D service\n";
    $failCount++;
}

// ─────────────────────────────────────────────────────────────
// Step 6: Verify Referral Dashboard Shows Both in Correct Tabs
// ─────────────────────────────────────────────────────────────
echo "\n--- STEP 6: Verify Dashboard Separation for User A ---\n";

$controller = new \App\Http\Controllers\API\v1\ReferralDashboardAPIController();
$request = new \Illuminate\Http\Request(['user_id' => $referrerId]);
$dashboardJson = $controller->getStats($request)->getData(true);
$dData = $dashboardJson['data'];

$consumerCount = $dData['consumer']['total_referrals'];
$businessCount = $dData['business']['total_referrals'];

echo "  Consumer Network Tab Referees : $consumerCount\n";
echo "  Business Partners Tab Referees: $businessCount\n";

if ($consumerCount > 0) {
    echo "  [PASS] Consumer Network tab correctly shows referred consumers\n";
    $passCount++;
} else {
    echo "  [FAIL] Consumer Network tab has 0 consumers\n";
    $failCount++;
}

if ($businessCount > 0) {
    echo "  [PASS] Business Partners tab correctly shows referred drivers\n";
    $passCount++;
} else {
    echo "  [FAIL] Business Partners tab has 0 business partners\n";
    $failCount++;
}

// ─────────────────────────────────────────────────────────────
// SUMMARY
// ─────────────────────────────────────────────────────────────
echo "\n=================================================================\n";
$total = $passCount + $failCount;
echo "  TOTAL: $passCount / $total PASSED | $failCount FAILED\n";
echo "  Final Referrer Wallet Balance: Rs.$walletAfterDriverRide\n";
echo "=================================================================\n";
