<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Models\WalletTopupCashbackRule;
use App\Services\WalletTopupCashbackService;

class CashbackConversionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Ensure settings threshold is 500
        DB::table('tj_settings')->updateOrInsert(
            ['id' => 1],
            ['min_cashback_redeem_threshold' => 500.00]
        );
    }

    public function test_cashback_topup_credits_cashback_balance_not_main_wallet_amount()
    {
        // 1. Create a test user
        $userId = DB::table('tj_user_app')->insertGetId([
            'nom'              => 'Test',
            'prenom'           => 'User',
            'phone'            => '+919999988888',
            'amount'           => '100.00',
            'cashback_balance' => 0.00,
            'statut'           => 'yes',
            'creer'            => date('Y-m-d H:i:s'),
            'modifier'         => date('Y-m-d H:i:s'),
        ]);

        // 2. Create an active flat cashback rule: ₹50 cashback on ₹500+ topup
        $rule = WalletTopupCashbackRule::create([
            'name'              => 'Test Cashback Rule',
            'cashback_type'     => 'flat',
            'cashback_value'    => 50.00,
            'min_topup_amount'  => 500.00,
            'max_cashback_cap'  => 50.00,
            'target_user_type'  => 'all',
            'is_active'         => true,
            'priority'          => 10,
        ]);

        // 3. Process topup cashback for this user
        $result = WalletTopupCashbackService::processTopupCashback($userId, 'customer', 1000.00, 'TEST_TXN_001');

        $this->assertIsArray($result);
        $this->assertEquals(50.00, $result['cashback_amount']);

        // 4. Assert user main wallet amount is UNTOUCHED (still 100.00)
        $user = DB::table('tj_user_app')->where('id', $userId)->first();
        $this->assertEquals(100.00, (float) $user->amount);

        // 5. Assert cashback_balance has been credited with 50.00
        $this->assertEquals(50.00, (float) $user->cashback_balance);

        // Clean up
        $rule->delete();
        DB::table('tj_user_app')->where('id', $userId)->delete();
    }

    public function test_cashback_conversion_fails_below_threshold()
    {
        // User with ₹300 cashback (threshold is ₹500)
        $userId = DB::table('tj_user_app')->insertGetId([
            'nom'              => 'Test',
            'prenom'           => 'LowCashback',
            'phone'            => '+919999977777',
            'amount'           => '200.00',
            'cashback_balance' => 300.00,
            'statut'           => 'yes',
            'creer'            => date('Y-m-d H:i:s'),
            'modifier'         => date('Y-m-d H:i:s'),
        ]);

        $response = $this->postJson('/api/v1/cashback/convert-to-wallet', [
            'user_id'   => $userId,
            'user_type' => 'customer',
            'amount'    => 300.00,
        ]);

        $response->assertStatus(422);
        $this->assertEquals('error', $response->json('res'));
        $this->assertStringContainsString('Minimum ₹500.00 cashback required', $response->json('msg'));

        // Clean up
        DB::table('tj_user_app')->where('id', $userId)->delete();
    }

    public function test_cashback_conversion_succeeds_at_or_above_threshold()
    {
        // User with ₹650 cashback (threshold is ₹500)
        $userId = DB::table('tj_user_app')->insertGetId([
            'nom'              => 'Test',
            'prenom'           => 'EligibleCashback',
            'phone'            => '+919999966666',
            'amount'           => '50.00',
            'cashback_balance' => 650.00,
            'statut'           => 'yes',
            'creer'            => date('Y-m-d H:i:s'),
            'modifier'         => date('Y-m-d H:i:s'),
        ]);

        $response = $this->postJson('/api/v1/cashback/convert-to-wallet', [
            'user_id'   => $userId,
            'user_type' => 'customer',
            'amount'    => 650.00,
        ]);

        $response->assertStatus(200);
        $this->assertEquals('success', $response->json('res'));
        $this->assertEquals('700', (string) $response->json('data.wallet_amount'));
        $this->assertEquals('0', (string) $response->json('data.cashback_balance'));

        // Check DB state
        $user = DB::table('tj_user_app')->where('id', $userId)->first();
        $this->assertEquals(700.00, (float) $user->amount);
        $this->assertEquals(0.00, (float) $user->cashback_balance);

        // Check transaction record
        $txn = DB::table('tj_transaction')->where('id_user_app', $userId)->where('payment_method', 'Cashback Conversion')->first();
        $this->assertNotNull($txn);
        $this->assertEquals(650.00, (float) $txn->amount);

        // Clean up
        DB::table('tj_transaction')->where('id_user_app', $userId)->delete();
        DB::table('tj_user_app')->where('id', $userId)->delete();
    }

    public function test_driver_cashback_conversion_succeeds()
    {
        // Driver with ₹550 cashback
        $driverId = DB::table('tj_conducteur')->insertGetId([
            'nom'              => 'Driver',
            'prenom'           => 'Hero',
            'phone'            => '+919999955555',
            'amount'           => '120.00',
            'cashback_balance' => 550.00,
            'statut'           => 'yes',
            'creer'            => date('Y-m-d H:i:s'),
            'modifier'         => date('Y-m-d H:i:s'),
        ]);

        $response = $this->postJson('/api/v1/convert_cashback_to_wallet/driver', [
            'user_id'   => $driverId,
            'user_type' => 'driver',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('success', $response->json('res'));
        $this->assertEquals('670', (string) $response->json('data.wallet_amount'));
        $this->assertEquals('0', (string) $response->json('data.cashback_balance'));

        // Check driver DB state
        $driver = DB::table('tj_conducteur')->where('id', $driverId)->first();
        $this->assertEquals(670.00, (float) $driver->amount);
        $this->assertEquals(0.00, (float) $driver->cashback_balance);

        // Clean up
        DB::table('tj_conducteur_transaction')->where('id_conducteur', $driverId)->delete();
        DB::table('tj_conducteur')->where('id', $driverId)->delete();
    }

    public function test_show_wallet_amount_returns_cashback_and_threshold()
    {
        $userId = DB::table('tj_user_app')->insertGetId([
            'nom'              => 'Tester',
            'prenom'           => 'Balance',
            'phone'            => '+919999944444',
            'amount'           => '250.00',
            'cashback_balance' => 125.00,
            'statut'           => 'yes',
            'creer'            => date('Y-m-d H:i:s'),
            'modifier'         => date('Y-m-d H:i:s'),
        ]);

        $response = $this->postJson('/api/v1/show_wallet_amount/smart-value', [
            'user_id'   => $userId,
            'user_type' => 'customer',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('250', (string) $response->json('data.wallet_amount'));
        $this->assertEquals('125', (string) $response->json('data.cashback_balance'));
        $this->assertEquals('500', (string) $response->json('data.min_cashback_redeem_threshold'));

        DB::table('tj_user_app')->where('id', $userId)->delete();
    }
}
