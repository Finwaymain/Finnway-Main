<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RazorpayWebhookCreditTest extends TestCase
{
    /**
     * 1. Test crediting customer wallet when recipient ac_no is present in notes['comment']
     */
    public function test_webhook_credits_user_via_notes()
    {
        $user = DB::table('tj_user_app')->whereNotNull('ac_no')->first();
        if (!$user) {
            $this->markTestSkipped('No user with ac_no found in test database');
        }

        $initAmount = floatval($user->amount);
        $testPaymentId = 'pay_test_' . time() . '_' . rand(100, 999);
        $creditAmountRupees = 15.00;
        $amountPaise = $creditAmountRupees * 100;

        $payload = [
            'event'   => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id'      => $testPaymentId,
                        'amount'  => $amountPaise,
                        'status'  => 'captured',
                        'contact' => '+919999000000',
                        'vpa'     => 'sender@okhdfcbank',
                        'notes'   => [
                            'comment' => $user->ac_no,
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->postJson('/api/v1/payments/razorpay/webhook', $payload);
        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        // Check user amount incremented
        $updatedUser = DB::table('tj_user_app')->where('id', $user->id)->first();
        $this->assertEquals($initAmount + $creditAmountRupees, floatval($updatedUser->amount));

        // Check upi_qr_transactions record exists
        $txn = DB::table('upi_qr_transactions')->where('razorpay_payment_id', $testPaymentId)->first();
        $this->assertNotNull($txn);
        $this->assertTrue((bool)$txn->wallet_credited);
        $this->assertEquals('captured', $txn->status);
        $this->assertEquals($user->id, $txn->user_id);
    }

    /**
     * 2. Test fallback: PhonePe/GPay QR payment where notes is empty, but payer phone matches user
     */
    public function test_webhook_credits_user_via_payer_phone_fallback()
    {
        $user = DB::table('tj_user_app')->whereNotNull('phone')->where('phone', '!=', '')->first();
        if (!$user) {
            $this->markTestSkipped('No user with phone found');
        }

        $initAmount = floatval($user->amount);
        $testPaymentId = 'pay_phone_' . time() . '_' . rand(100, 999);
        $creditAmountRupees = 25.00;
        $amountPaise = $creditAmountRupees * 100;

        // Payload has NO ac_no in notes - only payer phone
        $payload = [
            'event'   => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id'      => $testPaymentId,
                        'amount'  => $amountPaise,
                        'status'  => 'captured',
                        'contact' => $user->phone,
                        'vpa'     => 'payer@upi',
                        'notes'   => [],
                    ],
                ],
            ],
        ];

        $response = $this->postJson('/api/v1/payments/razorpay/webhook', $payload);
        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $updatedUser = DB::table('tj_user_app')->where('id', $user->id)->first();
        $this->assertEquals($initAmount + $creditAmountRupees, floatval($updatedUser->amount));

        $txn = DB::table('upi_qr_transactions')->where('razorpay_payment_id', $testPaymentId)->first();
        $this->assertNotNull($txn);
        $this->assertTrue((bool)$txn->wallet_credited);
        $this->assertEquals($user->id, $txn->user_id);
    }

    /**
     * 3. Test fallback: Payer VPA contains 10-digit phone (e.g. 9669454554@ybl) matching user
     */
    public function test_webhook_credits_user_via_payer_vpa_fallback()
    {
        $user = DB::table('tj_user_app')->whereNotNull('phone')->where('phone', '!=', '')->first();
        if (!$user) {
            $this->markTestSkipped('No user with phone found');
        }

        $rawPhone = preg_replace('/\D/', '', $user->phone);
        $digits10 = strlen($rawPhone) >= 10 ? substr($rawPhone, -10) : $rawPhone;
        if (strlen($digits10) !== 10) {
            $this->markTestSkipped('No 10-digit phone user found');
        }

        $initAmount = floatval($user->amount);
        $testPaymentId = 'pay_vpa_' . time() . '_' . rand(100, 999);
        $creditAmountRupees = 30.00;
        $amountPaise = $creditAmountRupees * 100;

        $payload = [
            'event'   => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id'      => $testPaymentId,
                        'amount'  => $amountPaise,
                        'status'  => 'captured',
                        'contact' => '',
                        'vpa'     => $digits10 . '@oksbi',
                        'notes'   => [],
                    ],
                ],
            ],
        ];

        $response = $this->postJson('/api/v1/payments/razorpay/webhook', $payload);
        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $updatedUser = DB::table('tj_user_app')->where('id', $user->id)->first();
        $this->assertEquals($initAmount + $creditAmountRupees, floatval($updatedUser->amount));
    }

    /**
     * 4. Test crediting driver account (tj_conducteur) via webhook
     */
    public function test_webhook_credits_driver_account()
    {
        $driver = DB::table('tj_conducteur')->whereNotNull('phone')->where('phone', '!=', '')->first();
        if (!$driver) {
            $this->markTestSkipped('No driver found');
        }

        $initAmount = floatval($driver->amount);
        $testPaymentId = 'pay_drv_' . time() . '_' . rand(100, 999);
        $creditAmountRupees = 40.00;
        $amountPaise = $creditAmountRupees * 100;

        $payload = [
            'event'   => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id'      => $testPaymentId,
                        'amount'  => $amountPaise,
                        'status'  => 'captured',
                        'contact' => $driver->phone,
                        'vpa'     => 'driver_payer@paytm',
                        'notes'   => [],
                    ],
                ],
            ],
        ];

        $response = $this->postJson('/api/v1/payments/razorpay/webhook', $payload);
        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $updatedDriver = DB::table('tj_conducteur')->where('id', $driver->id)->first();
        $this->assertEquals($initAmount + $creditAmountRupees, floatval($updatedDriver->amount));

        $txn = DB::table('upi_qr_transactions')->where('razorpay_payment_id', $testPaymentId)->first();
        $this->assertNotNull($txn);
        $this->assertEquals('driver', $txn->user_type);
        $this->assertEquals($driver->id, $txn->user_id);
    }

    /**
     * 5. Test idempotency: Calling webhook twice for the same payment does NOT double-credit
     */
    public function test_webhook_idempotency_prevents_duplicate_crediting()
    {
        $user = DB::table('tj_user_app')->whereNotNull('phone')->first();
        if (!$user) {
            $this->markTestSkipped('No user found');
        }

        $initAmount = floatval($user->amount);
        $testPaymentId = 'pay_idem_' . time() . '_' . rand(100, 999);
        $creditAmountRupees = 20.00;
        $amountPaise = $creditAmountRupees * 100;

        $payload = [
            'event'   => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id'      => $testPaymentId,
                        'amount'  => $amountPaise,
                        'status'  => 'captured',
                        'contact' => $user->phone,
                        'notes'   => [],
                    ],
                ],
            ],
        ];

        // First delivery: should credit
        $res1 = $this->postJson('/api/v1/payments/razorpay/webhook', $payload);
        $res1->assertStatus(200);
        $res1->assertJson(['status' => 'success']);

        $afterFirst = floatval(DB::table('tj_user_app')->where('id', $user->id)->value('amount'));
        $this->assertEquals($initAmount + $creditAmountRupees, $afterFirst);

        // Second duplicate delivery: should be detected as already processed
        $res2 = $this->postJson('/api/v1/payments/razorpay/webhook', $payload);
        $res2->assertStatus(200);
        $res2->assertJson(['status' => 'success', 'message' => 'Already processed and credited']);

        // Amount must remain EXACTLY unchanged (not credited twice)
        $afterSecond = floatval(DB::table('tj_user_app')->where('id', $user->id)->value('amount'));
        $this->assertEquals($afterFirst, $afterSecond);
    }

    /**
     * 6. Test unknown user: saves transaction as unassigned and allows admin to assign
     */
    public function test_webhook_records_unassigned_and_admin_can_assign()
    {
        $testPaymentId = 'pay_unassigned_' . time() . '_' . rand(100, 999);
        $amountRupees = 35.00;
        $amountPaise = $amountRupees * 100;

        $payload = [
            'event'   => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id'      => $testPaymentId,
                        'amount'  => $amountPaise,
                        'status'  => 'captured',
                        'contact' => '+910000111222',
                        'vpa'     => 'stranger@upi',
                        'notes'   => [],
                    ],
                ],
            ],
        ];

        $response = $this->postJson('/api/v1/payments/razorpay/webhook', $payload);
        $response->assertStatus(200);
        $response->assertJson(['status' => 'recorded_unassigned']);

        // Check transaction was still saved into upi_qr_transactions!
        $txn = DB::table('upi_qr_transactions')->where('razorpay_payment_id', $testPaymentId)->first();
        $this->assertNotNull($txn);
        $this->assertFalse((bool)$txn->wallet_credited);
        $this->assertEquals('unassigned', $txn->status);
        $this->assertNull($txn->user_id);

        // Now test admin assigning this unassigned transaction to a real user
        $user = DB::table('tj_user_app')->first();
        if ($user) {
            $adminUser = \App\Models\User::first() ?: \App\Models\User::factory()->create();
            $this->actingAs($adminUser);

            $userInitAmount = floatval($user->amount);
            $assignResponse = $this->post('/walletstransactions/upi/assign', [
                'id'        => $txn->id,
                'user_id'   => $user->id,
                'user_type' => 'customer',
            ]);
            $assignResponse->assertRedirect();

            $updatedTxn = DB::table('upi_qr_transactions')->where('id', $txn->id)->first();
            $this->assertTrue((bool)$updatedTxn->wallet_credited);
            $this->assertEquals('captured', $updatedTxn->status);
            $this->assertEquals($user->id, $updatedTxn->user_id);

            $refreshedUser = DB::table('tj_user_app')->where('id', $user->id)->first();
            $this->assertEquals($userInitAmount + $amountRupees, floatval($refreshedUser->amount));
        }
    }

    /**
     * 7. Test Admin manual credit by Razorpay Payment ID directly
     */
    public function test_admin_manual_credit_by_payment_id()
    {
        $user = DB::table('tj_user_app')->first();
        if (!$user) {
            $this->markTestSkipped('No user found');
        }

        $adminUser = \App\Models\User::first() ?: \App\Models\User::factory()->create();
        $this->actingAs($adminUser);

        $initAmount = floatval($user->amount);
        $testPaymentId = 'pay_manual_' . time() . '_' . rand(100, 999);
        $creditAmount = 50.00;

        $response = $this->post('/walletstransactions/upi/manual-credit', [
            'razorpay_payment_id' => $testPaymentId,
            'amount'              => $creditAmount,
            'user_id'             => $user->id,
            'user_type'           => 'customer',
            'payer_name'          => 'Manual Test Payer',
        ]);
        $response->assertRedirect();

        $refreshedUser = DB::table('tj_user_app')->where('id', $user->id)->first();
        $this->assertEquals($initAmount + $creditAmount, floatval($refreshedUser->amount));

        $txn = DB::table('upi_qr_transactions')->where('razorpay_payment_id', $testPaymentId)->first();
        $this->assertNotNull($txn);
        $this->assertTrue((bool)$txn->wallet_credited);
        $this->assertEquals('captured', $txn->status);
    }

    /**
     * 8. Test user search AJAX endpoint for admin dropdown
     */
    public function test_admin_search_users_for_upi()
    {
        $adminUser = \App\Models\User::first() ?: \App\Models\User::factory()->create();
        $this->actingAs($adminUser);

        $user = DB::table('tj_user_app')->first();
        if (!$user) {
            $this->markTestSkipped('No user found');
        }

        $query = substr($user->prenom ?: $user->nom ?: $user->phone, 0, 4);
        $response = $this->getJson('/walletstransactions/upi/search-users?q=' . urlencode($query));
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey('id', $data[0]);
        $this->assertArrayHasKey('user_type', $data[0]);
        $this->assertArrayHasKey('label', $data[0]);
    }

    /**
     * 9. Test payment.failed event recording
     */
    public function test_webhook_records_payment_failed_event()
    {
        $testPaymentId = 'pay_fail_' . time() . '_' . rand(100, 999);

        $payload = [
            'event'   => 'payment.failed',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id'      => $testPaymentId,
                        'amount'  => 5000,
                        'status'  => 'failed',
                        'contact' => '+919999888877',
                        'vpa'     => 'failed_user@upi',
                        'notes'   => [],
                    ],
                ],
            ],
        ];

        $response = $this->postJson('/api/v1/payments/razorpay/webhook', $payload);
        $response->assertStatus(200);
        $response->assertJson(['status' => 'success', 'message' => 'Recorded failed payment']);

        $txn = DB::table('upi_qr_transactions')->where('razorpay_payment_id', $testPaymentId)->first();
        $this->assertNotNull($txn);
        $this->assertEquals('failed', $txn->status);
        $this->assertFalse((bool)$txn->wallet_credited);
    }

    /**
     * 10. Test Admin UPI Payments page renders HTTP 200 with stats
     */
    public function test_admin_upi_payments_page_renders()
    {
        $adminUser = \App\Models\User::first() ?: \App\Models\User::factory()->create();
        $this->actingAs($adminUser);

        $response = $this->get('/walletstransactions/upi');
        $response->assertStatus(200);
        $response->assertSee('UPI QR Payments');
        $response->assertSee('Total QR Transactions');
        $response->assertSee('Total Collected Volume');
    }
}
