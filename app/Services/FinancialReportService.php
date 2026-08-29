<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class FinancialReportService
{
    /**
     * Parse date range string into start and end datetime strings.
     */
    public static function parseDateRange(?string $range = 'this_month', ?string $customStart = null, ?string $customEnd = null): array
    {
        $now = Carbon::now();

        switch ($range) {
            case 'today':
                $start = $now->copy()->startOfDay();
                $end   = $now->copy()->endOfDay();
                break;
            case 'this_week':
                $start = $now->copy()->startOfWeek();
                $end   = $now->copy()->endOfWeek();
                break;
            case 'this_year':
                $start = $now->copy()->startOfYear();
                $end   = $now->copy()->endOfYear();
                break;
            case 'all':
                $start = Carbon::create(2020, 1, 1)->startOfDay();
                $end   = $now->copy()->endOfDay();
                break;
            case 'custom':
                $start = !empty($customStart) ? Carbon::parse($customStart)->startOfDay() : $now->copy()->startOfMonth();
                $end   = !empty($customEnd) ? Carbon::parse($customEnd)->endOfDay() : $now->copy()->endOfDay();
                break;
            case 'this_month':
            default:
                $start = $now->copy()->startOfMonth();
                $end   = $now->copy()->endOfMonth();
                $range = 'this_month';
                break;
        }

        return [$start->toDateTimeString(), $end->toDateTimeString(), $range];
    }

    /**
     * Compute full ecosystem financial statistics with unified formulas.
     * EXCLUDES all cancelled / rejected / failed bookings & orders.
     */
    public static function computeStats(?string $startDate = null, ?string $endDate = null): array
    {
        if (!$startDate || !$endDate) {
            [$startDate, $endDate] = self::parseDateRange('this_month');
        }

        $startStr = $startDate;
        $endStr   = $endDate;

        // Cancelled / Invalid Status Exclusions
        $cancelledRideStatuses    = ['canceled', 'cancel', 'cancelled', 'rejected', 'reject', 'driver_rejected', 'user_canceled', 'user_cancelled', 'declined'];
        $cancelledServiceStatuses = ['cancelled', 'canceled', 'rejected', 'user_cancelled', 'driver_cancelled', 'failed', 'declined'];
        $cancelledMarketStatuses  = ['cancelled', 'canceled', 'refunded', 'failed'];

        // Helper scopes
        $validRide = function($q) use ($cancelledRideStatuses) {
            return $q->whereNotIn('statut', $cancelledRideStatuses);
        };

        $validService = function($q) use ($cancelledServiceStatuses) {
            return $q->whereNotIn('status', $cancelledServiceStatuses);
        };

        $validMarket = function($q) use ($cancelledMarketStatuses) {
            return $q->whereNotIn('status', $cancelledMarketStatuses);
        };

        // 1. Subscription Revenue helper
        $calcSubRevenue = function($query) {
            $total = 0.0;
            $subs = $query->orderBy('id')->get();
            foreach ($subs as $s) {
                if (!empty($s->subscription_plan)) {
                    $plan = is_string($s->subscription_plan) ? json_decode($s->subscription_plan, true) : (array)$s->subscription_plan;
                    if (!empty($plan['price']) && is_numeric($plan['price'])) {
                        $total += (float)$plan['price'];
                    }
                }
            }
            return $total;
        };

        // ── 1. REVENUE DASHBOARD (PERIODIC) ──────────────────────────────────
        $todayStart = Carbon::today()->startOfDay()->toDateTimeString();
        $todayEnd   = Carbon::today()->endOfDay()->toDateTimeString();
        $weekStart  = Carbon::now()->startOfWeek()->toDateTimeString();
        $monthStart = Carbon::now()->startOfMonth()->toDateTimeString();
        $yearStart  = Carbon::now()->startOfYear()->toDateTimeString();

        $hasRequete      = Schema::hasTable('tj_requete');
        $hasMarketOrders = Schema::hasTable('marketplace_orders');
        $hasServiceReq   = Schema::hasTable('service_requests');
        $hasSubHist      = Schema::hasTable('subscription_history');
        $hasCondTxn      = Schema::hasTable('tj_conducteur_transaction');
        $hasUserTxn      = Schema::hasTable('tj_transaction');

        $revToday = (float)($hasRequete ? $validRide(DB::table('tj_requete'))->whereBetween('creer', [$todayStart, $todayEnd])->sum('montant') : 0)
                  + (float)($hasMarketOrders ? $validMarket(DB::table('marketplace_orders'))->whereBetween('created_at', [$todayStart, $todayEnd])->sum('total_amount') : 0)
                  + (float)($hasServiceReq ? $validService(DB::table('service_requests'))->whereBetween('created_at', [$todayStart, $todayEnd])->sum('amount') : 0)
                  + ($hasSubHist ? $calcSubRevenue(DB::table('subscription_history')->whereBetween('created_at', [$todayStart, $todayEnd])) : 0);

        $revWeek  = (float)($hasRequete ? $validRide(DB::table('tj_requete'))->whereBetween('creer', [$weekStart, $endStr])->sum('montant') : 0)
                  + (float)($hasMarketOrders ? $validMarket(DB::table('marketplace_orders'))->whereBetween('created_at', [$weekStart, $endStr])->sum('total_amount') : 0)
                  + (float)($hasServiceReq ? $validService(DB::table('service_requests'))->whereBetween('created_at', [$weekStart, $endStr])->sum('amount') : 0)
                  + ($hasSubHist ? $calcSubRevenue(DB::table('subscription_history')->whereBetween('created_at', [$weekStart, $endStr])) : 0);

        $revMonth = (float)($hasRequete ? $validRide(DB::table('tj_requete'))->whereBetween('creer', [$monthStart, $endStr])->sum('montant') : 0)
                  + (float)($hasMarketOrders ? $validMarket(DB::table('marketplace_orders'))->whereBetween('created_at', [$monthStart, $endStr])->sum('total_amount') : 0)
                  + (float)($hasServiceReq ? $validService(DB::table('service_requests'))->whereBetween('created_at', [$monthStart, $endStr])->sum('amount') : 0)
                  + ($hasSubHist ? $calcSubRevenue(DB::table('subscription_history')->whereBetween('created_at', [$monthStart, $endStr])) : 0);

        $revYear  = (float)($hasRequete ? $validRide(DB::table('tj_requete'))->whereBetween('creer', [$yearStart, $endStr])->sum('montant') : 0)
                  + (float)($hasMarketOrders ? $validMarket(DB::table('marketplace_orders'))->whereBetween('created_at', [$yearStart, $endStr])->sum('total_amount') : 0)
                  + (float)($hasServiceReq ? $validService(DB::table('service_requests'))->whereBetween('created_at', [$yearStart, $endStr])->sum('amount') : 0)
                  + ($hasSubHist ? $calcSubRevenue(DB::table('subscription_history')->whereBetween('created_at', [$yearStart, $endStr])) : 0);

        // Include transaction table volume if ecosystem table sums are 0
        $txnVolToday = $hasUserTxn ? (float)DB::table('tj_transaction')->whereBetween('creer', [$todayStart, $todayEnd])->sum('amount') : 0;
        $txnVolWeek  = $hasUserTxn ? (float)DB::table('tj_transaction')->whereBetween('creer', [$weekStart, $endStr])->sum('amount') : 0;
        $txnVolMonth = $hasUserTxn ? (float)DB::table('tj_transaction')->whereBetween('creer', [$monthStart, $endStr])->sum('amount') : 0;
        $txnVolYear  = $hasUserTxn ? (float)DB::table('tj_transaction')->whereBetween('creer', [$yearStart, $endStr])->sum('amount') : 0;

        $revToday = max($revToday, $txnVolToday);
        $revWeek  = max($revWeek, $txnVolWeek);
        $revMonth = max($revMonth, $txnVolMonth);
        $revYear  = max($revYear, $txnVolYear);

        // ── 2. FILTERED PERIOD GROSS REVENUES, GST & PLATFORM FEES ─────────
        $rideGross    = (float)($hasRequete ? $validRide(DB::table('tj_requete'))->whereBetween('creer', [$startStr, $endStr])->sum('montant') : 0);
        $rideComm     = (float)($hasRequete ? $validRide(DB::table('tj_requete'))->whereBetween('creer', [$startStr, $endStr])->sum('admin_commission') : 0);
        $rideTxnCount = $hasRequete ? $validRide(DB::table('tj_requete'))->whereBetween('creer', [$startStr, $endStr])->count() : 0;

        $marketGross  = (float)($hasMarketOrders ? $validMarket(DB::table('marketplace_orders'))->whereBetween('created_at', [$startStr, $endStr])->sum('total_amount') : 0);
        $marketComm   = round($marketGross * 0.10, 2);
        $marketTxnCount = $hasMarketOrders ? $validMarket(DB::table('marketplace_orders'))->whereBetween('created_at', [$startStr, $endStr])->count() : 0;

        $serviceGross = 0.0;
        $serviceBaseGross = (float)($hasServiceReq ? $validService(DB::table('service_requests'))->whereBetween('created_at', [$startStr, $endStr])->sum('amount') : 0);
        $serviceComm = (float)($hasCondTxn ? DB::table('tj_conducteur_transaction')
            ->where(function($q) { $q->where('payment_method', 'Commission')->orWhere('deduction_type', 'Commission'); })
            ->whereBetween('creer', [$startStr, $endStr])
            ->sum(DB::raw('ABS(amount)')) : 0);
        if ($serviceComm == 0 && $serviceBaseGross > 0) {
            $serviceComm = round($serviceBaseGross * 0.10, 2);
        }
        $serviceTxnCount = $hasServiceReq ? $validService(DB::table('service_requests'))->whereBetween('created_at', [$startStr, $endStr])->count() : 0;

        // Accurate GST and Platform Fee extraction across all services
        $gstCollectedTotal  = 0.0;
        $gstCollectedOnline = 0.0;
        $gstCollectedCash   = 0.0;
        $platformFeeTotal   = 0.0;
        $platformFeeOnline  = 0.0;
        $platformFeeCash    = 0.0;
        $serviceOnlineGross = 0.0;
        $serviceCashGross   = 0.0;

        if ($hasServiceReq) {
            $selectCols = ['id', 'amount', 'payment_status'];
            if (Schema::hasColumn('service_requests', 'price_breakdown')) $selectCols[] = 'price_breakdown';
            if (Schema::hasColumn('service_requests', 'tax_amount')) $selectCols[] = 'tax_amount';
            if (Schema::hasColumn('service_requests', 'tax')) $selectCols[] = 'tax';

            $validService(DB::table('service_requests'))
                ->whereBetween('created_at', [$startStr, $endStr])
                ->select($selectCols)
                ->orderBy('id')
                ->chunk(200, function ($rows) use (&$gstCollectedTotal, &$gstCollectedOnline, &$gstCollectedCash, &$platformFeeTotal, &$platformFeeOnline, &$platformFeeCash, &$serviceGross, &$serviceOnlineGross, &$serviceCashGross) {
                    foreach ($rows as $row) {
                        $baseAmt = (float)($row->amount ?? 0);
                        $pb = (!empty($row->price_breakdown)) ? (is_string($row->price_breakdown) ? json_decode($row->price_breakdown, true) : (array)$row->price_breakdown) : [];
                        
                        $pFee = (float)($pb['platform_fee'] ?? 0);
                        
                        $taxAmt = 0.0;
                        if (isset($row->tax_amount) && is_numeric($row->tax_amount) && (float)$row->tax_amount > 0) {
                            $taxAmt = (float)$row->tax_amount;
                        } elseif (isset($pb['gst_amount']) && is_numeric($pb['gst_amount'])) {
                            $taxAmt = (float)$pb['gst_amount'];
                        } elseif (isset($pb['taxes']) && is_numeric($pb['taxes'])) {
                            $taxAmt = (float)$pb['taxes'];
                        } elseif (isset($row->tax) && is_numeric($row->tax)) {
                            $taxAmt = (float)$row->tax;
                        }

                        $totalBookingVal = $baseAmt + $pFee + $taxAmt;
                        $isCash = in_array(strtolower(trim((string)($row->payment_status ?? ''))), ['paid_cash', 'cash'], true);

                        $platformFeeTotal += $pFee;
                        $gstCollectedTotal += $taxAmt;
                        $serviceGross += $totalBookingVal;

                        if ($isCash) {
                            $platformFeeCash += $pFee;
                            $gstCollectedCash += $taxAmt;
                            $serviceCashGross += $totalBookingVal;
                        } else {
                            $platformFeeOnline += $pFee;
                            $gstCollectedOnline += $taxAmt;
                            $serviceOnlineGross += $totalBookingVal;
                        }
                    }
                });
        }
        if ($serviceGross == 0) {
            $serviceGross = $serviceBaseGross;
        }

        // Add ride GST
        if ($hasRequete) {
            $validRide(DB::table('tj_requete'))
                ->whereBetween('creer', [$startStr, $endStr])
                ->select('tax', 'statut_paiement', 'id_payment_method')
                ->orderBy('id')
                ->chunk(200, function ($rides) use (&$gstCollectedTotal, &$gstCollectedOnline) {
                    foreach ($rides as $r) {
                        if (!empty($r->tax)) {
                            $taxVal = 0.0;
                            if (is_numeric($r->tax)) {
                                $taxVal = (float)$r->tax;
                            } else {
                                $taxArr = is_string($r->tax) ? json_decode($r->tax, true) : (array)$r->tax;
                                if (is_array($taxArr)) {
                                    foreach ($taxArr as $tItem) {
                                        $taxVal += (float)($tItem['value'] ?? $tItem['amount'] ?? 0);
                                    }
                                }
                            }
                            $gstCollectedTotal += $taxVal;
                            $gstCollectedOnline += $taxVal;
                        }
                    }
                });
        }

        $subRevenue  = $hasSubHist ? $calcSubRevenue(DB::table('subscription_history')->whereBetween('created_at', [$startStr, $endStr])) : 0.0;
        $subTxnCount = $hasSubHist ? DB::table('subscription_history')->whereBetween('created_at', [$startStr, $endStr])->count() : 0;

        $userTxnVol   = $hasUserTxn ? (float)DB::table('tj_transaction')->whereBetween('creer', [$startStr, $endStr])->sum('amount') : 0;
        $userTxnCount = $hasUserTxn ? DB::table('tj_transaction')->whereBetween('creer', [$startStr, $endStr])->count() : 0;

        // Pending Recovery (Debt owed by Service Providers / Drivers from Cash bookings)
        $pendingDriverDebt = 0.0;
        $driversWithDebtCount = 0;
        $driversDebtList = collect();
        if (Schema::hasTable('tj_conducteur')) {
            $pendingDriverDebt = (float)DB::table('tj_conducteur')->where('amount', '<', 0)->sum(DB::raw('ABS(amount)'));
            $driversWithDebtCount = DB::table('tj_conducteur')->where('amount', '<', 0)->count();
            $driversDebtList = DB::table('tj_conducteur')
                ->where('amount', '<', 0)
                ->select('id', 'nom', 'prenom', 'phone', 'email', 'amount', 'ac_no', 'updated_at')
                ->orderBy('amount', 'asc')
                ->limit(10)
                ->get();
        }

        // Total Gross Ecosystem Revenue (GMV) - Total collections across user payments
        $grossRevenue = round(max($rideGross + $marketGross + $serviceGross + $subRevenue, $userTxnVol), 2);
        $onlineGrossVolume = round($rideGross + $marketGross + $serviceOnlineGross + $subRevenue, 2);
        $cashGrossVolume = round($serviceCashGross, 2);

        // Total Gross Admin Revenue (Commissions + Platform Fees + Subscriptions)
        // ❗ GST IS STRICTLY EXCLUDED FROM ADMIN REVENUE
        $netRevenue = round($rideComm + $marketComm + $serviceComm + $platformFeeTotal + $subRevenue, 2);
        $totalTransactions = max($rideTxnCount + $marketTxnCount + $serviceTxnCount + $subTxnCount, $userTxnCount);

        // Realized vs Pending Due Split
        $dueAdminRevenue = round(min($netRevenue, $pendingDriverDebt), 2);
        $realizedAdminRevenue = round(max(0, $netRevenue - $dueAdminRevenue), 2);

        // ── 3. SERVICE BREAKDOWN ─────────────────────────────────────────────
        $hasRideTypeCol  = $hasRequete && Schema::hasColumn('tj_requete', 'ride_type');
        $hasAdminCommCol = $hasRequete && Schema::hasColumn('tj_requete', 'admin_commission');

        $cabGross = (float)($hasRequete ? $validRide(DB::table('tj_requete'))->whereBetween('creer', [$startStr, $endStr])
            ->where(function($q) use ($hasRideTypeCol) {
                if ($hasRideTypeCol) {
                    $q->whereNull('ride_type')->orWhere('ride_type', 'cab')->orWhere('ride_type', '')->orWhere('ride_type', 'city');
                }
            })->sum('montant') : 0);

        $cabComm = (float)($hasAdminCommCol ? $validRide(DB::table('tj_requete'))->whereBetween('creer', [$startStr, $endStr])
            ->where(function($q) use ($hasRideTypeCol) {
                if ($hasRideTypeCol) {
                    $q->whereNull('ride_type')->orWhere('ride_type', 'cab')->orWhere('ride_type', '')->orWhere('ride_type', 'city');
                }
            })->sum('admin_commission') : 0);

        $cabBookings = $hasRequete ? $validRide(DB::table('tj_requete'))->whereBetween('creer', [$startStr, $endStr])
            ->where(function($q) use ($hasRideTypeCol) {
                if ($hasRideTypeCol) {
                    $q->whereNull('ride_type')->orWhere('ride_type', 'cab')->orWhere('ride_type', '')->orWhere('ride_type', 'city');
                }
            })->count() : 0;

        $homeGross = $serviceGross;
        $homeComm  = $serviceComm + $platformFeeTotal;
        $homeBookings = $serviceTxnCount;

        $foodGross = (float)($hasRideTypeCol ? $validRide(DB::table('tj_requete'))->whereBetween('creer', [$startStr, $endStr])->where('ride_type', 'food')->sum('montant') : 0);
        $foodComm  = (float)($hasRideTypeCol && $hasAdminCommCol ? $validRide(DB::table('tj_requete'))->whereBetween('creer', [$startStr, $endStr])->where('ride_type', 'food')->sum('admin_commission') : 0);
        $foodBookings = $hasRideTypeCol ? $validRide(DB::table('tj_requete'))->whereBetween('creer', [$startStr, $endStr])->where('ride_type', 'food')->count() : 0;

        $parcelGross = (float)($hasRideTypeCol ? $validRide(DB::table('tj_requete'))->whereBetween('creer', [$startStr, $endStr])->where('ride_type', 'parcel')->sum('montant') : 0);
        $parcelComm  = (float)($hasRideTypeCol && $hasAdminCommCol ? $validRide(DB::table('tj_requete'))->whereBetween('creer', [$startStr, $endStr])->where('ride_type', 'parcel')->sum('admin_commission') : 0);
        $parcelBookings = $hasRideTypeCol ? $validRide(DB::table('tj_requete'))->whereBetween('creer', [$startStr, $endStr])->where('ride_type', 'parcel')->count() : 0;

        $travelGross = (float)($hasRideTypeCol ? $validRide(DB::table('tj_requete'))->whereBetween('creer', [$startStr, $endStr])->where('ride_type', 'travel')->sum('montant') : 0);
        $travelComm  = (float)($hasRideTypeCol && $hasAdminCommCol ? $validRide(DB::table('tj_requete'))->whereBetween('creer', [$startStr, $endStr])->where('ride_type', 'travel')->sum('admin_commission') : 0);
        $travelBookings = $hasRideTypeCol ? $validRide(DB::table('tj_requete'))->whereBetween('creer', [$startStr, $endStr])->where('ride_type', 'travel')->count() : 0;

        $otherGross = (float)($hasRideTypeCol ? $validRide(DB::table('tj_requete'))->whereBetween('creer', [$startStr, $endStr])
            ->whereNotIn('ride_type', ['cab', 'city', 'food', 'parcel', 'travel'])->whereNotNull('ride_type')->where('ride_type', '!=', '')->sum('montant') : 0);
        $otherComm = (float)($hasRideTypeCol && $hasAdminCommCol ? $validRide(DB::table('tj_requete'))->whereBetween('creer', [$startStr, $endStr])
            ->whereNotIn('ride_type', ['cab', 'city', 'food', 'parcel', 'travel'])->whereNotNull('ride_type')->where('ride_type', '!=', '')->sum('admin_commission') : 0);
        $otherBookings = $hasRideTypeCol ? $validRide(DB::table('tj_requete'))->whereBetween('creer', [$startStr, $endStr])
            ->whereNotIn('ride_type', ['cab', 'city', 'food', 'parcel', 'travel'])->whereNotNull('ride_type')->where('ride_type', '!=', '')->count() : 0;

        $totalCommissionEarned = round($cabComm + $homeComm + $foodComm + $parcelComm + $travelComm + $otherComm, 2);

        $servicesBreakdown = [
            ['service' => 'Cab & Transport Rides', 'rate' => 'Dynamic %', 'bookings' => $cabBookings, 'gross' => $cabGross, 'commission' => $cabComm],
            ['service' => 'Home Services & Repairs', 'rate' => '10% + Platform Fee', 'bookings' => $homeBookings, 'gross' => $homeGross, 'commission' => $homeComm],
            ['service' => 'Food Delivery Orders', 'rate' => '18%', 'bookings' => $foodBookings, 'gross' => $foodGross, 'commission' => $foodComm],
            ['service' => 'Parcel & Courier', 'rate' => 'Flat / 10%', 'bookings' => $parcelBookings, 'gross' => $parcelGross, 'commission' => $parcelComm],
            ['service' => 'Travel & Outstation', 'rate' => '10%', 'bookings' => $travelBookings, 'gross' => $travelGross, 'commission' => $travelComm],
            ['service' => 'Other On-Demand Services', 'rate' => '10%', 'bookings' => $otherBookings, 'gross' => $otherGross, 'commission' => $otherComm],
        ];

        // ── 4. MARKETPLACE ───────────────────────────────────────────────────
        $categoryEarnings = collect();
        if (Schema::hasTable('marketplace_order_items') && Schema::hasTable('marketplace_products')) {
            $categoryEarnings = DB::table('marketplace_order_items')
                ->join('marketplace_products', 'marketplace_order_items.product_id', '=', 'marketplace_products.id')
                ->leftJoin('marketplace_categories', 'marketplace_products.category_id', '=', 'marketplace_categories.id')
                ->join('marketplace_orders', 'marketplace_order_items.order_id', '=', 'marketplace_orders.id')
                ->whereNotIn('marketplace_orders.status', $cancelledMarketStatuses)
                ->select(
                    DB::raw("COALESCE(marketplace_categories.name, 'General Catalog') as category"),
                    DB::raw("COUNT(marketplace_order_items.id) as sales_count"),
                    DB::raw("COALESCE(SUM(marketplace_order_items.price * marketplace_order_items.quantity), 0) as gross_sales")
                )
                ->groupBy('category')
                ->get()
                ->map(function($item) {
                    $item->commission = round($item->gross_sales * 0.10, 2);
                    return $item;
                });
        }

        $recentMarketplaceOrders = collect();
        if (Schema::hasTable('marketplace_orders')) {
            $recentMarketplaceOrders = $validMarket(DB::table('marketplace_orders as o'))
                ->leftJoin('tj_user_app as u', 'o.user_id', '=', 'u.id')
                ->select(
                    'o.id', 'o.total_amount', 'o.status', 'o.created_at',
                    DB::raw("TRIM(CONCAT(COALESCE(u.prenom,''),' ',COALESCE(u.nom,''))) as buyer_name"),
                    'u.phone'
                )
                ->whereBetween('o.created_at', [$startStr, $endStr])
                ->orderBy('o.id', 'desc')
                ->limit(10)
                ->get()
                ->map(function($order) {
                    $order->seller_commission = round((float)$order->total_amount * 0.10, 2);
                    return $order;
                });
        }

        // ── 5. PREMIUM PLANS ─────────────────────────────────────────────────
        $consumerPlanCount = DB::table('tj_user_app')->whereNotNull('consumer_plan_id')->where('consumer_plan_id', '>', 0)->count();
        $businessPlanCount = DB::table('tj_conducteur')->whereNotNull('subscriptionPlanId')->where('subscriptionPlanId', '>', 0)->count();

        $subHistoryList = collect();
        if (Schema::hasTable('subscription_history')) {
            $subHistoryList = DB::table('subscription_history as sh')
                ->leftJoin('tj_conducteur as d', 'sh.user_id', '=', 'd.id')
                ->leftJoin('tj_user_app as u', 'sh.user_id', '=', 'u.id')
                ->select(
                    'sh.id', 'sh.subscription_plan', 'sh.created_at', 'sh.user_id',
                    DB::raw("COALESCE(TRIM(CONCAT(COALESCE(d.prenom,''),' ',COALESCE(d.nom,''))), TRIM(CONCAT(COALESCE(u.prenom,''),' ',COALESCE(u.nom,''))), 'Subscriber') as subscriber_name"),
                    DB::raw("COALESCE(d.phone, u.phone, 'N/A') as phone")
                )
                ->whereBetween('sh.created_at', [$startStr, $endStr])
                ->orderBy('sh.id', 'desc')
                ->limit(10)
                ->get()
                ->map(function($sh) {
                    $plan = is_string($sh->subscription_plan) ? json_decode($sh->subscription_plan, true) : (array)$sh->subscription_plan;
                    $sh->plan_title = $plan['title'] ?? ($plan['name'] ?? 'Premium Subscription');
                    $sh->price = (float)($plan['price'] ?? 0);
                    return $sh;
                });
        }

        // ── 6. REFERRAL ──────────────────────────────────────────────────────
        $referralCount = DB::table('referral')->count();
        $referralRewardsPaid = (float)DB::table('tj_transaction')
            ->where(function($q) {
                $q->where('payment_method', 'like', '%Referral%')
                  ->orWhere('payment_method', 'like', '%Refer%')
                  ->orWhere('type', 'referral');
            })
            ->sum('amount');

        $referredUserIds = DB::table('referral')->where('user_type', '!=', 'driver')->pluck('user_id')->toArray();
        $revenueByReferredUsers = 0.0;
        if (!empty($referredUserIds)) {
            $revenueByReferredUsers = (float)($hasRequete ? $validRide(DB::table('tj_requete'))->whereIn('id_user_app', $referredUserIds)->sum('montant') : 0)
                                    + (float)($hasMarketOrders ? $validMarket(DB::table('marketplace_orders'))->whereIn('user_id', $referredUserIds)->sum('total_amount') : 0)
                                    + (float)($hasServiceReq ? $validService(DB::table('service_requests'))->whereIn('user_id', $referredUserIds)->sum('amount') : 0);
        }
        $netReferralContribution = round($revenueByReferredUsers - $referralRewardsPaid, 2);

        $referralList = DB::table('referral as r')
            ->leftJoin('tj_user_app as u', function ($j) {
                $j->on('r.user_id', '=', 'u.id')->where('r.user_type', '!=', 'driver');
            })
            ->leftJoin('tj_conducteur as d', function ($j) {
                $j->on('r.user_id', '=', 'd.id')->where('r.user_type', '=', 'driver');
            })
            ->select(
                'r.id', 'r.user_id', 'r.user_type', 'r.referral_code', 'r.creer',
                DB::raw("COALESCE(TRIM(CONCAT(COALESCE(u.prenom,''),' ',COALESCE(u.nom,''))), TRIM(CONCAT(COALESCE(d.prenom,''),' ',COALESCE(d.nom,''))), 'N/A') as user_name"),
                DB::raw("COALESCE(u.phone, d.phone) as phone")
            )
            ->orderBy('r.id', 'desc')
            ->limit(10)
            ->get();

        // ── 7. PAYMENTS & GATEWAY CHARGES ────────────────────────────────────
        $totalPaymentVolume = (float)DB::table('tj_transaction')
            ->where(function($q) { $q->where('payment_status', 'success')->orWhere('payment_status', 'Success'); })
            ->whereBetween('creer', [$startStr, $endStr])
            ->sum('amount');

        // External Payment Gateway Volume (UPI, Cards, Razorpay, Stripe, etc.)
        $externalGatewayVolume = (float)DB::table('tj_transaction')
            ->where(function($q) { $q->where('payment_status', 'success')->orWhere('payment_status', 'Success'); })
            ->whereBetween('creer', [$startStr, $endStr])
            ->where(function($q) {
                $q->where('payment_method', 'LIKE', '%Razorpay%')
                  ->orWhere('payment_method', 'LIKE', '%UPI%')
                  ->orWhere('payment_method', 'LIKE', '%Card%')
                  ->orWhere('payment_method', 'LIKE', '%Stripe%')
                  ->orWhere('payment_method', 'LIKE', '%Paytm%')
                  ->orWhere('payment_method', 'LIKE', '%PhonePe%')
                  ->orWhere('payment_method', 'LIKE', '%GPay%')
                  ->orWhere('payment_method', 'LIKE', '%GooglePay%')
                  ->orWhere('payment_method', 'LIKE', '%ApplePay%')
                  ->orWhere('payment_method', 'LIKE', '%Online%')
                  ->orWhere('payment_method', 'LIKE', '%NetBanking%')
                  ->orWhere('payment_method', 'LIKE', '%Payfast%')
                  ->orWhere('payment_method', 'LIKE', '%Paystack%')
                  ->orWhere('payment_method', 'LIKE', '%Flutterwave%')
                  ->orWhere('payment_method', 'LIKE', '%Mercadopago%')
                  ->orWhere('payment_method', 'LIKE', '%Xendit%')
                  ->orWhere('payment_method', 'LIKE', '%OrangePay%')
                  ->orWhere('payment_method', 'LIKE', '%Midtrans%');
            })
            ->where(function($q) {
                $q->where('payment_method', 'NOT LIKE', '%Wallet%')
                  ->where('payment_method', 'NOT LIKE', '%Referral%')
                  ->where('payment_method', 'NOT LIKE', '%Cashback%')
                  ->where('payment_method', 'NOT LIKE', '%Bonus%')
                  ->where('payment_method', 'NOT LIKE', '%COD%')
                  ->where('payment_method', 'NOT LIKE', '%Cash%');
            })
            ->sum('amount');

        $gatewayCharges   = round($externalGatewayVolume * 0.02, 2); // 2% External Gateway Fee ONLY
        $failedTxnsCount  = DB::table('tj_transaction')->whereIn('payment_status', ['Failed', 'failed', 'Cancelled', 'cancelled', 'Refunded', 'refunded'])->count();
        $failedTxnsAmount = (float)DB::table('tj_transaction')->whereIn('payment_status', ['Failed', 'failed', 'Cancelled', 'cancelled', 'Refunded', 'refunded'])->sum('amount');

        $recentTransactions = DB::table('tj_transaction as t')
            ->leftJoin('tj_user_app as u', 't.id_user_app', '=', 'u.id')
            ->select('t.*', DB::raw("TRIM(CONCAT(COALESCE(u.prenom,''),' ',COALESCE(u.nom,''))) as user_name"), 'u.phone')
            ->orderBy('t.id', 'desc')
            ->limit(10)
            ->get();

        // ── 8. CASHBACK & PROMOTIONAL BURNS ──────────────────────────────────
        $cashbackGiven = (float)DB::table('tj_transaction')
            ->whereIn('type', ['cashback', 'bonus'])
            ->whereBetween('creer', [$startStr, $endStr])
            ->sum('amount');

        $discountsGiven = (float)($hasRequete ? $validRide(DB::table('tj_requete'))->whereBetween('creer', [$startStr, $endStr])->sum('discount') : 0);
        $medicalCashbackGiven = (float) (Schema::hasTable('tj_medical_claims') ? DB::table('tj_medical_claims')->where('status', 'approved')->sum('approved_amount') : 0);

        $totalPromotionalCost = round($cashbackGiven + $discountsGiven + $referralRewardsPaid + $medicalCashbackGiven, 2);

        // ── 9. SETTLEMENTS & PROVIDER PAYOUTS ────────────────────────────────
        $businessCollection = $rideGross + $serviceGross + $marketGross;
        $companyCommission  = $rideComm + $serviceComm + $marketComm + $platformFeeTotal;
        $providerPayable    = max(0, round($businessCollection - $companyCommission, 2));

        $paidSettlement     = 0.0;
        $pendingSettlement  = 0.0;
        $recentSettlements  = collect();
        if (Schema::hasTable('withdrawals')) {
            $paidSettlement = (float)DB::table('withdrawals')->where('statut', 1)->whereBetween('creer', [$startStr, $endStr])->sum('amount');
            $pendingSettlement = (float)DB::table('withdrawals')->where('statut', 0)->sum('amount');

            $recentSettlements = DB::table('withdrawals as w')
                ->leftJoin('tj_conducteur as d', 'w.id_conducteur', '=', 'd.id')
                ->select(
                    'w.id', 'w.amount', 'w.statut', 'w.creer',
                    DB::raw("TRIM(CONCAT(COALESCE(d.prenom,''),' ',COALESCE(d.nom,''))) as provider_name"),
                    'd.phone', 'd.email'
                )
                ->orderBy('w.id', 'desc')
                ->limit(10)
                ->get();
        }

        // ── 10. ACCURATE PROFIT & LOSS STATEMENT ─────────────────────────────
        $refundsPnl     = $failedTxnsAmount;
        $netProfitPnl   = round($netRevenue - ($totalPromotionalCost + $gatewayCharges + $refundsPnl), 2);
        $profitMarginPnl= $grossRevenue > 0 ? round(($netProfitPnl / $grossRevenue) * 100, 1) : 0.0;

        // Daily Reports — filter valid non-cancelled rides ONLY
        $dailyReports = collect();
        if ($hasRequete) {
            $dailyReports = $validRide(DB::table('tj_requete'))
                ->whereBetween('creer', [$startStr, $endStr])
                ->select(
                    DB::raw('DATE(creer) as date'),
                    DB::raw('COUNT(id) as total_rides'),
                    DB::raw('COALESCE(SUM(montant), 0) as gross_amount'),
                    DB::raw('COALESCE(SUM(admin_commission), 0) as commission')
                )
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->limit(10)
                ->get();
        }
        if ($dailyReports->isEmpty() && $hasUserTxn) {
            $dailyReports = DB::table('tj_transaction')
                ->whereBetween('creer', [$startStr, $endStr])
                ->select(
                    DB::raw('DATE(creer) as date'),
                    DB::raw('COUNT(id) as total_rides'),
                    DB::raw('COALESCE(SUM(amount), 0) as gross_amount'),
                    DB::raw('0 as commission')
                )
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->limit(10)
                ->get();
        }

        // 6-Month Chart Data — filter valid non-cancelled items ONLY
        $chartLabels    = [];
        $chartGrossData = [];
        $chartNetData   = [];

        for ($i = 5; $i >= 0; $i--) {
            $mStart = Carbon::now()->subMonths($i)->startOfMonth()->toDateTimeString();
            $mEnd   = Carbon::now()->subMonths($i)->endOfMonth()->toDateTimeString();
            $monthName = Carbon::now()->subMonths($i)->format('M Y');

            $mGross = (float)($hasRequete ? $validRide(DB::table('tj_requete'))->whereBetween('creer', [$mStart, $mEnd])->sum('montant') : 0)
                    + (float)($hasMarketOrders ? $validMarket(DB::table('marketplace_orders'))->whereBetween('created_at', [$mStart, $mEnd])->sum('total_amount') : 0)
                    + (float)($hasServiceReq ? $validService(DB::table('service_requests'))->whereBetween('created_at', [$mStart, $mEnd])->sum('amount') : 0);
            
            $mNet = (float)($hasRequete ? $validRide(DB::table('tj_requete'))->whereBetween('creer', [$mStart, $mEnd])->sum('admin_commission') : 0)
                  + (float)($hasCondTxn ? DB::table('tj_conducteur_transaction')
                    ->where(function($q) { $q->where('payment_method', 'Commission')->orWhere('deduction_type', 'Commission'); })
                    ->whereBetween('creer', [$mStart, $mEnd])
                    ->sum(DB::raw('ABS(amount)')) : 0);

            $chartLabels[]    = $monthName;
            $chartGrossData[] = round($mGross, 2);
            $chartNetData[]   = round($mNet, 2);
        }

        // Payment mode breakdown
        $paymentModeData = DB::table('tj_transaction')
            ->whereBetween('creer', [$startStr, $endStr])
            ->where(function($q) { $q->where('payment_status', 'success')->orWhere('payment_status', 'Success'); })
            ->whereNotNull('payment_method')
            ->where('payment_method', '!=', '')
            ->select('payment_method', DB::raw('COUNT(*) as txn_count'), DB::raw('COALESCE(SUM(amount),0) as total'))
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        return [
            'revToday'                => round($revToday, 2),
            'revWeek'                 => round($revWeek, 2),
            'revMonth'                => round($revMonth, 2),
            'revYear'                 => round($revYear, 2),
            'grossRevenue'            => round($grossRevenue, 2),
            'netRevenue'              => round($netRevenue, 2),
            'totalTransactions'       => $totalTransactions,
            'servicesBreakdown'       => $servicesBreakdown,
            'totalCommissionEarned'   => round($totalCommissionEarned, 2),
            'marketplaceProductSales' => round($marketGross, 2),
            'marketplaceSellerComm'   => round($marketComm, 2),
            'categoryEarnings'        => $categoryEarnings,
            'recentMarketplaceOrders' => $recentMarketplaceOrders,
            'consumerPlansRev'        => 0.0,
            'businessPlansRev'        => round($subRevenue, 2),
            'totalSubscriptionRevenue'=> round($subRevenue, 2),
            'subHistoryList'          => $subHistoryList,
            'consumerPlanCount'       => $consumerPlanCount,
            'businessPlanCount'       => $businessPlanCount,
            'referralCount'           => $referralCount,
            'referralRewardsPaid'     => round($referralRewardsPaid, 2),
            'revenueByReferredUsers'  => round($revenueByReferredUsers, 2),
            'netReferralContribution' => round($netReferralContribution, 2),
            'referralList'            => $referralList,
            'totalPaymentVolume'      => round($totalPaymentVolume, 2),
            'gatewayCharges'          => round($gatewayCharges, 2),
            'platformCharges'         => round($platformFeeTotal, 2),
            'companyShare'            => round($netRevenue, 2),
            'failedTxnsCount'         => $failedTxnsCount,
            'failedTxnsAmount'        => round($failedTxnsAmount, 2),
            'recentTransactions'      => $recentTransactions,
            'cashbackGiven'           => round($cashbackGiven, 2),
            'discountsGiven'          => round($discountsGiven, 2),
            'premiumDiscounts'        => 0.0,
            'medicalCashbackGiven'    => round($medicalCashbackGiven, 2),
            'totalPromotionalCost'    => round($totalPromotionalCost, 2),
            'businessCollection'      => round($businessCollection, 2),
            'companyCommission'       => round($companyCommission, 2),
            'providerPayable'         => round($providerPayable, 2),
            'paidSettlement'          => round($paidSettlement, 2),
            'pendingSettlement'       => round($pendingSettlement, 2),
            'recentSettlements'       => $recentSettlements,
            'totalRevenuePnl'         => round($grossRevenue, 2),
            'refundsPnl'              => round($refundsPnl, 2),
            'otherExpensesPnl'        => 0.0,
            'netProfitPnl'            => round($netProfitPnl, 2),
            'profitMarginPnl'         => $profitMarginPnl,
            'gstCollectedTotal'       => round($gstCollectedTotal, 2),
            'gstCollectedOnline'      => round($gstCollectedOnline, 2),
            'gstCollectedCash'        => round($gstCollectedCash, 2),
            'platformFeeTotal'        => round($platformFeeTotal, 2),
            'platformFeeOnline'       => round($platformFeeOnline, 2),
            'platformFeeCash'         => round($platformFeeCash, 2),
            'pendingDriverDebt'       => round($pendingDriverDebt, 2),
            'driversWithDebtCount'    => $driversWithDebtCount,
            'driversDebtList'         => $driversDebtList,
            'realizedAdminRevenue'    => round($realizedAdminRevenue, 2),
            'dueAdminRevenue'         => round($dueAdminRevenue, 2),
            'onlineGrossVolume'       => round($onlineGrossVolume, 2),
            'cashGrossVolume'         => round($cashGrossVolume, 2),
            'dailyReports'            => $dailyReports,
            'chartLabels'             => $chartLabels,
            'chartGrossData'          => $chartGrossData,
            'chartNetData'            => $chartNetData,
            'paymentModeData'         => $paymentModeData,
        ];
    }
}
