<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\FinancialReportService;

class EarningController extends Controller
{
    private function parseDateRange(Request $request)
    {
        return FinancialReportService::parseDateRange(
            $request->get('date_range', 'this_month'),
            $request->get('start_date'),
            $request->get('end_date')
        );
    }

    private function computeAllSectionStats($startDate, $endDate)
    {
        return FinancialReportService::computeStats($startDate, $endDate);
    }

    /**
     * Master All-In-One Earning Overview Dashboard (10 Sections)
     */
    public function index(Request $request)
    {
        [$startDate, $endDate, $dateRange] = $this->parseDateRange($request);
        $stats = $this->computeAllSectionStats($startDate, $endDate);

        return view('earnings.index', compact('stats', 'dateRange', 'startDate', 'endDate'));
    }

    /**
     * API Endpoint for dynamic stats updates
     */
    public function getApiStats(Request $request)
    {
        [$startDate, $endDate, $dateRange] = $this->parseDateRange($request);
        $stats = $this->computeAllSectionStats($startDate, $endDate);

        return response()->json([
            'success'    => true,
            'date_range' => $dateRange,
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'data'       => $stats,
        ]);
    }

    /**
     * Stream CSV helper
     */
    private function csvResponse(string $filename, callable $callback)
    {
        return response()->stream($callback, 200, [
            'Content-type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$filename}",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ]);
    }

    /**
     * Section 1 & Full Report Export
     */
    public function exportReport(Request $request)
    {
        [$startDate, $endDate, $dateRange] = $this->parseDateRange($request);
        $stats = $this->computeAllSectionStats($startDate, $endDate);
        $filename = "fiinway_all_in_one_10_sections_report_" . date('Ymd_His') . ".csv";

        return $this->csvResponse($filename, function () use ($stats, $startDate, $endDate, $dateRange) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, ['FIINWAY ALL-IN-ONE 10-SECTION FINANCIAL INTELLIGENCE REPORT']);
            fputcsv($file, ['Date Range Filter:', $dateRange, 'From:', $startDate, 'To:', $endDate]);
            fputcsv($file, ['Generated At:', date('Y-m-d H:i:s')]);
            fputcsv($file, []);

            // 1. Revenue Dashboard
            fputcsv($file, ['=== 1. REVENUE DASHBOARD ===']);
            fputcsv($file, ['Today Gross Collection', 'INR ' . number_format($stats['revToday'], 2)]);
            fputcsv($file, ['This Week Gross Collection', 'INR ' . number_format($stats['revWeek'], 2)]);
            fputcsv($file, ['This Month Gross Collection', 'INR ' . number_format($stats['revMonth'], 2)]);
            fputcsv($file, ['This Year Gross Collection', 'INR ' . number_format($stats['revYear'], 2)]);
            fputcsv($file, ['Gross GMV (Filtered Period)', 'INR ' . number_format($stats['grossRevenue'], 2)]);
            fputcsv($file, ['- Online Realized Volume', 'INR ' . number_format($stats['onlineGrossVolume'] ?? 0, 2)]);
            fputcsv($file, ['- Cash Collected by Providers', 'INR ' . number_format($stats['cashGrossVolume'] ?? 0, 2)]);
            fputcsv($file, ['Net Admin Revenue (Commissions + Platform Fees)', 'INR ' . number_format($stats['netRevenue'], 2)]);
            fputcsv($file, ['- Realized Admin Revenue', 'INR ' . number_format($stats['realizedAdminRevenue'] ?? $stats['netRevenue'], 2)]);
            fputcsv($file, ['- Pending Due from Cash Bookings', 'INR ' . number_format($stats['dueAdminRevenue'] ?? 0, 2)]);
            fputcsv($file, ['Platform Fees Earned (Admin Revenue Source #2)', 'INR ' . number_format($stats['platformFeeTotal'] ?? 0, 2)]);
            fputcsv($file, ['GST Tax Collected (Govt Liability • Not Revenue)', 'INR ' . number_format($stats['gstCollectedTotal'] ?? 0, 2)]);
            fputcsv($file, ['Outstanding Provider Cash Debt (Recovery Queue)', 'INR ' . number_format($stats['pendingDriverDebt'] ?? 0, 2)]);
            fputcsv($file, ['Total Transactions Count', $stats['totalTransactions']]);
            fputcsv($file, []);

            // 2. Service Commission
            fputcsv($file, ['=== 2. SERVICE COMMISSION BREAKDOWN ===']);
            fputcsv($file, ['Service Name', 'Rate', 'Total Bookings', 'Gross Sales (INR)', 'Admin Commission Earned (INR)']);
            foreach ($stats['servicesBreakdown'] as $sb) {
                fputcsv($file, [$sb['service'], $sb['rate'], $sb['bookings'], number_format($sb['gross'], 2), number_format($sb['commission'], 2)]);
            }
            fputcsv($file, ['Total Service Commission Earned', '', '', '', 'INR ' . number_format($stats['totalCommissionEarned'], 2)]);
            fputcsv($file, []);

            // 3. Marketplace Commission
            fputcsv($file, ['=== 3. MARKETPLACE COMMISSION ===']);
            fputcsv($file, ['Total Marketplace Product Sales (GMV)', 'INR ' . number_format($stats['marketplaceProductSales'], 2)]);
            fputcsv($file, ['Seller Platform Commission (10%)', 'INR ' . number_format($stats['marketplaceSellerComm'], 2)]);
            fputcsv($file, []);

            // 4. Premium Plan Revenue
            fputcsv($file, ['=== 4. PREMIUM PLAN REVENUE ===']);
            fputcsv($file, ['Consumer Active Plans Count', $stats['consumerPlanCount']]);
            fputcsv($file, ['Business Active Plans Count', $stats['businessPlanCount']]);
            fputcsv($file, ['Total Subscription Revenue', 'INR ' . number_format($stats['totalSubscriptionRevenue'], 2)]);
            fputcsv($file, []);

            // 5. Referral Cost & Revenue
            fputcsv($file, ['=== 5. REFERRAL COST & REVENUE ===']);
            fputcsv($file, ['Total Referral Links Generated', $stats['referralCount']]);
            fputcsv($file, ['Referral Rewards Distributed (Cost)', 'INR ' . number_format($stats['referralRewardsPaid'], 2)]);
            fputcsv($file, ['Revenue Generated by Referred Users', 'INR ' . number_format($stats['revenueByReferredUsers'], 2)]);
            fputcsv($file, ['Net Referral Contribution', 'INR ' . number_format($stats['netReferralContribution'], 2)]);
            fputcsv($file, []);

            // 6. Payment & Transaction Revenue
            fputcsv($file, ['=== 6. PAYMENT & TRANSACTION REVENUE ===']);
            fputcsv($file, ['Total Payment Volume', 'INR ' . number_format($stats['totalPaymentVolume'], 2)]);
            fputcsv($file, ['Gateway Charges (Est. 2%)', 'INR ' . number_format($stats['gatewayCharges'], 2)]);
            fputcsv($file, ['Platform Charges Collected', 'INR ' . number_format($stats['platformCharges'], 2)]);
            fputcsv($file, ['Company Share', 'INR ' . number_format($stats['companyShare'], 2)]);
            fputcsv($file, ['Failed / Refunded Transactions Amount', 'INR ' . number_format($stats['failedTxnsAmount'], 2), 'Count: ' . $stats['failedTxnsCount']]);
            fputcsv($file, []);

            // 7. Cashback & Discount Cost
            fputcsv($file, ['=== 7. CASHBACK & DISCOUNT COST ===']);
            fputcsv($file, ['Cashback Given', 'INR ' . number_format($stats['cashbackGiven'], 2)]);
            fputcsv($file, ['Discounts Given', 'INR ' . number_format($stats['discountsGiven'], 2)]);
            fputcsv($file, ['Referral Rewards Paid', 'INR ' . number_format($stats['referralRewardsPaid'], 2)]);
            fputcsv($file, ['Medical Cashback Approved', 'INR ' . number_format($stats['medicalCashbackGiven'], 2)]);
            fputcsv($file, ['Total Promotional Cost (Admin Burn)', 'INR ' . number_format($stats['totalPromotionalCost'], 2)]);
            fputcsv($file, []);

            // 8. Settlement & Provider Payout
            fputcsv($file, ['=== 8. SETTLEMENT & PROVIDER PAYOUT ===']);
            fputcsv($file, ['Business Collection', 'INR ' . number_format($stats['businessCollection'], 2)]);
            fputcsv($file, ['Company Commission', 'INR ' . number_format($stats['companyCommission'], 2)]);
            fputcsv($file, ['Provider Payable Amount', 'INR ' . number_format($stats['providerPayable'], 2)]);
            fputcsv($file, ['Paid / Settled Amount', 'INR ' . number_format($stats['paidSettlement'], 2)]);
            fputcsv($file, ['Pending Settlement Amount', 'INR ' . number_format($stats['pendingSettlement'], 2)]);
            fputcsv($file, []);

            // 9. Profit & Loss Statement
            fputcsv($file, ['=== 9. PROFIT & LOSS STATEMENT ===']);
            fputcsv($file, ['Gross Revenue', 'INR ' . number_format($stats['totalRevenuePnl'], 2)]);
            fputcsv($file, ['Less: Provider Payout', '- INR ' . number_format($stats['providerPayable'], 2)]);
            fputcsv($file, ['Less: Gateway Charges', '- INR ' . number_format($stats['gatewayCharges'], 2)]);
            fputcsv($file, ['Less: Cashback & Bonuses Given', '- INR ' . number_format($stats['cashbackGiven'], 2)]);
            fputcsv($file, ['Less: Referral Rewards Paid', '- INR ' . number_format($stats['referralRewardsPaid'], 2)]);
            fputcsv($file, ['Less: Refunds / Failed', '- INR ' . number_format($stats['refundsPnl'], 2)]);
            fputcsv($file, ['NET ADMIN PROFIT', 'INR ' . number_format($stats['netProfitPnl'], 2)]);
            fputcsv($file, ['Profit Margin %', $stats['profitMarginPnl'] . '%']);

            fclose($file);
        });
    }

    /** Export — Service Commission */
    public function exportServices(Request $request)
    {
        [$startDate, $endDate] = $this->parseDateRange($request);
        $stats = $this->computeAllSectionStats($startDate, $endDate);
        $filename = "service_commission_" . date('Ymd_His') . ".csv";

        return $this->csvResponse($filename, function () use ($stats, $startDate, $endDate) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, ['FIINWAY — Service Commission Breakdown']);
            fputcsv($file, ['Date Range', $startDate . ' to ' . $endDate]);
            fputcsv($file, ['Generated At', date('Y-m-d H:i:s')]);
            fputcsv($file, []);
            fputcsv($file, ['Service Category', 'Commission Rate', 'Bookings Count', 'Gross Sales (INR)', 'Admin Commission Earned (INR)']);
            foreach ($stats['servicesBreakdown'] as $sb) {
                fputcsv($file, [$sb['service'], $sb['rate'], $sb['bookings'], number_format($sb['gross'], 2), number_format($sb['commission'], 2)]);
            }
            fclose($file);
        });
    }

    /** Export — Marketplace Commission */
    public function exportMarketplace(Request $request)
    {
        [$startDate, $endDate] = $this->parseDateRange($request);
        $stats = $this->computeAllSectionStats($startDate, $endDate);
        $filename = "marketplace_commission_" . date('Ymd_His') . ".csv";

        return $this->csvResponse($filename, function () use ($stats, $startDate, $endDate) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, ['FIINWAY — Marketplace Commission Report']);
            fputcsv($file, ['Date Range', $startDate . ' to ' . $endDate]);
            fputcsv($file, ['Generated At', date('Y-m-d H:i:s')]);
            fputcsv($file, []);
            fputcsv($file, ['Category Name', 'Items Sold', 'Gross Sales (INR)', 'Platform Commission (INR)']);
            foreach ($stats['categoryEarnings'] as $ce) {
                fputcsv($file, [$ce->category, $ce->sales_count, number_format($ce->gross_sales, 2), number_format($ce->commission, 2)]);
            }
            fclose($file);
        });
    }

    /** Export — Premium Plans */
    public function exportPremium(Request $request)
    {
        [$startDate, $endDate] = $this->parseDateRange($request);
        $stats = $this->computeAllSectionStats($startDate, $endDate);
        $filename = "premium_plans_revenue_" . date('Ymd_His') . ".csv";

        return $this->csvResponse($filename, function () use ($stats, $startDate, $endDate) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, ['FIINWAY — Premium Plan Revenue Report']);
            fputcsv($file, ['Date Range', $startDate . ' to ' . $endDate]);
            fputcsv($file, ['Generated At', date('Y-m-d H:i:s')]);
            fputcsv($file, []);
            fputcsv($file, ['#', 'Subscriber Name', 'Phone', 'Plan Title', 'Amount (INR)', 'Date']);
            foreach ($stats['subHistoryList'] as $i => $s) {
                fputcsv($file, [$i + 1, $s->subscriber_name ?: 'User #' . $s->user_id, $s->phone ?: 'N/A', $s->plan_title, number_format($s->price, 2), $s->created_at]);
            }
            fclose($file);
        });
    }

    /** Export — Wallet Recharge */
    public function exportWallet(Request $request)
    {
        [$startDate, $endDate] = $this->parseDateRange($request);
        $filename = "wallet_recharges_" . date('Ymd_His') . ".csv";

        return $this->csvResponse($filename, function () use ($startDate, $endDate) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, ['FIINWAY — Wallet Recharge Report']);
            fputcsv($file, ['Date Range', $startDate . ' to ' . $endDate]);
            fputcsv($file, ['Generated At', date('Y-m-d H:i:s')]);
            fputcsv($file, []);
            fputcsv($file, ['#TXN', 'User', 'Phone', 'Amount (INR)', 'Payment Method', 'Status', 'Date']);

            $rows = DB::table('tj_transaction as t')
                ->leftJoin('tj_user_app as u', 't.id_user_app', '=', 'u.id')
                ->select('t.*', DB::raw("TRIM(CONCAT(COALESCE(u.prenom,''),' ',COALESCE(u.nom,''))) as user_name"), 'u.phone')
                ->whereBetween('t.creer', [$startDate, $endDate])
                ->orderBy('t.id', 'desc')
                ->get();

            foreach ($rows as $i => $r) {
                fputcsv($file, [$r->id, $r->user_name ?: 'User #' . $r->id_user_app, $r->phone ?: 'N/A', number_format((float)$r->amount, 2), $r->payment_method ?: 'N/A', $r->payment_status ?: 'N/A', $r->creer ?: 'N/A']);
            }
            fclose($file);
        });
    }

    /** Export — Company Earnings */
    public function exportCompany(Request $request)
    {
        [$startDate, $endDate] = $this->parseDateRange($request);
        $stats = $this->computeAllSectionStats($startDate, $endDate);
        $filename = "company_earnings_" . date('Ymd_His') . ".csv";

        return $this->csvResponse($filename, function () use ($stats, $startDate, $endDate) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, ['FIINWAY — Company Earnings & Commissions Summary']);
            fputcsv($file, ['Date Range', $startDate . ' to ' . $endDate]);
            fputcsv($file, ['Generated At', date('Y-m-d H:i:s')]);
            fputcsv($file, []);
            fputcsv($file, ['Category', 'Gross Volume (INR)', 'Admin Commission / Fee (INR)']);
            fputcsv($file, ['Ride Commissions', '-', number_format($stats['netRevenue'] - $stats['totalSubscriptionRevenue'] - $stats['platformCharges'], 2)]);
            fputcsv($file, ['Service Commissions & Platform Fee', '-', number_format($stats['totalCommissionEarned'], 2)]);
            fputcsv($file, ['Marketplace Seller Commission (10%)', number_format($stats['marketplaceProductSales'], 2), number_format($stats['marketplaceSellerComm'], 2)]);
            fputcsv($file, ['Subscriptions Revenue', number_format($stats['totalSubscriptionRevenue'], 2), number_format($stats['totalSubscriptionRevenue'], 2)]);
            fputcsv($file, ['Total Gross Admin Earnings', '', 'INR ' . number_format($stats['netRevenue'], 2)]);
            fclose($file);
        });
    }

    /** Export — Referral */
    public function exportReferral(Request $request)
    {
        [$startDate, $endDate] = $this->parseDateRange($request);
        $filename = "referral_report_" . date('Ymd_His') . ".csv";

        return $this->csvResponse($filename, function () use ($startDate, $endDate) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, ['FIINWAY — Referral Report']);
            fputcsv($file, ['Date Range', $startDate . ' to ' . $endDate]);
            fputcsv($file, ['Generated At', date('Y-m-d H:i:s')]);
            fputcsv($file, []);
            fputcsv($file, ['#', 'User Name', 'Phone', 'User Type', 'Referral Code', 'Referred By ID', 'Joined Date']);

            $rows = DB::table('referral as r')
                ->leftJoin('tj_user_app as u', function ($j) {
                    $j->on('r.user_id', '=', 'u.id')->where('r.user_type', '!=', 'driver');
                })
                ->leftJoin('tj_conducteur as d', function ($j) {
                    $j->on('r.user_id', '=', 'd.id')->where('r.user_type', '=', 'driver');
                })
                ->select(
                    'r.id', 'r.user_id', 'r.user_type', 'r.referral_code', 'r.referral_by_id', 'r.creer',
                    DB::raw("COALESCE(TRIM(CONCAT(COALESCE(u.prenom,''),' ',COALESCE(u.nom,''))), TRIM(CONCAT(COALESCE(d.prenom,''),' ',COALESCE(d.nom,''))), 'N/A') as user_name"),
                    DB::raw("COALESCE(u.phone, d.phone) as phone")
                )
                ->whereBetween('r.creer', [$startDate, $endDate])
                ->orderBy('r.id', 'desc')
                ->get();

            foreach ($rows as $i => $r) {
                fputcsv($file, [$i + 1, $r->user_name ?: 'User #' . $r->user_id, $r->phone ?: 'N/A', ucfirst($r->user_type ?: 'customer'), $r->referral_code ?: 'N/A', $r->referral_by_id ?: 'None', $r->creer]);
            }
            fclose($file);
        });
    }

    /** Export — Payments */
    public function exportPayments(Request $request)
    {
        [$startDate, $endDate] = $this->parseDateRange($request);
        $stats = $this->computeAllSectionStats($startDate, $endDate);
        $filename = "payments_transactions_" . date('Ymd_His') . ".csv";

        return $this->csvResponse($filename, function () use ($startDate, $endDate) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, ['FIINWAY — Payment & Transactions Report']);
            fputcsv($file, ['Date Range', $startDate . ' to ' . $endDate]);
            fputcsv($file, ['Generated At', date('Y-m-d H:i:s')]);
            fputcsv($file, []);
            fputcsv($file, ['#TXN', 'User', 'Phone', 'Amount (INR)', 'Method', 'Status', 'Date']);

            $rows = DB::table('tj_transaction as t')
                ->leftJoin('tj_user_app as u', 't.id_user_app', '=', 'u.id')
                ->select('t.*', DB::raw("TRIM(CONCAT(COALESCE(u.prenom,''),' ',COALESCE(u.nom,''))) as user_name"), 'u.phone')
                ->whereBetween('t.creer', [$startDate, $endDate])
                ->orderBy('t.id', 'desc')
                ->get();

            foreach ($rows as $i => $r) {
                fputcsv($file, [$r->id, $r->user_name ?: 'User #' . $r->id_user_app, $r->phone ?: 'N/A', number_format((float)$r->amount, 2), $r->payment_method ?: 'N/A', $r->payment_status ?: 'N/A', $r->creer ?: 'N/A']);
            }
            fclose($file);
        });
    }

    /** Export — Settlements */
    public function exportSettlement(Request $request)
    {
        [$startDate, $endDate] = $this->parseDateRange($request);
        $filename = "business_settlement_" . date('Ymd_His') . ".csv";

        return $this->csvResponse($filename, function () use ($startDate, $endDate) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, ['FIINWAY — Business Settlement & Payout Report']);
            fputcsv($file, ['Date Range', $startDate . ' to ' . $endDate]);
            fputcsv($file, ['Generated At', date('Y-m-d H:i:s')]);
            fputcsv($file, []);
            fputcsv($file, ['#', 'Provider Name', 'Phone', 'Email', 'Amount (INR)', 'Status', 'Date']);

            if (Schema::hasTable('withdrawals')) {
                $rows = DB::table('withdrawals as w')
                    ->leftJoin('tj_conducteur as d', 'w.id_conducteur', '=', 'd.id')
                    ->select('w.id', 'w.amount', 'w.statut', 'w.creer', DB::raw("TRIM(CONCAT(COALESCE(d.prenom,''),' ',COALESCE(d.nom,''))) as provider_name"), 'd.phone', 'd.email')
                    ->orderBy('w.id', 'desc')
                    ->get();

                foreach ($rows as $i => $r) {
                    fputcsv($file, [$i + 1, $r->provider_name ?: 'Driver #' . $r->id_conducteur, $r->phone ?: 'N/A', $r->email ?: 'N/A', number_format((float)$r->amount, 2), $r->statut == 1 ? 'Settled / Paid' : 'Pending Approval', $r->creer ?: 'N/A']);
                }
            }
            fclose($file);
        });
    }

    /** Export — Profit & Loss */
    public function exportProfitLoss(Request $request)
    {
        [$startDate, $endDate] = $this->parseDateRange($request);
        $stats = $this->computeAllSectionStats($startDate, $endDate);
        $filename = "profit_and_loss_" . date('Ymd_His') . ".csv";

        return $this->csvResponse($filename, function () use ($stats, $startDate, $endDate) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, ['FIINWAY — Accurate Profit & Loss Statement']);
            fputcsv($file, ['Date Range', $startDate . ' to ' . $endDate]);
            fputcsv($file, ['Generated At', date('Y-m-d H:i:s')]);
            fputcsv($file, []);
            fputcsv($file, ['Financial Line Item', 'Amount (INR)']);
            fputcsv($file, ['Total Gross Revenue', number_format($stats['totalRevenuePnl'], 2)]);
            fputcsv($file, ['Less: Provider Payouts', '-' . number_format($stats['providerPayable'], 2)]);
            fputcsv($file, ['Less: Gateway Charges (2%)', '-' . number_format($stats['gatewayCharges'], 2)]);
            fputcsv($file, ['Less: Cashback & Wallet Bonuses Given', '-' . number_format($stats['cashbackGiven'], 2)]);
            fputcsv($file, ['Less: Referral Rewards Paid', '-' . number_format($stats['referralRewardsPaid'], 2)]);
            fputcsv($file, ['Less: Medical Cashback Claims Approved', '-' . number_format($stats['medicalCashbackGiven'], 2)]);
            fputcsv($file, ['Less: Refunds / Failed Transactions', '-' . number_format($stats['refundsPnl'], 2)]);
            fputcsv($file, ['NET ADMIN PROFIT', number_format($stats['netProfitPnl'], 2)]);
            fputcsv($file, ['Net Profit Margin %', $stats['profitMarginPnl'] . '%']);
            fclose($file);
        });
    }

    /** Export — Daily Earning Reports */
    public function exportDailyReports(Request $request)
    {
        [$startDate, $endDate] = $this->parseDateRange($request);
        $stats = $this->computeAllSectionStats($startDate, $endDate);
        $filename = "daily_earning_reports_" . date('Ymd_His') . ".csv";

        return $this->csvResponse($filename, function () use ($stats, $startDate, $endDate) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, ['FIINWAY — Daily Earning Breakdown Report']);
            fputcsv($file, ['Date Range', $startDate . ' to ' . $endDate]);
            fputcsv($file, ['Generated At', date('Y-m-d H:i:s')]);
            fputcsv($file, []);
            fputcsv($file, ['Date', 'Total Bookings/Rides', 'Gross Amount (INR)', 'Admin Commission (INR)']);
            foreach ($stats['dailyReports'] as $d) {
                fputcsv($file, [$d->date, $d->total_rides, number_format((float)$d->gross_amount, 2), number_format((float)$d->commission, 2)]);
            }
            fclose($file);
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Sub-route Redirections to section anchors on Master Page
    // ──────────────────────────────────────────────────────────────────────────
    public function revenueDashboard()      { return redirect()->route('earnings.index', ['#section-1-revenue']); }
    public function serviceCommission()     { return redirect()->route('earnings.index', ['#section-2-services']); }
    public function marketplaceCommission() { return redirect()->route('earnings.index', ['#section-3-marketplace']); }
    public function premiumPlans()          { return redirect()->route('earnings.index', ['#section-4-premium']); }
    public function referralCostRevenue()   { return redirect()->route('earnings.index', ['#section-5-referral']); }
    public function paymentTransactions()   { return redirect()->route('earnings.index', ['#section-6-payments']); }
    public function cashbackDiscounts()     { return redirect()->route('earnings.index', ['#section-7-cashback']); }
    public function settlementsPayouts()    { return redirect()->route('earnings.index', ['#section-8-settlement']); }
    public function profitLoss()            { return redirect()->route('earnings.index', ['#section-9-pnl']); }
    public function earningReports()        { return redirect()->route('earnings.index', ['#section-10-reports']); }

    public function resetTestData(Request $request)
    {
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            $tablesToTruncate = [
                'tj_user_app',
                'tj_conducteur',
                'tj_vehicule',
                'tj_transaction',
                'tj_conducteur_transaction',
                'tj_requete',
                'service_requests',
                'marketplace_order_items',
                'marketplace_orders',
                'subscription_history',
                'referral',
                'withdrawals',
                'tj_medical_claims',
                'tj_medical_cards',
                'parcel_orders',
                'tj_note',
                'tj_sos'
            ];

            foreach ($tablesToTruncate as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->truncate();
                }
            }
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'All test users, drivers, transactions and earnings reset to zero successfully.']);
            }
            return redirect()->route('earnings.index')->with('success', 'All test users, transactions and earnings have been reset to zero.');
        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return redirect()->route('earnings.index')->with('error', 'Reset failed: ' . $e->getMessage());
        }
    }

    public function seedSampleData(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Sample data seed endpoint active.']);
        }
        return redirect()->route('earnings.index')->with('info', 'Sample data seeding endpoint active.');
    }
}
