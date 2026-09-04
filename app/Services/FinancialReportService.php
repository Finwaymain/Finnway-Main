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
     * WALLET RECHARGES ARE NEVER DOUBLE-COUNTED AS MERCHANDISE GMV.
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
        $cancelledParcelStatuses  = ['cancelled', 'canceled', 'rejected', 'failed'];

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

        $validParcel = function($q) use ($cancelledParcelStatuses) {
            return $q->whereNotIn('status', $cancelledParcelStatuses);
        };

        // 1. Configured Commission & Tax Rates from Database
        $defaultCommRate = 10.0;
        if (Schema::hasTable('tj_commission')) {
            $commRow = DB::table('tj_commission')->where('statut', 'yes')->first();
            if ($commRow && is_numeric($commRow->value)) {
                $defaultCommRate = (float)$commRow->value;
            }
        }

        $defaultGstRate = 18.0;
        $defaultPlatformFeeRate = 10.0;
        if (Schema::hasTable('tj_tax')) {
            $gstRow = DB::table('tj_tax')->where('statut', 'yes')->where(function($q) {
                $q->where('libelle', 'like', '%GST%')->orWhere('libelle', 'like', '%Tax%');
            })->first();
            if ($gstRow && is_numeric($gstRow->value)) {
                $defaultGstRate = (float)$gstRow->value;
            }

            $pFeeRow = DB::table('tj_tax')->where('statut', 'yes')->where(function($q) {
                $q->where('libelle', 'like', '%Platform%')->orWhere('libelle', 'like', '%Fee%');
            })->first();
            if ($pFeeRow && is_numeric($pFeeRow->value)) {
                $defaultPlatformFeeRate = (float)$pFeeRow->value;
            }
        }

        // Helper to calculate subscription revenue
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

        // Helper to extract strictly pure recorded tax liability from a row (JSON or numeric). Zero fallback.
        $parsePureRecordedTax = function($taxField): float {
            if (empty($taxField)) {
                return 0.0;
            }
            if (is_numeric($taxField)) {
                return (float)$taxField;
            }
            if (is_string($taxField)) {
                $decoded = json_decode($taxField, true);
                if (is_array($decoded)) {
                    $sum = 0.0;
                    foreach ($decoded as $item) {
                        $sum += (float)($item['value'] ?? $item['amount'] ?? 0);
                    }
                    return $sum;
                }
            }
            return 0.0;
        };

        $hasRequete      = Schema::hasTable('tj_requete');
        $hasMarketOrders = Schema::hasTable('marketplace_orders');
        $hasServiceReq   = Schema::hasTable('service_requests');
        $hasParcelOrders = Schema::hasTable('parcel_orders');
        $hasSubHist      = Schema::hasTable('subscription_history');
        $hasCondTxn      = Schema::hasTable('tj_conducteur_transaction');
        $hasUserTxn      = Schema::hasTable('tj_transaction');

        // Helper to sum real GMV (merchandise & services ONLY, no wallet top-up double counting)
        $calcPeriodGmv = function($pStart, $pEnd) use ($hasRequete, $hasMarketOrders, $hasServiceReq, $hasParcelOrders, $hasSubHist, $validRide, $validMarket, $validService, $validParcel, $calcSubRevenue) {
            $sum = 0.0;
            if ($hasRequete) {
                $sum += (float)$validRide(DB::table('tj_requete'))->whereBetween('creer', [$pStart, $pEnd])->sum('montant');
            }
            if ($hasMarketOrders) {
                $sum += (float)$validMarket(DB::table('marketplace_orders'))->whereBetween('created_at', [$pStart, $pEnd])->sum('total_amount');
            }
            if ($hasServiceReq) {
                $sum += (float)$validService(DB::table('service_requests'))->whereBetween('created_at', [$pStart, $pEnd])->sum('amount');
            }
            if ($hasParcelOrders) {
                $sum += (float)$validParcel(DB::table('parcel_orders'))->whereBetween('created_at', [$pStart, $pEnd])->sum('amount');
            }
            if ($hasSubHist) {
                $sum += $calcSubRevenue(DB::table('subscription_history')->whereBetween('created_at', [$pStart, $pEnd]));
            }
            return round($sum, 2);
        };

        // ── 1. REVENUE DASHBOARD (PERIODIC GMV) ──────────────────────────────
        $todayStart = Carbon::today()->startOfDay()->toDateTimeString();
        $todayEnd   = Carbon::today()->endOfDay()->toDateTimeString();
        $weekStart  = Carbon::now()->startOfWeek()->toDateTimeString();
        $monthStart = Carbon::now()->startOfMonth()->toDateTimeString();
        $yearStart  = Carbon::now()->startOfYear()->toDateTimeString();

        $revToday = $calcPeriodGmv($todayStart, $todayEnd);
        $revWeek  = $calcPeriodGmv($weekStart, $endStr);
        $revMonth = $calcPeriodGmv($monthStart, $endStr);
        $revYear  = $calcPeriodGmv($yearStart, $endStr);

        // ── 2. DETAILED SERVICE BREAKDOWN & TAX EXTRACTION ────────────────────
        $hasRideTypeCol  = $hasRequete && Schema::hasColumn('tj_requete', 'ride_type');
        $hasAdminCommCol = $hasRequete && Schema::hasColumn('tj_requete', 'admin_commission');

        // A. Cabs & Transport
        $cabGross = 0.0;
        $cabComm  = 0.0;
        $cabGst   = 0.0;
        $cabPFee  = 0.0;
        $cabBookings = 0;
        $cabOnlineGross = 0.0;
        $cabCashGross   = 0.0;

        if ($hasRequete) {
            $cabQuery = $validRide(DB::table('tj_requete'))->whereBetween('creer', [$startStr, $endStr]);
            if ($hasRideTypeCol) {
                $cabQuery->where(function($q) {
                    $q->whereNull('ride_type')->orWhereIn('ride_type', ['cab', 'city', 'transport', 'taxi', '']);
                });
            }
            $cabBookings = $cabQuery->count();
            $cabRows = $cabQuery->select('id', 'montant', 'admin_commission', 'tax', 'statut_paiement', 'id_payment_method')->get();

            foreach ($cabRows as $cr) {
                $fare = (float)($cr->montant ?? 0);
                $cabGross += $fare;

                // Commission: use actual if > 0, else default commission rate
                if (!empty($cr->admin_commission) && (float)$cr->admin_commission > 0) {
                    $cabComm += (float)$cr->admin_commission;
                } else {
                    $cabComm += round($fare * ($defaultCommRate / 100), 2);
                }

                // Pure GST: use recorded tax if present (JSON or numeric). Never inflate with phantom fallback.
                $tAmt = 0.0;
                if (!empty($cr->tax)) {
                    if (is_numeric($cr->tax)) {
                        $tAmt = (float)$cr->tax;
                    } else {
                        $tArr = json_decode($cr->tax, true);
                        if (is_array($tArr)) {
                            foreach ($tArr as $item) {
                                $tAmt += (float)($item['value'] ?? $item['amount'] ?? 0);
                            }
                        }
                    }
                }
                $cabGst += $tAmt;

                // Check cash vs online
                $isCash = (str_contains(strtolower((string)($cr->statut_paiement ?? '')), 'cash') || $cr->id_payment_method == 1);
                if ($isCash) {
                    $cabCashGross += $fare;
                } else {
                    $cabOnlineGross += $fare;
                }
            }
        }

        // B. Home Services & Repairs
        $homeGross = 0.0;
        $homeComm  = 0.0;
        $homeGst   = 0.0;
        $homePFee  = 0.0;
        $homeBookings = 0;
        $homeOnlineGross = 0.0;
        $homeCashGross   = 0.0;
        $homePFeeCash    = 0.0;
        $homePFeeOnline  = 0.0;
        $homeGstCash     = 0.0;
        $homeGstOnline   = 0.0;

        if ($hasServiceReq) {
            $homeQuery = $validService(DB::table('service_requests'))->whereBetween('created_at', [$startStr, $endStr]);
            $homeBookings = $homeQuery->count();
            $homeRows = $homeQuery->get();

            foreach ($homeRows as $hr) {
                $bAmt = (float)($hr->amount ?? 0);
                $homeGross += $bAmt;

                $pb = !empty($hr->price_breakdown) ? (is_string($hr->price_breakdown) ? json_decode($hr->price_breakdown, true) : (array)$hr->price_breakdown) : [];
                $pF = (float)($pb['platform_fee'] ?? 0);
                if ($pF == 0) {
                    $pF = 50.0; // Standard nominal platform fee
                }
                $homePFee += $pF;

                // Commission
                $sComm = (float)($pb['commission'] ?? 0);
                if ($sComm == 0) {
                    $sComm = round(max(0, $bAmt - $pF) * 0.10, 2);
                }
                $homeComm += $sComm;

                // Pure GST: strictly pure tax amount without arbitrary percentage inflation
                $sTax = (float)($hr->tax_amount ?? 0);
                if ($sTax == 0 && isset($pb['gst_amount'])) {
                    $sTax = (float)$pb['gst_amount'];
                }
                if ($sTax == 0 && isset($pb['taxes'])) {
                    $sTax = (float)$pb['taxes'];
                }
                $homeGst += $sTax;

                $isCash = in_array(strtolower(trim((string)($hr->payment_status ?? ''))), ['paid_cash', 'cash'], true);
                if ($isCash) {
                    $homeCashGross += $bAmt;
                    $homePFeeCash  += $pF;
                    $homeGstCash   += $sTax;
                } else {
                    $homeOnlineGross += $bAmt;
                    $homePFeeOnline  += $pF;
                    $homeGstOnline   += $sTax;
                }
            }
        }

        // C. Food Delivery
        $foodGross = 0.0;
        $foodComm  = 0.0;
        $foodGst   = 0.0;
        $foodPFee  = 0.0;
        $foodBookings = 0;
        if ($hasRideTypeCol) {
            $foodQuery = $validRide(DB::table('tj_requete'))->whereBetween('creer', [$startStr, $endStr])->where('ride_type', 'food');
            $foodBookings = $foodQuery->count();
            $foodRows = $foodQuery->select('montant', 'admin_commission', 'tax')->get();
            foreach ($foodRows as $fr) {
                $fFare = (float)($fr->montant ?? 0);
                $foodGross += $fFare;
                $foodComm  += (!empty($fr->admin_commission) && (float)$fr->admin_commission > 0) ? (float)$fr->admin_commission : round($fFare * 0.18, 2);
                $foodGst   += $parsePureRecordedTax($fr->tax ?? null);
            }
        }

        // D. Parcel & Courier
        $parcelGross = 0.0;
        $parcelComm  = 0.0;
        $parcelGst   = 0.0;
        $parcelPFee  = 0.0;
        $parcelBookings = 0;
        if ($hasParcelOrders) {
            $parcelQuery = $validParcel(DB::table('parcel_orders'))->whereBetween('created_at', [$startStr, $endStr]);
            $parcelBookings = $parcelQuery->count();
            $parcelRows = $parcelQuery->get();
            foreach ($parcelRows as $pr) {
                $pFare = (float)($pr->amount ?? 0);
                $parcelGross += $pFare;
                $parcelComm  += (!empty($pr->admin_commission) && (float)$pr->admin_commission > 0) ? (float)$pr->admin_commission : round($pFare * 0.10, 2);
                $parcelGst   += $parsePureRecordedTax($pr->tax ?? null);
            }
        }
        // Also add any rides marked parcel
        if ($hasRideTypeCol) {
            $pRideRows = $validRide(DB::table('tj_requete'))->whereBetween('creer', [$startStr, $endStr])->where('ride_type', 'parcel')->get();
            foreach ($pRideRows as $pr) {
                $parcelBookings++;
                $pFare = (float)($pr->montant ?? 0);
                $parcelGross += $pFare;
                $parcelComm  += (!empty($pr->admin_commission) && (float)$pr->admin_commission > 0) ? (float)$pr->admin_commission : round($pFare * 0.10, 2);
                $parcelGst   += $parsePureRecordedTax($pr->tax ?? null);
            }
        }

        // E. Travel & Outstation
        $travelGross = 0.0;
        $travelComm  = 0.0;
        $travelGst   = 0.0;
        $travelPFee  = 0.0;
        $travelBookings = 0;
        if ($hasRideTypeCol) {
            $travelQuery = $validRide(DB::table('tj_requete'))->whereBetween('creer', [$startStr, $endStr])->where('ride_type', 'travel');
            $travelBookings = $travelQuery->count();
            $travelRows = $travelQuery->select('montant', 'admin_commission', 'tax')->get();
            foreach ($travelRows as $tr) {
                $tFare = (float)($tr->montant ?? 0);
                $travelGross += $tFare;
                $travelComm  += (!empty($tr->admin_commission) && (float)$tr->admin_commission > 0) ? (float)$tr->admin_commission : round($tFare * 0.10, 2);
                $travelGst   += $parsePureRecordedTax($tr->tax ?? null);
            }
        }

        // F. Other Services
        $otherGross = 0.0;
        $otherComm  = 0.0;
        $otherGst   = 0.0;
        $otherPFee  = 0.0;
        $otherBookings = 0;
        if ($hasRideTypeCol) {
            $otherQuery = $validRide(DB::table('tj_requete'))->whereBetween('creer', [$startStr, $endStr])
                ->whereNotIn('ride_type', ['cab', 'city', 'transport', 'taxi', 'food', 'parcel', 'travel'])
                ->whereNotNull('ride_type')->where('ride_type', '!=', '');
            $otherBookings = $otherQuery->count();
            $otherRows = $otherQuery->select('montant', 'admin_commission', 'tax')->get();
            foreach ($otherRows as $or) {
                $oFare = (float)($or->montant ?? 0);
                $otherGross += $oFare;
                $otherComm  += (!empty($or->admin_commission) && (float)$or->admin_commission > 0) ? (float)$or->admin_commission : round($oFare * 0.10, 2);
                $otherGst   += $parsePureRecordedTax($or->tax ?? null);
            }
        }

        // G. Marketplace Orders & Commission
        $marketGross = 0.0;
        $marketComm  = 0.0;
        $marketPendingComm = 0.0;
        $marketGst   = 0.0;
        $marketPFee  = 0.0;
        $marketPendingPFee = 0.0;
        $marketTxnCount = 0;
        if ($hasMarketOrders) {
            $marketQuery = $validMarket(DB::table('marketplace_orders'))->whereBetween('created_at', [$startStr, $endStr]);
            $marketTxnCount = $marketQuery->count();
            $marketRows = $marketQuery->get();

            foreach ($marketRows as $mo) {
                $mAmt = (float)($mo->total_amount ?? 0);
                $marketGross += $mAmt;

                // Commission:
                $mComm = (float)($mo->admin_commission_amount ?? 0);
                if ($mComm == 0) {
                    $mRate = (float)($mo->admin_commission_rate ?? 10.0);
                    $mComm = round($mAmt * ($mRate / 100), 2);
                }

                // Split Marketplace Tax into pure GST (18%) and Platform Fee (10%)
                $taxAmt = (float)($mo->tax_amount ?? 0);
                $sub = (float)($mo->subtotal ?? 0);
                if ($sub <= 0 && $mAmt > 0) {
                    $sub = max(0, $mAmt - $taxAmt);
                }

                $taxName = strtolower((string)($mo->tax_name ?? ''));
                $taxRate = (float)($mo->tax_rate ?? 0);

                $orderGst = 0.0;
                $orderPFee = 0.0;

                if ($taxAmt > 0) {
                    if (str_contains($taxName, 'platform') && str_contains($taxName, 'gst')) {
                        $gstRate = 18.0;
                        $pFeeRate = 10.0;
                        if (preg_match('/gst\s*\(([0-9.]+)%\)/i', $taxName, $m)) {
                            $gstRate = (float)$m[1];
                        }
                        if (preg_match('/platform[^\(]*\(([0-9.]+)%\)/i', $taxName, $m)) {
                            $pFeeRate = (float)$m[1];
                        }
                        $rateSum = $gstRate + $pFeeRate;
                        if ($rateSum > 0) {
                            $orderGst = round($taxAmt * ($gstRate / $rateSum), 2);
                            $orderPFee = round($taxAmt - $orderGst, 2);
                        } else {
                            $orderGst = $taxAmt;
                        }
                    } elseif (str_contains($taxName, 'platform') && !str_contains($taxName, 'gst')) {
                        $orderPFee = $taxAmt;
                    } else {
                        if ($taxRate > 18.0 && $sub > 0) {
                            $orderGst = round($sub * 0.18, 2);
                            $orderPFee = max(0, round($taxAmt - $orderGst, 2));
                        } else {
                            $orderGst = $taxAmt;
                        }
                    }
                }

                // Pure GST is tax liability collected from the buyer
                $marketGst += $orderGst;

                // ❗ USER DIRECTIVE:
                // "and also in marketplace after only payout comission and platform fee should be added in earning managment"
                $isPayoutReleased = (strtolower(trim((string)($mo->payout_status ?? ''))) === 'released');

                if ($isPayoutReleased) {
                    $marketComm += $mComm;
                    $marketPFee += $orderPFee;
                } else {
                    $marketPendingComm += $mComm;
                    $marketPendingPFee += $orderPFee;
                }
            }
        }

        // H. Subscription Revenue
        $subRevenue = 0.0;
        $subTxnCount = 0;
        if ($hasSubHist) {
            $subRevenue  = $calcSubRevenue(DB::table('subscription_history')->whereBetween('created_at', [$startStr, $endStr]));
            $subTxnCount = DB::table('subscription_history')->whereBetween('created_at', [$startStr, $endStr])->count();
        }

        // ── 3. TOTAL ECOSYSTEM AGGREGATES ────────────────────────────────────
        // Platform Fees (Admin Revenue Source #2 - includes realized marketplace platform fees)
        $platformFeeTotal  = round($homePFee + $cabPFee + $foodPFee + $parcelPFee + $travelPFee + $otherPFee + $marketPFee, 2);
        $platformFeeOnline = round($homePFeeOnline + $marketPFee, 2);
        $platformFeeCash   = round($homePFeeCash, 2);

        // GST Tax (Liability • Kept separate from Admin Revenue • Strict GST without platform fees)
        $gstCollectedTotal  = round($cabGst + $homeGst + $foodGst + $parcelGst + $travelGst + $otherGst + $marketGst, 2);
        $gstCollectedOnline = round($cabGst + $homeGstOnline + $foodGst + $parcelGst + $travelGst + $otherGst + $marketGst, 2);
        $gstCollectedCash   = round($homeGstCash, 2);

        // Total Commissions Earned
        $totalCommissionEarned = round($cabComm + $homeComm + $foodComm + $parcelComm + $travelComm + $otherComm, 2);

        // Total Gross Ecosystem Revenue (GMV)
        // ❗ PURE MERCHANDISE & SERVICE VOLUME ONLY: NEVER SUMS WALLET TOP-UPS!
        $grossRevenue = round($cabGross + $homeGross + $foodGross + $parcelGross + $travelGross + $otherGross + $marketGross + $subRevenue, 2);
        $onlineGrossVolume = round($cabOnlineGross + $homeOnlineGross + $foodGross + $parcelGross + $travelGross + $otherGross + $marketGross + $subRevenue, 2);
        $cashGrossVolume   = round($cabCashGross + $homeCashGross, 2);

        // Net Admin Revenue (Commissions + Platform Fees + Subscriptions)
        // Marketplace commission & platform fees ONLY included after payout release!
        $netRevenue = round($totalCommissionEarned + $marketComm + $platformFeeTotal + $subRevenue, 2);
        $totalTransactions = $cabBookings + $homeBookings + $foodBookings + $parcelBookings + $travelBookings + $otherBookings + $marketTxnCount + $subTxnCount;

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

        // Realized vs Due Cash Split
        $dueAdminRevenue = round(min($netRevenue, $pendingDriverDebt), 2);
        $realizedAdminRevenue = round(max(0, $netRevenue - $dueAdminRevenue), 2);

        // ── 4. SERVICE BREAKDOWN ARRAY (SECTION 2) ───────────────────────────
        $servicesBreakdown = [
            [
                'service'       => 'Cab & Transport Rides',
                'rate'          => 'Dynamic %',
                'bookings'      => $cabBookings,
                'gross'         => round($cabGross, 2),
                'commission'    => round($cabComm, 2),
                'platform_fee'  => round($cabPFee, 2),
                'gst'           => round($cabGst, 2),
                'admin_earning' => round($cabComm + $cabPFee, 2),
            ],
            [
                'service'       => 'Home Services & Repairs',
                'rate'          => '10% + Platform Fee',
                'bookings'      => $homeBookings,
                'gross'         => round($homeGross, 2),
                'commission'    => round($homeComm, 2),
                'platform_fee'  => round($homePFee, 2),
                'gst'           => round($homeGst, 2),
                'admin_earning' => round($homeComm + $homePFee, 2),
            ],
            [
                'service'       => 'Food Delivery Orders',
                'rate'          => '18%',
                'bookings'      => $foodBookings,
                'gross'         => round($foodGross, 2),
                'commission'    => round($foodComm, 2),
                'platform_fee'  => round($foodPFee, 2),
                'gst'           => round($foodGst, 2),
                'admin_earning' => round($foodComm + $foodPFee, 2),
            ],
            [
                'service'       => 'Parcel & Courier',
                'rate'          => 'Flat / 10%',
                'bookings'      => $parcelBookings,
                'gross'         => round($parcelGross, 2),
                'commission'    => round($parcelComm, 2),
                'platform_fee'  => round($parcelPFee, 2),
                'gst'           => round($parcelGst, 2),
                'admin_earning' => round($parcelComm + $parcelPFee, 2),
            ],
            [
                'service'       => 'Travel & Outstation',
                'rate'          => '10%',
                'bookings'      => $travelBookings,
                'gross'         => round($travelGross, 2),
                'commission'    => round($travelComm, 2),
                'platform_fee'  => round($travelPFee, 2),
                'gst'           => round($travelGst, 2),
                'admin_earning' => round($travelComm + $travelPFee, 2),
            ],
            [
                'service'       => 'Other On-Demand Services',
                'rate'          => '10%',
                'bookings'      => $otherBookings,
                'gross'         => round($otherGross, 2),
                'commission'    => round($otherComm, 2),
                'platform_fee'  => round($otherPFee, 2),
                'gst'           => round($otherGst, 2),
                'admin_earning' => round($otherComm + $otherPFee, 2),
            ],
        ];

        // ── 5. MARKETPLACE CATEGORIES & RECENT (SECTION 3) ───────────────────
        $categoryEarnings = collect();
        if (Schema::hasTable('marketplace_order_items') && Schema::hasTable('marketplace_products')) {
            $categoryEarnings = DB::table('marketplace_order_items')
                ->join('marketplace_products', 'marketplace_order_items.product_id', '=', 'marketplace_products.id')
                ->leftJoin('marketplace_categories', 'marketplace_products.category_id', '=', 'marketplace_categories.id')
                ->join('marketplace_orders', 'marketplace_order_items.order_id', '=', 'marketplace_orders.id')
                ->whereNotIn('marketplace_orders.status', $cancelledMarketStatuses)
                ->whereBetween('marketplace_orders.created_at', [$startStr, $endStr])
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
        if ($hasMarketOrders) {
            $recentMarketplaceOrders = $validMarket(DB::table('marketplace_orders as o'))
                ->leftJoin('tj_user_app as u', 'o.user_id', '=', 'u.id')
                ->select(
                    'o.id', 'o.total_amount', 'o.status', 'o.payout_status', 'o.created_at', 'o.admin_commission_amount', 'o.admin_commission_rate',
                    'o.tax_name', 'o.tax_amount', 'o.subtotal',
                    DB::raw("TRIM(CONCAT(COALESCE(u.prenom,''),' ',COALESCE(u.nom,''))) as buyer_name"),
                    'u.phone'
                )
                ->whereBetween('o.created_at', [$startStr, $endStr])
                ->orderBy('o.id', 'desc')
                ->limit(10)
                ->get()
                ->map(function($order) {
                    $c = (float)($order->admin_commission_amount ?? 0);
                    if ($c == 0) {
                        $rate = (float)($order->admin_commission_rate ?? 10.0);
                        $c = round((float)$order->total_amount * ($rate / 100), 2);
                    }
                    $order->seller_commission = $c;
                    $order->is_payout_released = (strtolower(trim((string)($order->payout_status ?? ''))) === 'released');
                    return $order;
                });
        }

        // ── 6. SUBSCRIPTION PLANS (SECTION 4) ────────────────────────────────
        $consumerPlanCount = DB::table('tj_user_app')->whereNotNull('consumer_plan_id')->where('consumer_plan_id', '>', 0)->count();
        $businessPlanCount = DB::table('tj_conducteur')->whereNotNull('subscriptionPlanId')->where('subscriptionPlanId', '>', 0)->count();

        $subHistoryList = collect();
        if ($hasSubHist) {
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

        // ── 7. REFERRAL (SECTION 5) ──────────────────────────────────────────
        $referralCount = Schema::hasTable('referral') ? DB::table('referral')->count() : 0;
        $referralRewardsPaid = (float)DB::table('tj_transaction')
            ->where(function($q) {
                $q->where('payment_method', 'like', '%Referral%')
                  ->orWhere('payment_method', 'like', '%Refer%')
                  ->orWhere('type', 'referral');
            })
            ->sum('amount');

        $referredUserIds = Schema::hasTable('referral') ? DB::table('referral')->where('user_type', '!=', 'driver')->pluck('user_id')->toArray() : [];
        $revenueByReferredUsers = 0.0;
        if (!empty($referredUserIds)) {
            $revenueByReferredUsers = (float)($hasRequete ? $validRide(DB::table('tj_requete'))->whereIn('id_user_app', $referredUserIds)->sum('montant') : 0)
                                    + (float)($hasMarketOrders ? $validMarket(DB::table('marketplace_orders'))->whereIn('user_id', $referredUserIds)->sum('total_amount') : 0)
                                    + (float)($hasServiceReq ? $validService(DB::table('service_requests'))->whereIn('user_id', $referredUserIds)->sum('amount') : 0);
        }
        $netReferralContribution = round($revenueByReferredUsers - $referralRewardsPaid, 2);

        $referralList = collect();
        if (Schema::hasTable('referral')) {
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
        }

        // ── 8. PAYMENTS & GATEWAY CHARGES (SECTION 6) ────────────────────────
        // External Inflow (UPI, Cards, NetBanking, Razorpay, etc.)
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
                  ->orWhere('payment_method', 'LIKE', '%NetBanking%');
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

        // Internal Wallet Spending (Internal ecosystem money rotation)
        $internalWalletSpent = (float)DB::table('tj_transaction')
            ->where(function($q) { $q->where('payment_status', 'success')->orWhere('payment_status', 'Success'); })
            ->whereBetween('creer', [$startStr, $endStr])
            ->where('payment_method', 'LIKE', '%Wallet%')
            ->sum('amount');

        $totalPaymentVolume = round($externalGatewayVolume + $internalWalletSpent, 2);
        $gatewayCharges     = round($externalGatewayVolume * 0.02, 2); // 2% gateway charge ONLY on external card/UPI money

        $failedTxnsCount  = DB::table('tj_transaction')->whereIn('payment_status', ['Failed', 'failed', 'Cancelled', 'cancelled', 'Refunded', 'refunded'])->count();
        $failedTxnsAmount = (float)DB::table('tj_transaction')->whereIn('payment_status', ['Failed', 'failed', 'Cancelled', 'cancelled', 'Refunded', 'refunded'])->sum('amount');

        $recentTransactions = DB::table('tj_transaction as t')
            ->leftJoin('tj_user_app as u', 't.id_user_app', '=', 'u.id')
            ->select('t.*', DB::raw("TRIM(CONCAT(COALESCE(u.prenom,''),' ',COALESCE(u.nom,''))) as user_name"), 'u.phone')
            ->orderBy('t.id', 'desc')
            ->limit(10)
            ->get();

        // ── 9. CASHBACK & PROMOTIONAL BURNS (SECTION 7) ──────────────────────
        $cashbackGiven = (float)DB::table('tj_transaction')
            ->whereIn('type', ['cashback', 'bonus'])
            ->whereBetween('creer', [$startStr, $endStr])
            ->sum('amount');

        $discountsGiven = (float)($hasRequete ? $validRide(DB::table('tj_requete'))->whereBetween('creer', [$startStr, $endStr])->sum('discount') : 0);
        $medicalCashbackGiven = (float)(Schema::hasTable('tj_medical_claims') ? DB::table('tj_medical_claims')->where('status', 'approved')->sum('approved_amount') : 0);
        $totalPromotionalCost = round($cashbackGiven + $discountsGiven + $referralRewardsPaid + $medicalCashbackGiven, 2);

        // ── 10. SETTLEMENTS & PROVIDER PAYOUTS (SECTION 8) ───────────────────
        $businessCollection = round($cabGross + $homeGross + $foodGross + $parcelGross + $travelGross + $otherGross + $marketGross, 2);
        $companyCommission  = round($totalCommissionEarned + $marketComm + $platformFeeTotal, 2);
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

        // ── 11. PROFIT & LOSS (SECTION 9) ────────────────────────────────────
        $refundsPnl      = $failedTxnsAmount;
        $netProfitPnl    = round($netRevenue - ($totalPromotionalCost + $gatewayCharges + $refundsPnl), 2);
        $profitMarginPnl = $grossRevenue > 0 ? round(($netProfitPnl / $grossRevenue) * 100, 1) : 0.0;

        // Daily Reports
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

        // 6-Month Chart Data
        $chartLabels    = [];
        $chartGrossData = [];
        $chartNetData   = [];

        for ($i = 5; $i >= 0; $i--) {
            $mStart = Carbon::now()->subMonths($i)->startOfMonth()->toDateTimeString();
            $mEnd   = Carbon::now()->subMonths($i)->endOfMonth()->toDateTimeString();
            $monthName = Carbon::now()->subMonths($i)->format('M Y');

            $mGross = (float)($hasRequete ? $validRide(DB::table('tj_requete'))->whereBetween('creer', [$mStart, $mEnd])->sum('montant') : 0)
                    + (float)($hasMarketOrders ? $validMarket(DB::table('marketplace_orders'))->whereBetween('created_at', [$mStart, $mEnd])->sum('total_amount') : 0)
                    + (float)($hasServiceReq ? $validService(DB::table('service_requests'))->whereBetween('created_at', [$mStart, $mEnd])->sum('amount') : 0)
                    + (float)($hasParcelOrders ? $validParcel(DB::table('parcel_orders'))->whereBetween('created_at', [$mStart, $mEnd])->sum('amount') : 0);
            
            $mNet = (float)($hasRequete ? $validRide(DB::table('tj_requete'))->whereBetween('creer', [$mStart, $mEnd])->sum('admin_commission') : 0)
                  + (float)($hasMarketOrders ? $validMarket(DB::table('marketplace_orders'))->whereBetween('created_at', [$mStart, $mEnd])->sum('admin_commission_amount') : 0)
                  + (float)($hasCondTxn ? DB::table('tj_conducteur_transaction')
                    ->where(function($q) { $q->where('payment_method', 'Commission')->orWhere('deduction_type', 'Commission'); })
                    ->whereBetween('creer', [$mStart, $mEnd])
                    ->sum(DB::raw('ABS(amount)')) : 0);

            $chartLabels[]    = $monthName;
            $chartGrossData[] = round($mGross, 2);
            $chartNetData[]   = round($mNet, 2);
        }

        // ── 12. WALLET FLOAT & ECOSYSTEM BALANCE SHEET (SECTION 11) ──────────
        // A. Current Live Wallet Holdings (Holding Liabilities)
        $userWalletTotal = (float)(Schema::hasTable('tj_user_app') ? DB::table('tj_user_app')->where('amount', '>', 0)->sum('amount') : 0);
        $userWalletHolders = Schema::hasTable('tj_user_app') ? DB::table('tj_user_app')->where('amount', '>', 0)->count() : 0;

        $driverWalletTotal = (float)(Schema::hasTable('tj_conducteur') ? DB::table('tj_conducteur')->where('amount', '>', 0)->sum('amount') : 0);
        $driverWalletHolders = Schema::hasTable('tj_conducteur') ? DB::table('tj_conducteur')->where('amount', '>', 0)->count() : 0;

        $totalEcosystemFloat = round($userWalletTotal + $driverWalletTotal, 2);

        // B. Period Wallet Inflows (How money entered the wallets)
        // 1. External Gateway Top-ups (Razorpay, UPI, NetBanking)
        $userTopUps = 0.0;
        if (Schema::hasTable('tj_transaction')) {
            $userTopUps = (float)DB::table('tj_transaction')
                ->whereBetween('creer', [$startStr, $endStr])
                ->where(function($q) {
                    $q->whereIn('payment_method', ['UPI / NetBanking', 'Razorpay', 'RazorPay', 'Razorpay / UPI', 'Online', 'Card', 'UPI', 'NetBanking'])
                      ->orWhere('description', 'like', '%Top-Up%')
                      ->orWhere('description', 'like', '%Recharge%');
                })
                ->whereNotIn('payment_method', ['Referral Reward', 'Wallet Cashback', 'Marketplace Escrow'])
                ->sum('amount');
        }

        $driverTopUps = 0.0;
        if (Schema::hasTable('tj_conducteur_transaction')) {
            $driverTopUps = (float)DB::table('tj_conducteur_transaction')
                ->whereBetween('creer', [$startStr, $endStr])
                ->whereIn('payment_method', ['Razorpay', 'Razorpay / UPI', 'UPI', 'Online'])
                ->sum('amount');
        }
        $totalExternalTopUps = round($userTopUps + $driverTopUps, 2);

        // 2. Promotional Credits (Cashback & Referral Rewards)
        $userRewardsCredits = 0.0;
        if (Schema::hasTable('tj_transaction')) {
            $userRewardsCredits = (float)DB::table('tj_transaction')
                ->whereBetween('creer', [$startStr, $endStr])
                ->where(function($q) {
                    $q->whereIn('type', ['cashback', 'bonus', 'referral'])
                      ->orWhereIn('payment_method', ['Referral Reward', 'Wallet Cashback']);
                })
                ->sum('amount');
        }

        $driverRewardsCredits = 0.0;
        if (Schema::hasTable('tj_conducteur_transaction')) {
            $driverRewardsCredits = (float)DB::table('tj_conducteur_transaction')
                ->whereBetween('creer', [$startStr, $endStr])
                ->whereIn('payment_method', ['Referral Reward'])
                ->sum('amount');
        }
        $totalRewardCredits = round($userRewardsCredits + $driverRewardsCredits, 2);

        // 3. Partner Earnings & Escrow Credited to Wallets
        $escrowCredits = 0.0;
        if (Schema::hasTable('tj_transaction')) {
            $escrowCredits = (float)DB::table('tj_transaction')
                ->whereBetween('creer', [$startStr, $endStr])
                ->where('payment_method', 'Marketplace Escrow')
                ->sum('amount');
        }
        $totalWalletInflows = round($totalExternalTopUps + $totalRewardCredits + $escrowCredits, 2);

        // C. Period Wallet Outflows (Deductions from the wallet float)
        // 1. Wallet Spent on Purchases & Services (Rides, Services, Marketplace)
        $walletPurchases = 0.0;
        if (Schema::hasTable('tj_transaction')) {
            $walletPurchases = (float)DB::table('tj_transaction')
                ->whereBetween('creer', [$startStr, $endStr])
                ->where(function($q) {
                    $q->whereIn('deduction_type', [0, 2, 'debit'])
                      ->orWhere('type', 'debit');
                })
                ->where(function($q) {
                    $q->whereIn('payment_method', ['Wallet', 'Fiinway Wallet'])
                      ->orWhere('description', 'like', '%Payment for Order%')
                      ->orWhere('description', 'like', '%Order #%')
                      ->orWhere('description', 'like', '%Booking%')
                      ->orWhere('description', 'like', '%Ride%');
                })
                ->where('description', 'not like', 'Transferred %')
                ->sum('amount');
        }

        // 2. External Bank Withdrawals & Payouts (Real Cash Out)
        $settledWithdrawals = 0.0;
        if (Schema::hasTable('withdrawals')) {
            $settledWithdrawals = (float)DB::table('withdrawals')
                ->whereBetween('creer', [$startStr, $endStr])
                ->whereIn('statut', ['1', 'success'])
                ->sum('amount');
        }

        // 3. Commissions & Fees Deducted from Wallets
        $commDeductions = 0.0;
        if (Schema::hasTable('tj_conducteur_transaction')) {
            $commDeductions = (float)DB::table('tj_conducteur_transaction')
                ->whereBetween('creer', [$startStr, $endStr])
                ->where(function($q) {
                    $q->where('payment_method', 'Commission')
                      ->orWhere('deduction_type', 'Commission');
                })
                ->sum(DB::raw('ABS(amount)'));
        }

        $platformFeeDeductions = 0.0;
        if (Schema::hasTable('tj_transaction')) {
            $platformFeeDeductions = (float)DB::table('tj_transaction')
                ->whereBetween('creer', [$startStr, $endStr])
                ->where('payment_method', 'Platform Fee')
                ->sum('amount');
        }
        $totalWalletOutflows = round($walletPurchases + $settledWithdrawals + $commDeductions + $platformFeeDeductions, 2);

        // D. Closed-Loop Peer-to-Peer Transfers (Zero Net Ecosystem Impact)
        $p2pTransfersVolume = 0.0;
        $p2pTransfersCount  = 0;
        if (Schema::hasTable('tj_transaction')) {
            $p2pTransfersVolume = (float)DB::table('tj_transaction')
                ->whereBetween('creer', [$startStr, $endStr])
                ->where(function($q) {
                    $q->where('description', 'like', 'Transferred %')
                      ->orWhere('description', 'like', 'Received %')
                      ->orWhereNotNull('sender_user_type');
                })
                ->where('deduction_type', 0)
                ->sum('amount');
            if ($p2pTransfersVolume == 0) {
                $p2pTransfersVolume = (float)DB::table('tj_transaction')
                    ->whereBetween('creer', [$startStr, $endStr])
                    ->where('description', 'like', 'Transferred %')
                    ->sum('amount');
            }
            $p2pTransfersCount = DB::table('tj_transaction')
                ->whereBetween('creer', [$startStr, $endStr])
                ->where(function($q) {
                    $q->where('description', 'like', 'Transferred %')
                      ->orWhereNotNull('sender_user_type');
                })
                ->where('deduction_type', 0)
                ->count();
        }

        // Top Wallet Holders
        $topUserWallets = Schema::hasTable('tj_user_app') ? DB::table('tj_user_app')
            ->where('amount', '>', 0)
            ->select('id', DB::raw("TRIM(CONCAT(COALESCE(prenom,''),' ',COALESCE(nom,''))) as name"), 'phone', 'amount', 'modifier as updated_at')
            ->orderByDesc('amount')
            ->limit(5)
            ->get() : collect();

        $topDriverWallets = Schema::hasTable('tj_conducteur') ? DB::table('tj_conducteur')
            ->where('amount', '>', 0)
            ->select('id', DB::raw("TRIM(CONCAT(COALESCE(prenom,''),' ',COALESCE(nom,''))) as name"), 'phone', 'amount', 'modifier as updated_at')
            ->orderByDesc('amount')
            ->limit(5)
            ->get() : collect();

        // Recent Wallet Movements
        $recentWalletMovements = collect();
        if (Schema::hasTable('tj_transaction')) {
            $recentWalletMovements = DB::table('tj_transaction as t')
                ->leftJoin('tj_user_app as u', 't.id_user_app', '=', 'u.id')
                ->select('t.id', 't.amount', 't.payment_method', 't.description', 't.deduction_type', 't.type', 't.creer',
                         DB::raw("TRIM(CONCAT(COALESCE(u.prenom,''),' ',COALESCE(u.nom,''))) as user_name"), 'u.phone')
                ->whereBetween('t.creer', [$startStr, $endStr])
                ->orderByDesc('t.id')
                ->limit(10)
                ->get()
                ->map(function($tx) {
                    $desc = strtolower((string)$tx->description);
                    if (str_contains($desc, 'transfer') || str_contains($desc, 'received') || !empty($tx->sender_user_type)) {
                        $tx->flow_type = 'transfer';
                        $tx->flow_label = 'P2P Transfer (Neutral)';
                        $tx->badge_class = 'badge-dark-info';
                    } elseif ($tx->deduction_type == 1 || $tx->type == 'credit' || str_contains($desc, 'top-up') || str_contains($desc, 'recharge')) {
                        $tx->flow_type = 'inflow';
                        $tx->flow_label = 'Inflow (Top-Up / Credit)';
                        $tx->badge_class = 'badge-dark-success';
                    } else {
                        $tx->flow_type = 'outflow';
                        $tx->flow_label = 'Outflow (Spend / Deduction)';
                        $tx->badge_class = 'badge-dark-danger';
                    }
                    return $tx;
                });
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
            'marketplacePendingComm'  => round($marketPendingComm, 2),
            'marketplacePlatformFee'  => round($marketPFee, 2),
            'marketplacePendingPFee'  => round($marketPendingPFee, 2),
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
            'externalGatewayVolume'   => round($externalGatewayVolume, 2),
            'internalWalletSpent'     => round($internalWalletSpent, 2),
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
            'totalEcosystemFloat'     => $totalEcosystemFloat,
            'userWalletTotal'         => $userWalletTotal,
            'userWalletHolders'       => $userWalletHolders,
            'driverWalletTotal'       => $driverWalletTotal,
            'driverWalletHolders'     => $driverWalletHolders,
            'totalWalletInflows'      => $totalWalletInflows,
            'totalExternalTopUps'     => $totalExternalTopUps,
            'totalRewardCredits'      => $totalRewardCredits,
            'escrowCredits'           => $escrowCredits,
            'totalWalletOutflows'     => $totalWalletOutflows,
            'walletPurchases'         => $walletPurchases,
            'settledWithdrawals'      => $settledWithdrawals,
            'commDeductions'          => round($commDeductions + $platformFeeDeductions, 2),
            'p2pTransfersVolume'      => $p2pTransfersVolume,
            'p2pTransfersCount'       => $p2pTransfersCount,
            'topUserWallets'          => $topUserWallets,
            'topDriverWallets'        => $topDriverWallets,
            'recentWalletMovements'   => $recentWalletMovements,
        ];
    }
}
