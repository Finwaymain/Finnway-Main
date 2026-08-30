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

class ReferralAppInstallQualificationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_admin_can_save_app_install_qualification_rules()
    {
        ApiKeySetting::updateOrCreate(
            ['key_name' => 'event_rule_app_install_user_enable'],
            ['group' => 'referral_event', 'provider' => 'rule', 'key_value' => '1', 'is_active' => true]
        );
        ApiKeySetting::updateOrCreate(
            ['key_name' => 'event_rule_app_install_user_type'],
            ['group' => 'referral_event', 'provider' => 'rule', 'key_value' => 'flat', 'is_active' => true]
        );
        ApiKeySetting::updateOrCreate(
            ['key_name' => 'event_rule_app_install_user_value'],
            ['group' => 'referral_event', 'provider' => 'rule', 'key_value' => '10', 'is_active' => true]
        );
        ApiKeySetting::updateOrCreate(
            ['key_name' => 'event_rule_app_install_user_min_services'],
            ['group' => 'referral_event', 'provider' => 'rule', 'key_value' => '5', 'is_active' => true]
        );
        ApiKeySetting::updateOrCreate(
            ['key_name' => 'event_rule_app_install_user_min_amount'],
            ['group' => 'referral_event', 'provider' => 'rule', 'key_value' => '500', 'is_active' => true]
        );

        $this->assertEquals('1', ApiKeySetting::getApiKeyValue('event_rule_app_install_user_enable'));
        $this->assertEquals('flat', ApiKeySetting::getApiKeyValue('event_rule_app_install_user_type'));
        $this->assertEquals('10', ApiKeySetting::getApiKeyValue('event_rule_app_install_user_value'));
        $this->assertEquals('5', ApiKeySetting::getApiKeyValue('event_rule_app_install_user_min_services'));
        $this->assertEquals('500', ApiKeySetting::getApiKeyValue('event_rule_app_install_user_min_amount'));
    }

    public function test_user_referral_milestone_qualification_flow()
    {
        // 1. Configure Admin Rules: ₹10 Flat reward, requires at least 5 services and ₹500 total spend
        ApiKeySetting::updateOrCreate(['key_name' => 'event_rule_app_install_user_enable'], ['group' => 'referral_event', 'provider' => 'rule', 'key_value' => '1', 'is_active' => true]);
        ApiKeySetting::updateOrCreate(['key_name' => 'event_rule_app_install_user_type'], ['group' => 'referral_event', 'provider' => 'rule', 'key_value' => 'flat', 'is_active' => true]);
        ApiKeySetting::updateOrCreate(['key_name' => 'event_rule_app_install_user_value'], ['group' => 'referral_event', 'provider' => 'rule', 'key_value' => '10', 'is_active' => true]);
        ApiKeySetting::updateOrCreate(['key_name' => 'event_rule_app_install_user_min_services'], ['group' => 'referral_event', 'provider' => 'rule', 'key_value' => '5', 'is_active' => true]);
        ApiKeySetting::updateOrCreate(['key_name' => 'event_rule_app_install_user_min_amount'], ['group' => 'referral_event', 'provider' => 'rule', 'key_value' => '500', 'is_active' => true]);

        // 2. Create Referrer (User A)
        $uniquePhoneA = '99999' . rand(10000, 99999);
        $userAId = DB::table('tj_user_app')->insertGetId([
            'prenom' => 'Referrer',
            'nom' => 'UserA',
            'phone' => $uniquePhoneA,
            'email' => 'userA_' . uniqid() . '@test.com',
            'statut' => 'yes',
            'amount' => '0',
            'creer' => date('Y-m-d H:i:s'),
        ]);

        $codeA = ReferralCodeService::getOrCreateReferralCode($userAId, 'customer');

        // 3. Create Referee (User B) referred by User A
        $uniquePhoneB = '99998' . rand(10000, 99999);
        $userBId = DB::table('tj_user_app')->insertGetId([
            'prenom' => 'Referee',
            'nom' => 'UserB',
            'phone' => $uniquePhoneB,
            'email' => 'userB_' . uniqid() . '@test.com',
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

        // 4. Initial Check: User A must NOT have received the App Install reward
        $check0 = ReferralRewardService::checkAndProcessAppInstallReward($userBId, 'customer');
        $this->assertFalse($check0['reward_processed']);
        $this->assertFalse($check0['qualified'] ?? true);

        // 5. User B completes 4 services of ₹100 each (Total 4 services, ₹400 spend)
        for ($i = 1; $i <= 4; $i++) {
            DB::table('tj_requete')->insert([
                'id_user_app' => $userBId,
                'id_conducteur' => 1,
                'montant' => '100',
                'statut' => 'completed',
                'statut_paiement' => 'yes',
                'creer' => date('Y-m-d H:i:s'),
            ]);

            $check = ReferralRewardService::checkAndProcessAppInstallReward($userBId, 'customer');
            $this->assertFalse($check['reward_processed'], "Should not qualify at service count {$i}");
        }

        // 6. User B completes 5th service with ₹150 (Total 5 services, ₹550 spend) -> Qualified!
        DB::table('tj_requete')->insert([
            'id_user_app' => $userBId,
            'id_conducteur' => 1,
            'montant' => '150',
            'statut' => 'completed',
            'statut_paiement' => 'yes',
            'creer' => date('Y-m-d H:i:s'),
        ]);

        $checkFinal = ReferralRewardService::checkAndProcessAppInstallReward($userBId, 'customer');
        $this->assertTrue($checkFinal['reward_processed']);
        $this->assertTrue($checkFinal['qualified']);
        $this->assertEquals(10.0, (float)$checkFinal['reward_amount']);

        // Assert User A wallet credited
        $userABalance = (float)DB::table('tj_user_app')->where('id', $userAId)->value('amount');
        $this->assertEquals(10.0, $userABalance);

        // Assert referral marked as paid
        $isPaid = DB::table('referral')->where('user_id', $userBId)->value('app_install_reward_paid');
        $this->assertEquals(1, $isPaid);

        // 7. User B completes 6th service -> No duplicate reward
        $checkDup = ReferralRewardService::checkAndProcessAppInstallReward($userBId, 'customer');
        $this->assertFalse($checkDup['reward_processed']);
        $this->assertEquals('App install referral reward already credited', $checkDup['reason']);
    }

    public function test_driver_business_referral_qualification_flow()
    {
        // 1. Configure Admin Rules: ₹50 Flat reward, requires at least 2 services and ₹200 total spend
        ApiKeySetting::updateOrCreate(['key_name' => 'event_rule_app_install_business_enable'], ['group' => 'referral_event', 'provider' => 'rule', 'key_value' => '1', 'is_active' => true]);
        ApiKeySetting::updateOrCreate(['key_name' => 'event_rule_app_install_business_type'], ['group' => 'referral_event', 'provider' => 'rule', 'key_value' => 'flat', 'is_active' => true]);
        ApiKeySetting::updateOrCreate(['key_name' => 'event_rule_app_install_business_value'], ['group' => 'referral_event', 'provider' => 'rule', 'key_value' => '50', 'is_active' => true]);
        ApiKeySetting::updateOrCreate(['key_name' => 'event_rule_app_install_business_min_services'], ['group' => 'referral_event', 'provider' => 'rule', 'key_value' => '2', 'is_active' => true]);
        ApiKeySetting::updateOrCreate(['key_name' => 'event_rule_app_install_business_min_amount'], ['group' => 'referral_event', 'provider' => 'rule', 'key_value' => '200', 'is_active' => true]);

        // 2. Create Referrer (User A)
        $uniquePhoneA = '99997' . rand(10000, 99999);
        $userAId = DB::table('tj_user_app')->insertGetId([
            'prenom' => 'Referrer',
            'nom' => 'UserA',
            'phone' => $uniquePhoneA,
            'email' => 'userA_drv_' . uniqid() . '@test.com',
            'statut' => 'yes',
            'amount' => '0',
            'creer' => date('Y-m-d H:i:s'),
        ]);

        $codeA = ReferralCodeService::getOrCreateReferralCode($userAId, 'customer');

        // 3. Create Referee Driver (Driver B)
        $uniquePhoneB = '99996' . rand(10000, 99999);
        $driverBId = DB::table('tj_conducteur')->insertGetId([
            'prenom' => 'Partner',
            'nom' => 'DriverB',
            'phone' => $uniquePhoneB,
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

        // 5. Driver B completes 1st ride of ₹100 -> not qualified
        DB::table('tj_requete')->insert([
            'id_user_app' => 1,
            'id_conducteur' => $driverBId,
            'montant' => '100',
            'statut' => 'completed',
            'statut_paiement' => 'yes',
            'creer' => date('Y-m-d H:i:s'),
        ]);

        $check1 = ReferralRewardService::checkAndProcessAppInstallReward($driverBId, 'driver');
        $this->assertFalse($check1['reward_processed']);

        // 6. Driver B completes 2nd ride of ₹150 (Total 2 rides, ₹250) -> Qualified!
        DB::table('tj_requete')->insert([
            'id_user_app' => 1,
            'id_conducteur' => $driverBId,
            'montant' => '150',
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
