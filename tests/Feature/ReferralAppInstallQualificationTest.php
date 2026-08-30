<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\UserApp;
use App\Models\Driver;
use App\Models\ApiKeySetting;
use App\Services\ReferralCodeService;
use App\Services\ReferralRewardService;

class ReferralAppInstallQualificationTest extends TestCase
{
    private function getAdminUser()
    {
        $user = User::first();
        if (!$user) {
            $user = User::create([
                'name' => 'Super Admin',
                'email' => 'admin@fiinway.test',
                'password' => bcrypt('password'),
            ]);
        }
        return $user;
    }

    public function test_admin_post_form_saves_and_persists_rules_via_http()
    {
        $admin = $this->getAdminUser();

        $postData = [
            'event_rules_submit' => '1',
            'event_app_install_user_enable' => '1',
            'event_app_install_user_type' => 'flat',
            'event_app_install_user_value' => '10',
            'event_app_install_user_min_services' => '5',
            'event_app_install_user_min_amount' => '500',

            'event_app_install_business_enable' => '1',
            'event_app_install_business_type' => 'flat',
            'event_app_install_business_value' => '50',
            'event_app_install_business_min_services' => '2',
            'event_app_install_business_min_amount' => '200',
        ];

        $response = $this->actingAs($admin)->post('/referral-engine/update', $postData);
        $response->assertRedirect();

        // Assert database values
        $this->assertEquals('10', ApiKeySetting::getApiKeyValue('event_rule_app_install_user_value'));
        $this->assertEquals('5', ApiKeySetting::getApiKeyValue('event_rule_app_install_user_min_services'));
        $this->assertEquals('500', ApiKeySetting::getApiKeyValue('event_rule_app_install_user_min_amount'));

        $this->assertEquals('50', ApiKeySetting::getApiKeyValue('event_rule_app_install_business_value'));
        $this->assertEquals('2', ApiKeySetting::getApiKeyValue('event_rule_app_install_business_min_services'));
        $this->assertEquals('200', ApiKeySetting::getApiKeyValue('event_rule_app_install_business_min_amount'));

        // Assert view renders newly saved values
        $viewResponse = $this->actingAs($admin)->get('/referral-engine');
        $viewResponse->assertStatus(200)
            ->assertSee('App Install User')
            ->assertSee('App Install Business')
            ->assertSee('value="10"', false)
            ->assertSee('value="500"', false)
            ->assertSee('value="50"', false)
            ->assertSee('value="200"', false);
    }

    public function test_user_qualifies_when_min_amount_met_any_one_condition()
    {
        // Require 5 services OR ₹500 spend. User completes 1 service of ₹600 -> Qualifies!
        ApiKeySetting::updateOrCreate(['key_name' => 'event_rule_app_install_user_enable'], ['group' => 'referral_event', 'provider' => 'rule', 'key_value' => '1', 'is_active' => true]);
        ApiKeySetting::updateOrCreate(['key_name' => 'event_rule_app_install_user_type'], ['group' => 'referral_event', 'provider' => 'rule', 'key_value' => 'flat', 'is_active' => true]);
        ApiKeySetting::updateOrCreate(['key_name' => 'event_rule_app_install_user_value'], ['group' => 'referral_event', 'provider' => 'rule', 'key_value' => '10', 'is_active' => true]);
        ApiKeySetting::updateOrCreate(['key_name' => 'event_rule_app_install_user_min_services'], ['group' => 'referral_event', 'provider' => 'rule', 'key_value' => '5', 'is_active' => true]);
        ApiKeySetting::updateOrCreate(['key_name' => 'event_rule_app_install_user_min_amount'], ['group' => 'referral_event', 'provider' => 'rule', 'key_value' => '500', 'is_active' => true]);

        $userAId = DB::table('tj_user_app')->insertGetId([
            'prenom' => 'Referrer',
            'nom' => 'UserA1',
            'phone' => '99991' . rand(10000, 99999),
            'email' => 'userA1_' . uniqid() . '@test.com',
            'statut' => 'yes',
            'amount' => '0',
            'creer' => date('Y-m-d H:i:s'),
        ]);
        $codeA = ReferralCodeService::getOrCreateReferralCode($userAId, 'customer');

        $userBId = DB::table('tj_user_app')->insertGetId([
            'prenom' => 'Referee',
            'nom' => 'UserB1',
            'phone' => '99992' . rand(10000, 99999),
            'email' => 'userB1_' . uniqid() . '@test.com',
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

        // User B completes single service of ₹600 (min_amount ₹500 met, even though services = 1 < 5)
        DB::table('tj_requete')->insert([
            'id_user_app' => $userBId,
            'id_conducteur' => 1,
            'montant' => '600',
            'statut' => 'completed',
            'statut_paiement' => 'yes',
            'creer' => date('Y-m-d H:i:s'),
        ]);

        $check = ReferralRewardService::checkAndProcessAppInstallReward($userBId, 'customer');
        $this->assertTrue($check['reward_processed']);
        $this->assertEquals(10.0, (float)$check['reward_amount']);

        $userABalance = (float)DB::table('tj_user_app')->where('id', $userAId)->value('amount');
        $this->assertEquals(10.0, $userABalance);
    }

    public function test_user_qualifies_when_min_services_met_any_one_condition()
    {
        // Require 5 services OR ₹500 spend. User completes 5 services of ₹20 (Total ₹100 < ₹500, but services = 5) -> Qualifies!
        ApiKeySetting::updateOrCreate(['key_name' => 'event_rule_app_install_user_enable'], ['group' => 'referral_event', 'provider' => 'rule', 'key_value' => '1', 'is_active' => true]);
        ApiKeySetting::updateOrCreate(['key_name' => 'event_rule_app_install_user_type'], ['group' => 'referral_event', 'provider' => 'rule', 'key_value' => 'flat', 'is_active' => true]);
        ApiKeySetting::updateOrCreate(['key_name' => 'event_rule_app_install_user_value'], ['group' => 'referral_event', 'provider' => 'rule', 'key_value' => '10', 'is_active' => true]);
        ApiKeySetting::updateOrCreate(['key_name' => 'event_rule_app_install_user_min_services'], ['group' => 'referral_event', 'provider' => 'rule', 'key_value' => '5', 'is_active' => true]);
        ApiKeySetting::updateOrCreate(['key_name' => 'event_rule_app_install_user_min_amount'], ['group' => 'referral_event', 'provider' => 'rule', 'key_value' => '500', 'is_active' => true]);

        $userAId = DB::table('tj_user_app')->insertGetId([
            'prenom' => 'Referrer',
            'nom' => 'UserA2',
            'phone' => '99993' . rand(10000, 99999),
            'email' => 'userA2_' . uniqid() . '@test.com',
            'statut' => 'yes',
            'amount' => '0',
            'creer' => date('Y-m-d H:i:s'),
        ]);
        $codeA = ReferralCodeService::getOrCreateReferralCode($userAId, 'customer');

        $userBId = DB::table('tj_user_app')->insertGetId([
            'prenom' => 'Referee',
            'nom' => 'UserB2',
            'phone' => '99994' . rand(10000, 99999),
            'email' => 'userB2_' . uniqid() . '@test.com',
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

        // 4 services of ₹20 -> not qualified
        for ($i = 1; $i <= 4; $i++) {
            DB::table('tj_requete')->insert([
                'id_user_app' => $userBId,
                'id_conducteur' => 1,
                'montant' => '20',
                'statut' => 'completed',
                'statut_paiement' => 'yes',
                'creer' => date('Y-m-d H:i:s'),
            ]);
            $check = ReferralRewardService::checkAndProcessAppInstallReward($userBId, 'customer');
            $this->assertFalse($check['reward_processed']);
        }

        // 5th service of ₹20 -> Total 5 services (Total ₹100 < ₹500, but services = 5) -> Qualifies!
        DB::table('tj_requete')->insert([
            'id_user_app' => $userBId,
            'id_conducteur' => 1,
            'montant' => '20',
            'statut' => 'completed',
            'statut_paiement' => 'yes',
            'creer' => date('Y-m-d H:i:s'),
        ]);

        $checkFinal = ReferralRewardService::checkAndProcessAppInstallReward($userBId, 'customer');
        $this->assertTrue($checkFinal['reward_processed']);
        $this->assertEquals(10.0, (float)$checkFinal['reward_amount']);

        $userABalance = (float)DB::table('tj_user_app')->where('id', $userAId)->value('amount');
        $this->assertEquals(10.0, $userABalance);
    }

    public function test_driver_business_referral_qualification_flow()
    {
        // 1. Configure Admin Rules: ₹50 Flat reward, requires at least 2 services OR ₹200 total spend
        ApiKeySetting::updateOrCreate(['key_name' => 'event_rule_app_install_business_enable'], ['group' => 'referral_event', 'provider' => 'rule', 'key_value' => '1', 'is_active' => true]);
        ApiKeySetting::updateOrCreate(['key_name' => 'event_rule_app_install_business_type'], ['group' => 'referral_event', 'provider' => 'rule', 'key_value' => 'flat', 'is_active' => true]);
        ApiKeySetting::updateOrCreate(['key_name' => 'event_rule_app_install_business_value'], ['group' => 'referral_event', 'provider' => 'rule', 'key_value' => '50', 'is_active' => true]);
        ApiKeySetting::updateOrCreate(['key_name' => 'event_rule_app_install_business_min_services'], ['group' => 'referral_event', 'provider' => 'rule', 'key_value' => '2', 'is_active' => true]);
        ApiKeySetting::updateOrCreate(['key_name' => 'event_rule_app_install_business_min_amount'], ['group' => 'referral_event', 'provider' => 'rule', 'key_value' => '200', 'is_active' => true]);

        // 2. Create Referrer (User A)
        $userAId = DB::table('tj_user_app')->insertGetId([
            'prenom' => 'Referrer',
            'nom' => 'UserADrv',
            'phone' => '99995' . rand(10000, 99999),
            'email' => 'userA_drv_' . uniqid() . '@test.com',
            'statut' => 'yes',
            'amount' => '0',
            'creer' => date('Y-m-d H:i:s'),
        ]);
        $codeA = ReferralCodeService::getOrCreateReferralCode($userAId, 'customer');

        // 3. Create Referee Driver (Driver B)
        $driverBId = DB::table('tj_conducteur')->insertGetId([
            'prenom' => 'Partner',
            'nom' => 'DriverB',
            'phone' => '99996' . rand(10000, 99999),
            'email' => 'driverB_' . uniqid() . '@test.com',
            'statut' => 'yes',
            'amount' => '0',
            'creer' => date('Y-m-d H:i:s'),
        ]);

        DB::table('referral')->updateOrInsert(
            ['user_id' => $driverBId],
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

        // 4. Initial check -> not qualified
        $check0 = ReferralRewardService::checkAndProcessAppInstallReward($driverBId, 'driver');
        $this->assertFalse($check0['reward_processed']);

        // 5. Driver B completes 1st ride of ₹50 -> not qualified
        DB::table('tj_requete')->insert([
            'id_user_app' => 1,
            'id_conducteur' => $driverBId,
            'montant' => '50',
            'statut' => 'completed',
            'statut_paiement' => 'yes',
            'creer' => date('Y-m-d H:i:s'),
        ]);

        $check1 = ReferralRewardService::checkAndProcessAppInstallReward($driverBId, 'driver');
        $this->assertFalse($check1['reward_processed']);

        // 6. Driver B completes 2nd ride of ₹60 (Total 2 rides, ₹110) -> Qualifies because min_services = 2 is met!
        DB::table('tj_requete')->insert([
            'id_user_app' => 1,
            'id_conducteur' => $driverBId,
            'montant' => '60',
            'statut' => 'completed',
            'statut_paiement' => 'yes',
            'creer' => date('Y-m-d H:i:s'),
        ]);

        $check2 = ReferralRewardService::checkAndProcessAppInstallReward($driverBId, 'driver');
        $this->assertTrue($check2['reward_processed']);
        $this->assertEquals(50.0, (float)$check2['reward_amount']);

        // Assert User A wallet has ₹50
        $userABalance = (float)DB::table('tj_user_app')->where('id', $userAId)->value('amount');
        $this->assertEquals(50.0, $userABalance);
    }
}
