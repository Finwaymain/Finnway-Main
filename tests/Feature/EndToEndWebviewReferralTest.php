<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\ReferralCodeService;
use App\Http\Controllers\API\v1\AuthOtpController;
use App\Http\Controllers\API\v1\ReferralDashboardAPIController;
use App\Http\Controllers\API\v1\GetUserReferralCode;
use Carbon\Carbon;

class EndToEndWebviewReferralTest extends TestCase
{
    public function test_driver_and_customer_creation_and_webview_connection()
    {
        $now = Carbon::now()->toDateTimeString();
        $authController = new AuthOtpController();
        $dashboardController = new ReferralDashboardAPIController();
        $getReferralController = new GetUserReferralCode();

        // ══════════════════════════════════════════════════════════════════════
        // 1. CREATE FRESH CUSTOMER
        // ══════════════════════════════════════════════════════════════════════
        $customerPhone = '+91888811' . rand(1000, 9999);
        $customerId = DB::table('tj_user_app')->insertGetId([
            'nom'           => 'Sharma',
            'prenom'        => 'Rahul',
            'phone'         => $customerPhone,
            'email'         => 'rahul.' . rand(100, 999) . '@example.com',
            'amount'        => 0.00,
            'statut'        => 'yes',
            'statut_nic'    => 'yes',
            'aadhar_number' => '123456789012',
            'creer'         => $now,
            'modifier'      => $now,
        ]);

        $customerRefCode = ReferralCodeService::getOrCreateReferralCode($customerId, 'customer');

        // Assert Customer Code has 'FIINC' prefix
        $this->assertNotEmpty($customerRefCode);
        $this->assertStringStartsWith('FIINC', $customerRefCode);
        $this->assertEquals(11, strlen($customerRefCode)); // 'FIINC' (5) + 6 chars = 11

        // ══════════════════════════════════════════════════════════════════════
        // 2. CREATE FRESH DRIVER
        // ══════════════════════════════════════════════════════════════════════
        $driverPhone = '+91888822' . rand(1000, 9999);
        $driverId = DB::table('tj_conducteur')->insertGetId([
            'nom'           => 'Verma',
            'prenom'        => 'Vikram',
            'phone'         => $driverPhone,
            'email'         => 'vikram.' . rand(100, 999) . '@example.com',
            'amount'        => 0.00,
            'statut'        => 'yes',
            'online'        => 'yes',
            'is_verified'   => 1,
            'aadhar_number' => '987654321098',
            'creer'         => $now,
            'modifier'      => $now,
        ]);

        $driverRefCode = ReferralCodeService::getOrCreateReferralCode($driverId, 'driver');

        // Assert Driver Code has 'FIINB' prefix
        $this->assertNotEmpty($driverRefCode);
        $this->assertStringStartsWith('FIINB', $driverRefCode);
        $this->assertEquals(11, strlen($driverRefCode)); // 'FIINB' (5) + 6 chars = 11

        // ══════════════════════════════════════════════════════════════════════
        // 3. CHECK GET-REFERRAL API FOR BOTH
        // ══════════════════════════════════════════════════════════════════════
        // Customer get-referral API
        $respCustomerCode = $getReferralController->getData(new \Illuminate\Http\Request([
            'id_user'  => $customerId,
            'user_cat' => 'customer',
        ]));
        $custData = json_decode($respCustomerCode->getContent(), true);
        $this->assertEquals('success', $custData['success']);
        $this->assertEquals($customerRefCode, $custData['data']['referral_code']);

        // Driver get-referral API (via id_driver)
        $respDriverCode = $getReferralController->getData(new \Illuminate\Http\Request([
            'id_driver' => $driverId,
            'user_cat'  => 'driver',
        ]));
        $drvData = json_decode($respDriverCode->getContent(), true);
        $this->assertEquals('success', $drvData['success']);
        $this->assertEquals($driverRefCode, $drvData['data']['referral_code']);

        // Driver get-referral API (via legacy id_user parameter)
        $respDriverLegacy = $getReferralController->getData(new \Illuminate\Http\Request([
            'id_user'  => $driverId,
            'user_cat' => 'driver',
        ]));
        $drvLegacyData = json_decode($respDriverLegacy->getContent(), true);
        $this->assertEquals('success', $drvLegacyData['success']);
        $this->assertEquals($driverRefCode, $drvLegacyData['data']['referral_code']);

        // ══════════════════════════════════════════════════════════════════════
        // 4. CHECK WEBVIEW DASHBOARD API DATA & SHARE LINK FOR BOTH
        // ══════════════════════════════════════════════════════════════════════
        // A. Customer Webview Dashboard Stats
        $respCustomerDashboard = $dashboardController->getStats(new \Illuminate\Http\Request([
            'user_id'  => $customerId,
            'user_cat' => 'customer',
        ]));
        $custDash = json_decode($respCustomerDashboard->getContent(), true);
        $this->assertEquals('success', $custDash['success']);
        $this->assertEquals($customerRefCode, $custDash['data']['referral_code']);
        $this->assertEquals('https://api.fiinway.com/ref/' . $customerRefCode, $custDash['data']['share_url']);
        $this->assertEquals(0, $custDash['data']['summary']['total_partners']);

        // B. Driver Webview Dashboard Stats
        $respDriverDashboard = $dashboardController->getStats(new \Illuminate\Http\Request([
            'driver_id' => $driverId,
            'user_cat'  => 'driver',
        ]));
        $drvDash = json_decode($respDriverDashboard->getContent(), true);
        $this->assertEquals('success', $drvDash['success']);
        $this->assertEquals($driverRefCode, $drvDash['data']['referral_code']);
        $this->assertEquals('https://api.fiinway.com/ref/' . $driverRefCode, $drvDash['data']['share_url']);
        $this->assertEquals(0, $drvDash['data']['summary']['total_partners']);

        // ══════════════════════════════════════════════════════════════════════
        // 5. TEST REFERRAL CONNECTION (Customer refers a Driver & a Consumer)
        // ══════════════════════════════════════════════════════════════════════
        // 5a. Customer Rahul refers new Consumer Amit
        $refUser1Id = DB::table('tj_user_app')->insertGetId([
            'nom' => 'Kumar', 'prenom' => 'Amit', 'phone' => '+91888833' . rand(1000, 9999),
            'email' => 'amit.' . rand(100, 999) . '@example.com', 'amount' => 0.00, 'statut' => 'yes',
            'creer' => $now, 'modifier' => $now,
        ]);
        $authController->handleReferral($refUser1Id, $customerRefCode, $now, 'customer');

        // 5b. Customer Rahul refers new Driver Rajesh
        $refDriver1Id = DB::table('tj_conducteur')->insertGetId([
            'nom' => 'Singh', 'prenom' => 'Rajesh', 'phone' => '+91888844' . rand(1000, 9999),
            'email' => 'rajesh.' . rand(100, 999) . '@example.com', 'amount' => 0.00, 'statut' => 'yes',
            'creer' => $now, 'modifier' => $now,
        ]);
        $authController->handleReferral($refDriver1Id, $customerRefCode, $now, 'driver');

        // ══════════════════════════════════════════════════════════════════════
        // 6. TEST REFERRAL CONNECTION (Driver refers a Driver & a Consumer)
        // ══════════════════════════════════════════════════════════════════════
        // 6a. Driver Vikram refers new Consumer Sneha
        $refUser2Id = DB::table('tj_user_app')->insertGetId([
            'nom' => 'Patel', 'prenom' => 'Sneha', 'phone' => '+91888855' . rand(1000, 9999),
            'email' => 'sneha.' . rand(100, 999) . '@example.com', 'amount' => 0.00, 'statut' => 'yes',
            'creer' => $now, 'modifier' => $now,
        ]);
        $authController->handleReferral($refUser2Id, $driverRefCode, $now, 'customer');

        // 6b. Driver Vikram refers new Driver Anil
        $refDriver2Id = DB::table('tj_conducteur')->insertGetId([
            'nom' => 'Yadav', 'prenom' => 'Anil', 'phone' => '+91888866' . rand(1000, 9999),
            'email' => 'anil.' . rand(100, 999) . '@example.com', 'amount' => 0.00, 'statut' => 'yes',
            'creer' => $now, 'modifier' => $now,
        ]);
        $authController->handleReferral($refDriver2Id, $driverRefCode, $now, 'driver');

        // Complete qualifying services for the 4 referees
        DB::table('tj_requete')->insert(['id_user_app' => $refUser1Id, 'id_conducteur' => 1, 'montant' => '500', 'statut' => 'completed', 'statut_paiement' => 'yes', 'creer' => $now]);
        \App\Services\ReferralRewardService::checkAndProcessAppInstallReward($refUser1Id, 'customer');

        DB::table('tj_requete')->insert(['id_user_app' => 1, 'id_conducteur' => $refDriver1Id, 'montant' => '500', 'statut' => 'completed', 'statut_paiement' => 'yes', 'creer' => $now]);
        \App\Services\ReferralRewardService::checkAndProcessAppInstallReward($refDriver1Id, 'driver');

        DB::table('tj_requete')->insert(['id_user_app' => $refUser2Id, 'id_conducteur' => 1, 'montant' => '500', 'statut' => 'completed', 'statut_paiement' => 'yes', 'creer' => $now]);
        \App\Services\ReferralRewardService::checkAndProcessAppInstallReward($refUser2Id, 'customer');

        DB::table('tj_requete')->insert(['id_user_app' => 1, 'id_conducteur' => $refDriver2Id, 'montant' => '500', 'statut' => 'completed', 'statut_paiement' => 'yes', 'creer' => $now]);
        \App\Services\ReferralRewardService::checkAndProcessAppInstallReward($refDriver2Id, 'driver');

        // ══════════════════════════════════════════════════════════════════════
        // 7. VERIFY CUSTOMER WEBVIEW DASHBOARD AFTER CONNECTIONS
        // ══════════════════════════════════════════════════════════════════════
        $respCustAfter = $dashboardController->getStats(new \Illuminate\Http\Request([
            'user_id'  => $customerId,
            'user_cat' => 'customer',
        ]));
        $custAfterData = json_decode($respCustAfter->getContent(), true)['data'];

        $this->assertEquals(2, $custAfterData['summary']['total_partners']);
        $this->assertEquals(1, $custAfterData['summary']['consumer_count']);
        $this->assertEquals(1, $custAfterData['summary']['business_count']);
        $this->assertGreaterThan(0, $custAfterData['summary']['total_income']);

        // Check wallet transaction logs for Customer Rahul in tj_transaction
        $custTxnCount = DB::table('tj_transaction')
            ->where('id_user_app', $customerId)
            ->where('payment_method', 'Referral Reward')
            ->count();
        $this->assertEquals(2, $custTxnCount);

        // ══════════════════════════════════════════════════════════════════════
        // 8. VERIFY DRIVER WEBVIEW DASHBOARD AFTER CONNECTIONS
        // ══════════════════════════════════════════════════════════════════════
        $respDrvAfter = $dashboardController->getStats(new \Illuminate\Http\Request([
            'driver_id' => $driverId,
            'user_cat'  => 'driver',
        ]));
        $drvAfterData = json_decode($respDrvAfter->getContent(), true)['data'];

        $this->assertEquals(2, $drvAfterData['summary']['total_partners']);
        $this->assertEquals(1, $drvAfterData['summary']['consumer_count']);
        $this->assertEquals(1, $drvAfterData['summary']['business_count']);
        $this->assertGreaterThan(0, $drvAfterData['summary']['total_income']);

        // Check wallet transaction logs for Driver Vikram in tj_conducteur_transaction
        $drvTxnCount = DB::table('tj_conducteur_transaction')
            ->where('id_conducteur', $driverId)
            ->where('payment_method', 'Referral Reward')
            ->count();
        $this->assertEquals(2, $drvTxnCount);

        // ══════════════════════════════════════════════════════════════════════
        // 9. VERIFY HTTP WEBVIEW ROUTES RETURN 200 OK
        // ══════════════════════════════════════════════════════════════════════
        $this->get('/onboarding/referral?user_id=' . $customerId . '&user_cat=customer')
            ->assertStatus(200);

        $this->get('/onboarding/referral?driver_id=' . $driverId . '&user_cat=driver')
            ->assertStatus(200);

        $this->get('/referral?user_id=' . $customerId)
            ->assertStatus(200);
    }
}
