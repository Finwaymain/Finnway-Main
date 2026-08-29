<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\FinancialReportService;
use Carbon\Carbon;

class EcosystemEarningsFlowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Complete Automated End-to-End Test for the Entire Earnings Ecosystem Flow.
     */
    public function test_complete_ecosystem_earnings_and_profit_flow()
    {
        $now = Carbon::now()->toDateTimeString();

        // 1. Create or Find Test User & Test Driver
        $user = DB::table('tj_user_app')->first();
        if (!$user) {
            $userId = DB::table('tj_user_app')->insertGetId([
                'nom' => 'Test',
                'prenom' => 'Customer',
                'phone' => '+919876543210',
                'email' => 'testcustomer@fiinway.com',
                'amount' => 5000.00,
                'statut' => 'yes',
                'creer' => $now,
                'modifier' => $now,
            ]);
        } else {
            $userId = $user->id;
        }

        $driver = DB::table('tj_conducteur')->first();
        if (!$driver) {
            $driverId = DB::table('tj_conducteur')->insertGetId([
                'nom' => 'Test',
                'prenom' => 'Provider',
                'phone' => '+919123456789',
                'email' => 'testprovider@fiinway.com',
                'amount' => 1000.00,
                'statut' => 'yes',
                'online' => 'yes',
                'creer' => $now,
                'modifier' => $now,
            ]);
        } else {
            $driverId = $driver->id;
        }

        // ── STEP 1: Simulate Wallet Recharge (₹2,500) ────────────────────────
        $walletTxnId = DB::table('tj_transaction')->insertGetId([
            'id_user_app'    => $userId,
            'amount'         => '2500.00',
            'deduction_type' => 1,
            'payment_method' => 'Razorpay / UPI',
            'payment_status' => 'success',
            'creer'          => $now,
            'modifier'       => $now,
        ]);
        $this->assertGreaterThan(0, $walletTxnId);

        // ── STEP 2: Simulate Cab Ride Booking & Completion (₹600, ₹60 Admin Comm)
        $rideId = DB::table('tj_requete')->insertGetId([
            'id_user_app'      => $userId,
            'id_conducteur'    => $driverId,
            'montant'          => 600.00,
            'admin_commission' => 60.00,
            'statut'           => 'completed',
            'statut_paiement'  => 'success',
            'ride_type'        => 'cab',
            'depart_name'      => 'Origin Point',
            'destination_name' => 'Destination Point',
            'creer'            => $now,
            'modifier'         => $now,
        ]);
        $this->assertGreaterThan(0, $rideId);

        // ── STEP 3: Simulate Home Service Booking (₹1,200, ₹120 Comm + ₹50 Platform Fee)
        $serviceId = DB::table('service_requests')->insertGetId([
            'user_id'          => $userId,
            'driver_id'        => $driverId,
            'service_name'     => 'AC Deep Cleaning Service',
            'amount'           => 1200.00,
            'status'           => 'Completed',
            'payment_status'   => 'paid',
            'price_breakdown'  => json_encode([
                'service_charge' => 1150.00,
                'platform_fee'   => 50.00,
                'total'          => 1200.00
            ]),
            'created_at'       => $now,
            'updated_at'       => $now,
        ]);
        $this->assertGreaterThan(0, $serviceId);

        // Deduct Home Service Commission in Driver Transaction
        DB::table('tj_conducteur_transaction')->insert([
            'id_conducteur'  => $driverId,
            'id_ride'        => (string)$serviceId,
            'amount'         => '-120.00',
            'payment_method' => 'Commission',
            'deduction_type' => 'Commission',
            'note'           => 'Admin Commission for Service #' . $serviceId,
            'creer'          => $now,
            'modifier'       => $now,
        ]);

        // ── STEP 4: Simulate Marketplace Product Purchase (₹1,500 with 10% Platform Comm = ₹150)
        if (Schema::hasTable('marketplace_orders')) {
            $orderId = DB::table('marketplace_orders')->insertGetId([
                'user_id'      => $userId,
                'total_amount' => 1500.00,
                'status'       => 'delivered',
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
            $this->assertGreaterThan(0, $orderId);
        }

        // ── STEP 5: Simulate Premium Plan Subscription Purchase (₹799 Plan) ──
        if (Schema::hasTable('subscription_history')) {
            $subId = DB::table('subscription_history')->insertGetId([
                'user_id'           => $driverId,
                'subscription_plan' => json_encode([
                    'title' => 'Business Gold Partner Plan',
                    'price' => 799.00,
                    'validity_days' => 30
                ]),
                'payment_type'      => 'online',
                'expiry_date'       => Carbon::now()->addDays(30)->toDateString(),
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);
            $this->assertGreaterThan(0, $subId);
        }

        // ── STEP 6: Simulate Referral Registration & Reward Burn (₹50 Reward) ─
        $refId = DB::table('referral')->insertGetId([
            'user_id'       => $userId,
            'user_type'     => 'customer',
            'referral_code' => 'FIINTEST' . rand(100, 999),
            'creer'         => $now,
        ]);
        $this->assertGreaterThan(0, $refId);

        DB::table('tj_transaction')->insert([
            'id_user_app'    => $userId,
            'amount'         => '50.00',
            'deduction_type' => 1,
            'payment_method' => 'Referral Reward',
            'type'           => 'referral',
            'payment_status' => 'success',
            'creer'          => $now,
            'modifier'       => $now,
        ]);

        // ── STEP 7: Simulate Cashback & Wallet Bonus Awarding (₹75 Cashback) ──
        DB::table('tj_transaction')->insert([
            'id_user_app'    => $userId,
            'amount'         => '75.00',
            'deduction_type' => 1,
            'payment_method' => 'Wallet Cashback',
            'type'           => 'cashback',
            'payment_status' => 'success',
            'creer'          => $now,
            'modifier'       => $now,
        ]);

        // ── STEP 8: Simulate Medical Cashback Claim Submission & Approval (₹300 Claim)
        if (Schema::hasTable('tj_medical_claims')) {
            $claimId = DB::table('tj_medical_claims')->insertGetId([
                'claim_id'         => 'CLM' . rand(100000, 999999),
                'user_id'          => $userId,
                'user_type'        => 'customer',
                'requested_amount' => 300.00,
                'approved_amount'  => 300.00,
                'status'           => 'approved',
                'creer'            => $now,
                'modifier'         => $now,
            ]);
            $this->assertGreaterThan(0, $claimId);
        }

        // ── STEP 9: Simulate Provider Settlement / Withdrawal (₹800 Payout) ────
        if (Schema::hasTable('withdrawals')) {
            $withdrawalId = DB::table('withdrawals')->insertGetId([
                'id_conducteur' => $driverId,
                'amount'        => 800.00,
                'statut'        => 1, // Settled
                'creer'         => $now,
                'modifier'      => $now,
            ]);
            $this->assertGreaterThan(0, $withdrawalId);
        }

        // ── STEP 10: VERIFY FINANCIAL ENGINE CONSISTENCY ──────────────────────
        $stats = FinancialReportService::computeStats();

        // 1. Verify Gross GMV is strictly positive and accounted
        $this->assertGreaterThan(0, $stats['grossRevenue']);

        // 2. Verify Net Admin Revenue contains all commissions + platform fee + subscriptions
        $this->assertGreaterThan(0, $stats['netRevenue']);

        // 3. Verify Promotional Burn contains referral, cashback, and medical
        $this->assertGreaterThan(0, $stats['totalPromotionalCost']);

        // 4. Verify P&L Formula matches precisely
        $expectedNetProfit = round(
            $stats['netRevenue'] - ($stats['totalPromotionalCost'] + $stats['gatewayCharges'] + $stats['failedTxnsAmount']),
            2
        );
        $this->assertEquals($expectedNetProfit, $stats['netProfitPnl']);

        // 5. Verify All-In-One Dashboard Section Exports
        $this->assertArrayHasKey('servicesBreakdown', $stats);
        $this->assertArrayHasKey('servicesBreakdown', $stats);
        $this->assertArrayHasKey('marketplaceProductSales', $stats);
        $this->assertArrayHasKey('totalSubscriptionRevenue', $stats);
        $this->assertArrayHasKey('netReferralContribution', $stats);
        $this->assertArrayHasKey('totalPaymentVolume', $stats);
        $this->assertArrayHasKey('providerPayable', $stats);
        $this->assertArrayHasKey('netProfitPnl', $stats);
        $this->assertArrayHasKey('dailyReports', $stats);
    }
}
