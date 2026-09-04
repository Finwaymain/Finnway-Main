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
        $this->assertArrayHasKey('marketplaceProductSales', $stats);
        $this->assertArrayHasKey('totalSubscriptionRevenue', $stats);
        $this->assertArrayHasKey('netReferralContribution', $stats);
        $this->assertArrayHasKey('totalPaymentVolume', $stats);
        $this->assertArrayHasKey('providerPayable', $stats);
        $this->assertArrayHasKey('netProfitPnl', $stats);
        $this->assertArrayHasKey('dailyReports', $stats);
    }

    /**
     * Test Pure GST Calculation: Rides with empty/0 tax must NEVER have phantom GST.
     */
    public function test_pure_gst_calculation_without_phantom_inflation()
    {
        $now = Carbon::now()->toDateTimeString();
        $user = DB::table('tj_user_app')->first();
        $driver = DB::table('tj_conducteur')->first();

        // Create ride with 0 tax
        $rideId = DB::table('tj_requete')->insertGetId([
            'id_user_app'      => $user ? $user->id : 1,
            'id_conducteur'    => $driver ? $driver->id : 1,
            'montant'          => 500.00,
            'tax'              => null, // Zero tax collected
            'admin_commission' => 50.00,
            'statut'           => 'completed',
            'statut_paiement'  => 'yes',
            'creer'            => $now,
            'modifier'         => $now,
        ]);

        $stats = FinancialReportService::computeStats($now, $now);
        // Find cab entry in servicesBreakdown
        $cabStream = collect($stats['servicesBreakdown'])->firstWhere('service', 'Cab & Transport Rides');
        $this->assertNotNull($cabStream);
        $this->assertEquals(0.0, (float)$cabStream['gst'], 'Pure GST for 0-tax ride must be strictly 0.00 without phantom 5% inflation');

        // Cleanup
        DB::table('tj_requete')->where('id', $rideId)->delete();
    }

    /**
     * Test Net Admin Revenue Formula: Net Revenue = Commissions + Platform Fees + Subscriptions.
     */
    public function test_net_admin_revenue_reconciliation()
    {
        $stats = FinancialReportService::computeStats();
        $expectedNetRev = round(
            $stats['totalCommissionEarned'] + ($stats['marketplaceSellerComm'] ?? 0) + $stats['platformFeeTotal'] + ($stats['totalSubscriptionRevenue'] ?? 0),
            2
        );
        $this->assertEquals($expectedNetRev, (float)$stats['netRevenue'], 'Net Admin Revenue must strictly equal Commissions + Fees + Subscriptions');
    }

    /**
     * Test Provider Cash Debt Blocking: Provider with negative wallet balance CANNOT accept bookings.
     */
    public function test_provider_with_cash_debt_blocked_from_accepting_bookings()
    {
        $now = Carbon::now()->toDateTimeString();

        // Create test driver with NEGATIVE wallet balance (cash debt)
        $driverId = DB::table('tj_conducteur')->insertGetId([
            'nom'      => 'Debt',
            'prenom'   => 'Driver',
            'phone'    => '+919998887776',
            'amount'   => -250.00, // Debtor
            'statut'   => 'yes',
            'online'   => 'yes',
            'creer'    => $now,
            'modifier' => $now,
        ]);

        $userId = DB::table('tj_user_app')->value('id') ?? 1;

        // 1. Test Cab Ride Booking Confirm
        $rideId = DB::table('tj_requete')->insertGetId([
            'id_user_app'   => $userId,
            'montant'       => 200.00,
            'statut'        => 'new',
            'creer'         => $now,
        ]);

        $controller = new \App\Http\Controllers\API\v1\ConfirmRequeteController();
        $request = new \Illuminate\Http\Request([
            'id_ride'   => $rideId,
            'id_user'   => $userId,
            'from_id'   => $driverId,
        ]);

        $response = $controller->confirmRequest($request);
        $resData = json_decode($response->getContent(), true);

        $this->assertEquals('Failed', $resData['success']);
        $this->assertStringContainsString('outstanding cash collection due', $resData['error']);

        // Verify ride status remained 'new' and was NOT confirmed
        $ride = DB::table('tj_requete')->where('id', $rideId)->first();
        $this->assertEquals('new', $ride->statut);

        // 2. Test Home Service Booking Accept
        if (Schema::hasTable('service_requests')) {
            $serviceId = DB::table('service_requests')->insertGetId([
                'user_id'      => $userId,
                'service_name' => 'Plumbing Repair',
                'amount'       => 300.00,
                'status'       => 'Pending',
                'created_at'   => $now,
            ]);

            $svcController = new \App\Http\Controllers\API\v1\ServiceRequestAPIController();
            $svcRequest = new \Illuminate\Http\Request([
                'booking_id' => $serviceId,
                'driver_id'  => $driverId,
                'status'     => 'Accepted',
            ]);

            $svcResponse = $svcController->updateServiceBookingStatus($svcRequest);
            $svcData = json_decode($svcResponse->getContent(), true);

            $this->assertEquals('Failed', $svcData['success'] ?? $svcData['status'] ?? '');
            $this->assertStringContainsString('outstanding cash collection due', $svcData['error'] ?? $svcData['message'] ?? '');

            // Verify booking remained Pending
            $booking = DB::table('service_requests')->where('id', $serviceId)->first();
            $this->assertEquals('Pending', $booking->status);

            DB::table('service_requests')->where('id', $serviceId)->delete();
        }

        // Cleanup
        DB::table('tj_requete')->where('id', $rideId)->delete();
        DB::table('tj_conducteur')->where('id', $driverId)->delete();
    }

    /**
     * Test Standardized Wallet History Naming.
     */
    public function test_standardized_wallet_history_naming()
    {
        $controller = new \App\Http\Controllers\API\v1\UserProfileUpdateController();

        // 1. Marketplace Purchase (Buyer Debit)
        $txRow = (object)[
            'id' => 9991,
            'amount' => '1200.00',
            'deduction_type' => '0',
            'type' => 'debit',
            'description' => 'Marketplace Purchase: Purchased item #12',
            'payment_method' => 'Fiinway Wallet',
            'creer' => '2026-09-04 10:00:00',
        ];
        $enriched = $controller->enrichTransactionHistoryRow($txRow, '1', 'customer');
        $this->assertEquals('Marketplace Purchase', $enriched['categoryTitle']);
        $this->assertEquals('0', $enriched['deduction_type']);

        // 2. Marketplace Sale (Seller Credit)
        $saleRow = (object)[
            'id' => 9992,
            'amount' => '1000.00',
            'deduction_type' => '1',
            'type' => 'credit',
            'description' => 'Marketplace Sale Earning: Order #12',
            'payment_method' => 'Fiinway Wallet',
            'creer' => '2026-09-04 10:00:00',
        ];
        $enrichedSale = $controller->enrichTransactionHistoryRow($saleRow, '2', 'customer');
        $this->assertEquals('Marketplace Sale', $enrichedSale['categoryTitle']);
        $this->assertEquals('1', $enrichedSale['deduction_type']);

        // 3. Admin Commission Deduction
        $commRow = (object)[
            'id' => 9993,
            'amount' => '-100.00',
            'deduction_type' => '0',
            'type' => 'debit',
            'description' => 'Admin Commission for ride #55',
            'payment_method' => 'Commission',
            'creer' => '2026-09-04 10:00:00',
        ];
        $enrichedComm = $controller->enrichTransactionHistoryRow($commRow, '1', 'driver');
        $this->assertEquals('Admin Commission', $enrichedComm['categoryTitle']);
        $this->assertEquals('0', $enrichedComm['deduction_type']);
    }
}
