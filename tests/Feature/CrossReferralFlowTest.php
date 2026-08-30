<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\ReferralCodeService;
use App\Http\Controllers\API\v1\AuthOtpController;
use Carbon\Carbon;

class CrossReferralFlowTest extends TestCase
{
    /**
     * Test all 4 Cross-Referral Pathways:
     * 1. Consumer -> Consumer
     * 2. Consumer -> Business (Driver)
     * 3. Business (Driver) -> Consumer
     * 4. Business (Driver) -> Business (Driver)
     */
    public function test_all_cross_referral_pathways()
    {
        $now = Carbon::now()->toDateTimeString();
        $authController = new AuthOtpController();

        // ── 1. Create Consumer A (Referrer) ──────────────────────────────────
        $consumerAId = DB::table('tj_user_app')->insertGetId([
            'nom' => 'Referrer', 'prenom' => 'UserA', 'phone' => '+919999000001', 'email' => 'usera@test.com',
            'amount' => 0.00, 'statut' => 'yes', 'creer' => $now, 'modifier' => $now
        ]);
        $consumerACode = ReferralCodeService::getOrCreateReferralCode($consumerAId, 'customer');
        $this->assertStringStartsWith('FIINC', $consumerACode);

        // ── 2. Create Business Driver D (Referrer) ───────────────────────────
        $driverDId = DB::table('tj_conducteur')->insertGetId([
            'nom' => 'Referrer', 'prenom' => 'DriverD', 'phone' => '+919999000004', 'email' => 'driverd@test.com',
            'amount' => 0.00, 'statut' => 'yes', 'online' => 'yes', 'creer' => $now, 'modifier' => $now
        ]);
        $driverDCode = ReferralCodeService::getOrCreateReferralCode($driverDId, 'driver');
        $this->assertStringStartsWith('FIINB', $driverDCode);

        // ── PATHWAY 1: Consumer A refers Consumer B ──────────────────────────
        $consumerBId = DB::table('tj_user_app')->insertGetId([
            'nom' => 'Referred', 'prenom' => 'UserB', 'phone' => '+919999000002', 'email' => 'userb@test.com',
            'amount' => 0.00, 'statut' => 'yes', 'creer' => $now, 'modifier' => $now
        ]);
        $authController->handleReferral($consumerBId, $consumerACode, $now, 'customer');

        // Consumer B completes a qualifying service (₹500 meets min_amount threshold)
        DB::table('tj_requete')->insert([
            'id_user_app' => $consumerBId, 'id_conducteur' => 1, 'montant' => '500', 'statut' => 'completed', 'statut_paiement' => 'yes', 'creer' => $now
        ]);
        \App\Services\ReferralRewardService::checkAndProcessAppInstallReward($consumerBId, 'customer');

        // Verify Consumer A wallet credited
        $consumerABalance = (float)DB::table('tj_user_app')->where('id', $consumerAId)->value('amount');
        $this->assertGreaterThan(0, $consumerABalance);

        // Verify tj_transaction has record for Consumer A -> Consumer B
        $txnB = DB::table('tj_transaction')
            ->where('id_user_app', $consumerAId)
            ->where('sender_user_id', $consumerBId)
            ->where('sender_user_type', 'customer')
            ->first();
        $this->assertNotNull($txnB);
        $this->assertEquals('Referral Reward', $txnB->payment_method);

        // ── PATHWAY 2: Consumer A refers Business Driver C ───────────────────
        $driverCId = DB::table('tj_conducteur')->insertGetId([
            'nom' => 'Referred', 'prenom' => 'DriverC', 'phone' => '+919999000003', 'email' => 'driverc@test.com',
            'amount' => 0.00, 'statut' => 'yes', 'online' => 'yes', 'creer' => $now, 'modifier' => $now
        ]);
        $authController->handleReferral($driverCId, $consumerACode, $now, 'driver');

        // Driver C completes qualifying ride (₹500 meets threshold)
        DB::table('tj_requete')->insert([
            'id_user_app' => 1, 'id_conducteur' => $driverCId, 'montant' => '500', 'statut' => 'completed', 'statut_paiement' => 'yes', 'creer' => $now
        ]);
        \App\Services\ReferralRewardService::checkAndProcessAppInstallReward($driverCId, 'driver');

        // Verify Consumer A wallet received SECOND reward for Driver C
        $consumerABalanceAfterC = (float)DB::table('tj_user_app')->where('id', $consumerAId)->value('amount');
        $this->assertGreaterThan($consumerABalance, $consumerABalanceAfterC);

        // Verify tj_transaction has record for Consumer A -> Driver C
        $txnC = DB::table('tj_transaction')
            ->where('id_user_app', $consumerAId)
            ->where('sender_user_id', $driverCId)
            ->where('sender_user_type', 'driver')
            ->first();
        $this->assertNotNull($txnC);
        $this->assertEquals('Referral Reward', $txnC->payment_method);

        // ── PATHWAY 3: Business Driver D refers Consumer E ───────────────────
        $consumerEId = DB::table('tj_user_app')->insertGetId([
            'nom' => 'Referred', 'prenom' => 'UserE', 'phone' => '+919999000005', 'email' => 'usere@test.com',
            'amount' => 0.00, 'statut' => 'yes', 'creer' => $now, 'modifier' => $now
        ]);
        $authController->handleReferral($consumerEId, $driverDCode, $now, 'customer');

        // Consumer E completes qualifying service (₹500 meets threshold)
        DB::table('tj_requete')->insert([
            'id_user_app' => $consumerEId, 'id_conducteur' => 1, 'montant' => '500', 'statut' => 'completed', 'statut_paiement' => 'yes', 'creer' => $now
        ]);
        \App\Services\ReferralRewardService::checkAndProcessAppInstallReward($consumerEId, 'customer');

        // Verify Driver D wallet credited
        $driverDBalance = (float)DB::table('tj_conducteur')->where('id', $driverDId)->value('amount');
        $this->assertGreaterThan(0, $driverDBalance);

        // Verify tj_conducteur_transaction has record for Driver D -> Consumer E
        $txnE = DB::table('tj_conducteur_transaction')
            ->where('id_conducteur', $driverDId)
            ->where('receiver_user_id', $consumerEId)
            ->where('sender_user_type', 'customer')
            ->first();
        $this->assertNotNull($txnE);
        $this->assertEquals('Referral Reward', $txnE->payment_method);

        // ── PATHWAY 4: Business Driver D refers Business Driver F ────────────
        $driverFId = DB::table('tj_conducteur')->insertGetId([
            'nom' => 'Referred', 'prenom' => 'DriverF', 'phone' => '+919999000006', 'email' => 'driverf@test.com',
            'amount' => 0.00, 'statut' => 'yes', 'online' => 'yes', 'creer' => $now, 'modifier' => $now
        ]);
        $authController->handleReferral($driverFId, $driverDCode, $now, 'driver');

        // Driver F completes qualifying ride (₹500 meets threshold)
        DB::table('tj_requete')->insert([
            'id_user_app' => 1, 'id_conducteur' => $driverFId, 'montant' => '500', 'statut' => 'completed', 'statut_paiement' => 'yes', 'creer' => $now
        ]);
        \App\Services\ReferralRewardService::checkAndProcessAppInstallReward($driverFId, 'driver');

        // Verify Driver D wallet received SECOND reward for Driver F
        $driverDBalanceAfterF = (float)DB::table('tj_conducteur')->where('id', $driverDId)->value('amount');
        $this->assertGreaterThan($driverDBalance, $driverDBalanceAfterF);

        // Verify tj_conducteur_transaction has record for Driver D -> Driver F
        $txnF = DB::table('tj_conducteur_transaction')
            ->where('id_conducteur', $driverDId)
            ->where('receiver_user_id', $driverFId)
            ->where('sender_user_type', 'driver')
            ->first();
        $this->assertNotNull($txnF);
        $this->assertEquals('Referral Reward', $txnF->payment_method);

        // ── 5. VERIFY REFERRAL DASHBOARD API STATS ISOLATION ────────────────
        $dashboardController = new \App\Http\Controllers\API\v1\ReferralDashboardAPIController();

        // A. Consumer A Dashboard Stats (should contain User B & Driver C ONLY)
        $reqA = new \Illuminate\Http\Request(['user_id' => $consumerAId, 'user_cat' => 'customer']);
        $respA = $dashboardController->getStats($reqA);
        $dataA = json_decode($respA->getContent(), true)['data'];

        $this->assertEquals(2, $dataA['summary']['total_partners']);
        $this->assertEquals(1, $dataA['summary']['consumer_count']);
        $this->assertEquals(1, $dataA['summary']['business_count']);
        $this->assertGreaterThan(0, $dataA['summary']['total_income']);

        // B. Driver D Dashboard Stats (should contain User E & Driver F ONLY)
        $reqD = new \Illuminate\Http\Request(['driver_id' => $driverDId, 'user_cat' => 'driver']);
        $respD = $dashboardController->getStats($reqD);
        $dataD = json_decode($respD->getContent(), true)['data'];

        $this->assertEquals(2, $dataD['summary']['total_partners']);
        $this->assertEquals(1, $dataD['summary']['consumer_count']);
        $this->assertEquals(1, $dataD['summary']['business_count']);
        $this->assertGreaterThan(0, $dataD['summary']['total_income']);

        // Ensure Consumer A does NOT have Driver D's referee (User E or Driver F)
        $consumerRefIds = array_merge(
            array_column($dataA['consumer']['history'], 'id'),
            array_column($dataA['business']['history'], 'id')
        );
        $this->assertContains($consumerBId, $consumerRefIds);
        $this->assertContains($driverCId, $consumerRefIds);
        $this->assertNotContains($consumerEId, $consumerRefIds);
        $this->assertNotContains($driverFId, $consumerRefIds);

        // Ensure Driver D does NOT have Consumer A's referee (User B or Driver C)
        $driverRefIds = array_merge(
            array_column($dataD['consumer']['history'], 'id'),
            array_column($dataD['business']['history'], 'id')
        );
        $this->assertContains($consumerEId, $driverRefIds);
        $this->assertContains($driverFId, $driverRefIds);
        $this->assertNotContains($consumerBId, $driverRefIds);
        $this->assertNotContains($driverCId, $driverRefIds);

        // ── 6. VERIFY GET-REFERRAL API & PROFILE REFERRAL CODES ─────────────
        $getCodeController = new \App\Http\Controllers\API\v1\GetUserReferralCode();

        // Customer Get Referral Code
        $reqUserCode = new \Illuminate\Http\Request(['id_user' => $consumerAId, 'user_cat' => 'customer']);
        $respUserCode = $getCodeController->getData($reqUserCode);
        $dataUserCode = json_decode($respUserCode->getContent(), true);
        $this->assertEquals('success', $dataUserCode['success']);
        $this->assertStringStartsWith('FIINC', $dataUserCode['data']['referral_code']);

        // Driver Get Referral Code
        $reqDriverCode = new \Illuminate\Http\Request(['id_driver' => $driverDId, 'user_cat' => 'driver']);
        $respDriverCode = $getCodeController->getData($reqDriverCode);
        $dataDriverCode = json_decode($respDriverCode->getContent(), true);
        $this->assertEquals('success', $dataDriverCode['success']);
        $this->assertStringStartsWith('FIINB', $dataDriverCode['data']['referral_code']);

        // Driver Get Referral Code when called with legacy ?id_user parameter
        $reqDriverLegacy = new \Illuminate\Http\Request(['id_user' => $driverDId, 'user_cat' => 'driver']);
        $respDriverLegacy = $getCodeController->getData($reqDriverLegacy);
        $dataDriverLegacy = json_decode($respDriverLegacy->getContent(), true);
        $this->assertEquals('success', $dataDriverLegacy['success']);
        $this->assertStringStartsWith('FIINB', $dataDriverLegacy['data']['referral_code']);
    }
}
