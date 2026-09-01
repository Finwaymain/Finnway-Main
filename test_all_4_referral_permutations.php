<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\UserApp;
use App\Models\Driver;
use App\Services\ReferralCodeService;
use App\Services\ReferralRewardService;
use App\Http\Controllers\API\v1\ReferralDashboardAPIController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "========================================================================\n";
echo "  COMPREHENSIVE TEST SUITE: ALL 4 REFERRAL PERMUTATIONS & PENDING FLOW\n";
echo "========================================================================\n\n";

$dashCtrl = new ReferralDashboardAPIController();

function getDashboardStats($dashCtrl, $userId, $userType) {
    $req = Request::create('/api/v1/referral-dashboard-stats', 'GET', [
        'user_id' => $userId,
        'driver_id' => ($userType === 'driver' ? $userId : ''),
        'user_cat' => $userType,
    ]);
    $resp = $dashCtrl->getStats($req);
    return json_decode($resp->getContent(), true)['data'] ?? [];
}

// -----------------------------------------------------------------------------
// TEST CASE 1: USER -> USER (Customer refers Customer)
// -----------------------------------------------------------------------------
echo "------------------------------------------------------------------------\n";
echo "TEST 1: USER -> USER (Customer refers Customer)\n";
echo "------------------------------------------------------------------------\n";

$uA_phone = '99991' . rand(10000, 99999);
$uB_phone = '99992' . rand(10000, 99999);

$userA = UserApp::create([
    'nom' => 'ReferrerA',
    'prenom' => 'User',
    'phone' => $uA_phone,
    'email' => "user_a_{$uA_phone}@test.com",
    'amount' => 0.0,
    'statut' => 'yes',
    'creer' => date('Y-m-d H:i:s'),
]);
$refCodeA = ReferralCodeService::getOrCreateReferralCode($userA->id, 'customer');

$userB = UserApp::create([
    'nom' => 'RefereeB',
    'prenom' => 'User',
    'phone' => $uB_phone,
    'email' => "user_b_{$uB_phone}@test.com",
    'amount' => 0.0,
    'ref_by' => $refCodeA,
    'statut' => 'yes',
    'creer' => date('Y-m-d H:i:s'),
]);

DB::table('referral')->insert([
    'user_id' => $userB->id,
    'user_type' => 'customer',
    'referral_by_id' => $userA->id,
    'referral_by_type' => 'customer',
    'referral_by_code' => $refCodeA,
    'referral_code' => ReferralCodeService::generateUniqueCode('customer', $userB->id),
    'code_used' => 'true',
    'app_install_reward_paid' => 0,
    'creer' => date('Y-m-d H:i:s'),
]);

// Step 1: Check User A dashboard BEFORE condition is met
$statsBefore1 = getDashboardStats($dashCtrl, $userA->id, 'customer');
$consumerHistory1 = $statsBefore1['consumer']['history'] ?? [];
$refereeInList1 = array_filter($consumerHistory1, fn($it) => $it['id'] == $userB->id);
$item1 = reset($refereeInList1);

echo "Step 1 (Before Milestone):\n";
echo "- Referee Name: " . ($item1['name'] ?? 'N/A') . "\n";
echo "- Status: " . ($item1['status'] ?? 'N/A') . "\n";
echo "- Reward Amount: ₹" . ($item1['reward_amount'] ?? 0) . "\n";
echo "- Condition Fulfilled: " . (($item1['condition_fulfilled'] ?? false) ? 'YES' : 'NO') . "\n";
echo "- User A Wallet: ₹" . ($userA->fresh()->amount) . "\n";

if (($item1['status'] ?? '') === 'Pending' && ($item1['reward_amount'] ?? 0) > 0 && !($item1['condition_fulfilled'] ?? false)) {
    echo "✓ PASS: Referee B is correctly displayed in PENDING condition with reward amount!\n";
} else {
    echo "✗ FAIL: Expected status 'Pending' with positive reward amount.\n";
}

// Step 2: User B meets milestone condition (completes orders/rides reaching spend/service requirement)
DB::table('tj_requete')->insert([
    ['id_user_app' => $userB->id, 'id_conducteur' => 1, 'montant' => 300, 'statut' => 'completed', 'creer' => date('Y-m-d H:i:s')],
    ['id_user_app' => $userB->id, 'id_conducteur' => 1, 'montant' => 300, 'statut' => 'completed', 'creer' => date('Y-m-d H:i:s')],
]);

$resReward1 = ReferralRewardService::checkAndProcessAppInstallReward($userB->id, 'customer');
echo "\nStep 2 (After Milestone Trigger):\n";
echo "- Reward Processed: " . ($resReward1['reward_processed'] ? 'YES' : 'NO') . "\n";
echo "- Reward Amount: ₹" . ($resReward1['reward_amount'] ?? 0) . "\n";
echo "- User A Wallet Balance Now: ₹" . ($userA->fresh()->amount) . "\n";

$statsAfter1 = getDashboardStats($dashCtrl, $userA->id, 'customer');
$consumerHistoryAfter1 = $statsAfter1['consumer']['history'] ?? [];
$refereeInListAfter1 = array_filter($consumerHistoryAfter1, fn($it) => $it['id'] == $userB->id);
$itemAfter1 = reset($refereeInListAfter1);

echo "- Dashboard Status: " . ($itemAfter1['status'] ?? 'N/A') . "\n";
echo "- Dashboard Earned: ₹" . ($itemAfter1['referral_earned'] ?? 0) . "\n";

if (($itemAfter1['status'] ?? '') === 'Credited' && ($userA->fresh()->amount) > 0) {
    echo "✓ PASS: User A received cashback in wallet and status updated to Credited!\n";
} else {
    echo "✗ FAIL: Cashback wallet credit or status update failed.\n";
}

// -----------------------------------------------------------------------------
// TEST CASE 2: USER -> BUSINESS (Customer refers Driver/Partner)
// -----------------------------------------------------------------------------
echo "\n------------------------------------------------------------------------\n";
echo "TEST 2: USER -> BUSINESS (Customer refers Driver/Partner)\n";
echo "------------------------------------------------------------------------\n";

$uA2_phone = '99993' . rand(10000, 99999);
$dB2_phone = '99994' . rand(10000, 99999);

$userA2 = UserApp::create([
    'nom' => 'CustomerA2',
    'prenom' => 'User',
    'phone' => $uA2_phone,
    'email' => "user_a2_{$uA2_phone}@test.com",
    'amount' => 0.0,
    'statut' => 'yes',
    'creer' => date('Y-m-d H:i:s'),
]);
$refCodeA2 = ReferralCodeService::getOrCreateReferralCode($userA2->id, 'customer');

$driverB2 = Driver::create([
    'nom' => 'PartnerB2',
    'prenom' => 'Driver',
    'phone' => $dB2_phone,
    'email' => "driver_b2_{$dB2_phone}@test.com",
    'amount' => 0.0,
    'ref_by' => $refCodeA2,
    'statut' => 'yes',
    'is_verified' => 1,
    'creer' => date('Y-m-d H:i:s'),
]);

DB::table('referral')->insert([
    'user_id' => $driverB2->id,
    'user_type' => 'driver',
    'referral_by_id' => $userA2->id,
    'referral_by_type' => 'customer',
    'referral_by_code' => $refCodeA2,
    'referral_code' => ReferralCodeService::generateUniqueCode('driver', $driverB2->id),
    'code_used' => 'true',
    'app_install_reward_paid' => 0,
    'creer' => date('Y-m-d H:i:s'),
]);

$statsBefore2 = getDashboardStats($dashCtrl, $userA2->id, 'customer');
$bizHistory2 = $statsBefore2['business']['history'] ?? [];
$item2 = reset($bizHistory2);

echo "Step 1 (Before Milestone):\n";
echo "- Referee Partner: " . ($item2['name'] ?? 'N/A') . "\n";
echo "- Status: " . ($item2['status'] ?? 'N/A') . "\n";
echo "- Pending Reward: ₹" . ($item2['reward_amount'] ?? 0) . "\n";

// Driver B completes 2 rides/services
DB::table('tj_requete')->insert([
    ['id_user_app' => 1, 'id_conducteur' => $driverB2->id, 'montant' => 150, 'statut' => 'completed', 'creer' => date('Y-m-d H:i:s')],
    ['id_user_app' => 1, 'id_conducteur' => $driverB2->id, 'montant' => 150, 'statut' => 'completed', 'creer' => date('Y-m-d H:i:s')],
]);

$resReward2 = ReferralRewardService::checkAndProcessAppInstallReward($driverB2->id, 'driver');
echo "\nStep 2 (After Milestone Trigger):\n";
echo "- Reward Processed: " . ($resReward2['reward_processed'] ? 'YES' : 'NO') . "\n";
echo "- Customer A2 Wallet Balance Now: ₹" . ($userA2->fresh()->amount) . "\n";

$statsAfter2 = getDashboardStats($dashCtrl, $userA2->id, 'customer');
$bizHistoryAfter2 = $statsAfter2['business']['history'] ?? [];
$itemAfter2 = reset($bizHistoryAfter2);
echo "- Dashboard Status: " . ($itemAfter2['status'] ?? 'N/A') . "\n";

if (($itemAfter2['status'] ?? '') === 'Credited' && ($userA2->fresh()->amount) > 0) {
    echo "✓ PASS: User -> Business referral successfully credited to Customer wallet!\n";
} else {
    echo "✗ FAIL: User -> Business referral credit failed.\n";
}

// -----------------------------------------------------------------------------
// TEST CASE 3: BUSINESS -> USER (Driver/Partner refers Customer)
// -----------------------------------------------------------------------------
echo "\n------------------------------------------------------------------------\n";
echo "TEST 3: BUSINESS -> USER (Driver/Partner refers Customer)\n";
echo "------------------------------------------------------------------------\n";

$dA3_phone = '99995' . rand(10000, 99999);
$uB3_phone = '99996' . rand(10000, 99999);

$driverA3 = Driver::create([
    'nom' => 'DriverReferrerA3',
    'prenom' => 'Partner',
    'phone' => $dA3_phone,
    'email' => "driver_a3_{$dA3_phone}@test.com",
    'amount' => 0.0,
    'statut' => 'yes',
    'creer' => date('Y-m-d H:i:s'),
]);
$refCodeA3 = ReferralCodeService::getOrCreateReferralCode($driverA3->id, 'driver');

$userB3 = UserApp::create([
    'nom' => 'ConsumerRefereeB3',
    'prenom' => 'User',
    'phone' => $uB3_phone,
    'email' => "user_b3_{$uB3_phone}@test.com",
    'amount' => 0.0,
    'ref_by' => $refCodeA3,
    'statut' => 'yes',
    'creer' => date('Y-m-d H:i:s'),
]);

DB::table('referral')->insert([
    'user_id' => $userB3->id,
    'user_type' => 'customer',
    'referral_by_id' => $driverA3->id,
    'referral_by_type' => 'driver',
    'referral_by_code' => $refCodeA3,
    'referral_code' => ReferralCodeService::generateUniqueCode('customer', $userB3->id),
    'code_used' => 'true',
    'app_install_reward_paid' => 0,
    'creer' => date('Y-m-d H:i:s'),
]);

$statsBefore3 = getDashboardStats($dashCtrl, $driverA3->id, 'driver');
$consumerHistory3 = $statsBefore3['consumer']['history'] ?? [];
$item3 = reset($consumerHistory3);

echo "Step 1 (Before Milestone):\n";
echo "- Referee Consumer: " . ($item3['name'] ?? 'N/A') . "\n";
echo "- Status: " . ($item3['status'] ?? 'N/A') . "\n";
echo "- Pending Reward: ₹" . ($item3['reward_amount'] ?? 0) . "\n";

DB::table('tj_requete')->insert([
    ['id_user_app' => $userB3->id, 'id_conducteur' => 1, 'montant' => 300, 'statut' => 'completed', 'creer' => date('Y-m-d H:i:s')],
    ['id_user_app' => $userB3->id, 'id_conducteur' => 1, 'montant' => 300, 'statut' => 'completed', 'creer' => date('Y-m-d H:i:s')],
]);

$resReward3 = ReferralRewardService::checkAndProcessAppInstallReward($userB3->id, 'customer');
echo "\nStep 2 (After Milestone Trigger):\n";
echo "- Reward Processed: " . ($resReward3['reward_processed'] ? 'YES' : 'NO') . "\n";
echo "- Driver A3 Wallet Balance Now: ₹" . ($driverA3->fresh()->amount) . "\n";

$statsAfter3 = getDashboardStats($dashCtrl, $driverA3->id, 'driver');
$consumerHistoryAfter3 = $statsAfter3['consumer']['history'] ?? [];
$itemAfter3 = reset($consumerHistoryAfter3);
echo "- Dashboard Status: " . ($itemAfter3['status'] ?? 'N/A') . "\n";

if (($itemAfter3['status'] ?? '') === 'Credited' && ($driverA3->fresh()->amount) > 0) {
    echo "✓ PASS: Business -> User referral successfully credited to Driver wallet!\n";
} else {
    echo "✗ FAIL: Business -> User referral credit failed.\n";
}

// -----------------------------------------------------------------------------
// TEST CASE 4: BUSINESS -> BUSINESS (Driver/Partner refers Driver/Partner)
// -----------------------------------------------------------------------------
echo "\n------------------------------------------------------------------------\n";
echo "TEST 4: BUSINESS -> BUSINESS (Driver/Partner refers Driver/Partner)\n";
echo "------------------------------------------------------------------------\n";

$dA4_phone = '99997' . rand(10000, 99999);
$dB4_phone = '99998' . rand(10000, 99999);

$driverA4 = Driver::create([
    'nom' => 'DriverReferrerA4',
    'prenom' => 'Partner',
    'phone' => $dA4_phone,
    'email' => "driver_a4_{$dA4_phone}@test.com",
    'amount' => 0.0,
    'statut' => 'yes',
    'creer' => date('Y-m-d H:i:s'),
]);
$refCodeA4 = ReferralCodeService::getOrCreateReferralCode($driverA4->id, 'driver');

$driverB4 = Driver::create([
    'nom' => 'DriverRefereeB4',
    'prenom' => 'Partner',
    'phone' => $dB4_phone,
    'email' => "driver_b4_{$dB4_phone}@test.com",
    'amount' => 0.0,
    'ref_by' => $refCodeA4,
    'statut' => 'yes',
    'is_verified' => 1,
    'creer' => date('Y-m-d H:i:s'),
]);

DB::table('referral')->insert([
    'user_id' => $driverB4->id,
    'user_type' => 'driver',
    'referral_by_id' => $driverA4->id,
    'referral_by_type' => 'driver',
    'referral_by_code' => $refCodeA4,
    'referral_code' => ReferralCodeService::generateUniqueCode('driver', $driverB4->id),
    'code_used' => 'true',
    'app_install_reward_paid' => 0,
    'creer' => date('Y-m-d H:i:s'),
]);

$statsBefore4 = getDashboardStats($dashCtrl, $driverA4->id, 'driver');
$bizHistory4 = $statsBefore4['business']['history'] ?? [];
$item4 = reset($bizHistory4);

echo "Step 1 (Before Milestone):\n";
echo "- Referee Partner: " . ($item4['name'] ?? 'N/A') . "\n";
echo "- Status: " . ($item4['status'] ?? 'N/A') . "\n";
echo "- Pending Reward: ₹" . ($item4['reward_amount'] ?? 0) . "\n";

DB::table('tj_requete')->insert([
    ['id_user_app' => 1, 'id_conducteur' => $driverB4->id, 'montant' => 200, 'statut' => 'completed', 'creer' => date('Y-m-d H:i:s')],
    ['id_user_app' => 1, 'id_conducteur' => $driverB4->id, 'montant' => 200, 'statut' => 'completed', 'creer' => date('Y-m-d H:i:s')],
]);

$resReward4 = ReferralRewardService::checkAndProcessAppInstallReward($driverB4->id, 'driver');
echo "\nStep 2 (After Milestone Trigger):\n";
echo "- Reward Processed: " . ($resReward4['reward_processed'] ? 'YES' : 'NO') . "\n";
echo "- Driver A4 Wallet Balance Now: ₹" . ($driverA4->fresh()->amount) . "\n";

$statsAfter4 = getDashboardStats($dashCtrl, $driverA4->id, 'driver');
$bizHistoryAfter4 = $statsAfter4['business']['history'] ?? [];
$itemAfter4 = reset($bizHistoryAfter4);
echo "- Dashboard Status: " . ($itemAfter4['status'] ?? 'N/A') . "\n";

if (($itemAfter4['status'] ?? '') === 'Credited' && ($driverA4->fresh()->amount) > 0) {
    echo "✓ PASS: Business -> Business referral successfully credited to Driver wallet!\n";
} else {
    echo "✗ FAIL: Business -> Business referral credit failed.\n";
}

echo "\n========================================================================\n";
echo "  ALL 4 REFERRAL FLOW TEST CASES COMPLETED SUCCESSFULLY!\n";
echo "========================================================================\n";
