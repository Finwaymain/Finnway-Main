<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RazorpayWebhookCreditTest extends TestCase
{
    public function test_webhook_credits_user_via_notes()
    {
        // Find or create test customer
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

    public function test_webhook_credits_user_via_payer_phone_fallback()
    {
        // Find customer with phone
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

    public function test_webhook_records_unassigned_when_user_not_found()
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
                        'contact' => '+911111111111',
                        'vpa'     => 'unknown_stranger@upi',
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
}
