<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\UserApp;
use App\Models\Driver;
use App\Models\ApiKeySetting;
use App\Services\ReferralCodeService;
use App\Services\ReferralRewardService;
use App\Http\Controllers\API\v1\ReferralDashboardAPIController;
use Illuminate\Http\Request;

class ReferralDashboardFrozenCashbackTest extends TestCase
{
    public function test_referral_dashboard_displays_frozen_vs_unlocked_cashback()
    {
        // 1. Configure Rules: Consumer = ₹10 (5 services or ₹500), Business = ₹50 (2 services or ₹200)
        ApiKeySetting::updateOrCreate(['key_name' => 'event_rule_app_install_user_enable'], ['group' => 'referral_event', 'provider' => 'rule', 'key_value' => '1', 'is_active' => true]);
        ApiKeySetting::updateOrCreate(['key_name' => 'event_rule_app_install_user_type'], ['group' => 'referral_event', 'provider' => 'rule', 'key_value' => 'flat', 'is_active' => true]);
        ApiKeySetting::updateOrCreate(['key_name' => 'event_rule_app_install_user_value'], ['group' => 'referral_event', 'provider' => 'rule', 'key_value' => '10', 'is_active' => true]);
        ApiKeySetting::updateOrCreate(['key_name' => 'event_rule_app_install_user_min_services'], ['group' => 'referral_event', 'provider' => 'rule', 'key_value' => '5', 'is_active' => true]);
        ApiKeySetting::updateOrCreate(['key_name' => 'event_rule_app_install_user_min_amount'], ['group' => 'referral_event', 'provider' => 'rule', 'key_value' => '500', 'is_active' => true]);

        ApiKeySetting::updateOrCreate(['key_name' => 'event_rule_app_install_business_enable'], ['group' => 'referral_event', 'provider' => 'rule', 'key_value' => '1', 'is_active' => true]);
        ApiKeySetting::updateOrCreate(['key_name' => 'event_rule_app_install_business_type'], ['group' => 'referral_event', 'provider' => 'rule', 'key_value' => 'flat', 'is_active' => true]);
        ApiKeySetting::updateOrCreate(['key_name' => 'event_rule_app_install_business_value'], ['group' => 'referral_event', 'provider' => 'rule', 'key_value' => '50', 'is_active' => true]);
        ApiKeySetting::updateOrCreate(['key_name' => 'event_rule_app_install_business_min_services'], ['group' => 'referral_event', 'provider' => 'rule', 'key_value' => '2', 'is_active' => true]);
        ApiKeySetting::updateOrCreate(['key_name' => 'event_rule_app_install_business_min_amount'], ['group' => 'referral_event', 'provider' => 'rule', 'key_value' => '200', 'is_active' => true]);

        // 2. Create Referrer User A
        $userAId = DB::table('tj_user_app')->insertGetId([
            'prenom' => 'Referrer',
            'nom' => 'UserAFrozen',
            'phone' => '99881' . rand(10000, 99999),
            'email' => 'usera_frz_' . uniqid() . '@test.com',
            'statut' => 'yes',
            'amount' => '0',
            'creer' => date('Y-m-d H:i:s'),
        ]);
        $codeA = ReferralCodeService::getOrCreateReferralCode($userAId, 'customer');

        // 3. User B (Consumer) registers using Code A
        $userBId = DB::table('tj_user_app')->insertGetId([
            'prenom' => 'Referee',
            'nom' => 'ConsumerB',
            'phone' => '99882' . rand(10000, 99999),
            'email' => 'userb_frz_' . uniqid() . '@test.com',
            'statut' => 'yes',
            'amount' => '0',
            'creer' => date('Y-m-d H:i:s'),
        ]);
        DB::table('referral')->updateOrInsert(
            ['user_id' => $userBId],
            [
                'user_type' => 'customer',
                'referral_by_id' => $userAId,
                'referral_by_type' => 'customer',
                'referral_by_code' => $codeA,
                'code_used' => 'true',
                'app_install_reward_paid' => 0,
                'creer' => date('Y-m-d H:i:s'),
            ]
        );

        // 4. User C (Driver) registers using Code A
        $driverCId = DB::table('tj_conducteur')->insertGetId([
            'prenom' => 'Partner',
            'nom' => 'DriverC',
            'phone' => '99883' . rand(10000, 99999),
            'email' => 'driverc_frz_' . uniqid() . '@test.com',
            'statut' => 'yes',
            'amount' => '0',
            'creer' => date('Y-m-d H:i:s'),
        ]);
        DB::table('referral')->updateOrInsert(
            ['user_id' => $driverCId],
            [
                'user_type' => 'driver',
                'referral_by_id' => $userAId,
                'referral_by_type' => 'customer',
                'referral_by_code' => $codeA,
                'code_used' => 'true',
                'app_install_reward_paid' => 0,
                'creer' => date('Y-m-d H:i:s'),
            ]
        );

        // 5. Query Dashboard stats BEFORE any conditions fulfilled
        $controller = new ReferralDashboardAPIController();
        $response1 = $controller->getStats(new Request(['user_id' => $userAId, 'user_cat' => 'customer']));
        $data1 = json_decode($response1->getContent(), true)['data'];

        $this->assertEquals(0, $data1['wallet_balance']);
        $this->assertEquals(0, $data1['referral_earnings']);
        $this->assertEquals(60, $data1['frozen_income']); // ₹10 consumer + ₹50 business = ₹60 frozen
        $this->assertEquals(60, $data1['pending_cashback']);

        // Check Consumer B details
        $consumerHistory = $data1['consumer']['history'];
        $this->assertCount(1, $consumerHistory);
        $this->assertEquals('Frozen', $consumerHistory[0]['status']);
        $this->assertTrue($consumerHistory[0]['is_frozen']);
        $this->assertEquals(10.0, (float)$consumerHistory[0]['frozen_cashback']);
        $this->assertEquals(0.0, (float)$consumerHistory[0]['unlocked_cashback']);
        $this->assertStringContainsString('Frozen', $consumerHistory[0]['condition_note']);

        // Check Driver C details
        $businessHistory = $data1['business']['history'];
        $this->assertCount(1, $businessHistory);
        $this->assertEquals('Frozen', $businessHistory[0]['status']);
        $this->assertTrue($businessHistory[0]['is_frozen']);
        $this->assertEquals(50.0, (float)$businessHistory[0]['frozen_cashback']);
        $this->assertEquals(0.0, (float)$businessHistory[0]['unlocked_cashback']);

        // 6. User B fulfills condition by completing a qualifying ₹500 ride
        DB::table('tj_requete')->insert([
            'id_user_app' => $userBId,
            'id_conducteur' => 1,
            'montant' => '500',
            'statut' => 'completed',
            'statut_paiement' => 'yes',
            'creer' => date('Y-m-d H:i:s'),
        ]);
        $rewardResult = ReferralRewardService::checkAndProcessAppInstallReward($userBId, 'customer');
        $this->assertTrue($rewardResult['reward_processed']);

        // 7. Query Dashboard stats AFTER User B qualified
        $response2 = $controller->getStats(new Request(['user_id' => $userAId, 'user_cat' => 'customer']));
        $data2 = json_decode($response2->getContent(), true)['data'];

        $this->assertEquals(10, $data2['wallet_balance']);
        $this->assertEquals(10, $data2['referral_earnings']);
        $this->assertEquals(50, $data2['frozen_income']); // User C still frozen (₹50)
        $this->assertEquals(50, $data2['pending_cashback']);

        // Check Consumer B is now UNLOCKED
        $consumerHistoryAfter = $data2['consumer']['history'];
        $this->assertEquals('Unlocked', $consumerHistoryAfter[0]['status']);
        $this->assertFalse($consumerHistoryAfter[0]['is_frozen']);
        $this->assertEquals(0.0, (float)$consumerHistoryAfter[0]['frozen_cashback']);
        $this->assertEquals(10.0, (float)$consumerHistoryAfter[0]['unlocked_cashback']);
        $this->assertStringContainsString('Unlocked', $consumerHistoryAfter[0]['condition_note']);
    }
}
