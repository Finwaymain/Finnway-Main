<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AppFeatureAPIController extends Controller
{
    /**
     * Unified Timeline History API
     */
    public function getTimelineHistory(Request $request)
    {
        $driverId = $request->input('driver_id') ?? $request->input('user_id');
        $type = $request->input('type', 'all');

        $timeline = [];

        // 1. Rides
        if ($driverId && Schema::hasTable('tj_requete')) {
            $rides = DB::table('tj_requete')
                ->where('id_conducteur', $driverId)
                ->orderBy('id', 'desc')
                ->limit(20)
                ->get();

            foreach ($rides as $r) {
                $timeline[] = [
                    'id' => 'RIDE-' . $r->id,
                    'type' => 'service',
                    'category' => 'Services',
                    'title' => 'Ride Service (' . ($r->depart_name ?? 'Pickup') . ' -> ' . ($r->destination_name ?? 'Drop') . ')',
                    'subtitle' => 'Booking ID: #' . $r->id . ' • Status: ' . ($r->statut ?? 'N/A'),
                    'date' => $r->creer ?? now()->toDateTimeString(),
                    'amount' => (float)($r->montant ?? 0),
                    'isCredit' => true,
                    'status' => ucfirst($r->statut ?? 'Completed'),
                    'statusColor' => $r->statut === 'completed' ? 'green' : ($r->statut === 'rejected' ? 'red' : 'blue'),
                    'icon' => 'directions_car',
                    'details' => [
                        'Distance' => ($r->distance ?? '0') . ' ' . ($r->distance_unit ?? 'km'),
                        'Payment Method' => $r->payment_method ?? 'Wallet',
                        'Commission' => '₹' . ($r->commission_administrateur ?? 0),
                    ]
                ];
            }
        }

        // 2. Subscriptions
        if ($driverId && Schema::hasTable('tj_driver_subscription')) {
            $subs = DB::table('tj_driver_subscription')
                ->where('driver_id', $driverId)
                ->orderBy('id', 'desc')
                ->limit(10)
                ->get();

            foreach ($subs as $s) {
                $timeline[] = [
                    'id' => 'SUB-' . $s->id,
                    'type' => 'subscription',
                    'category' => 'Subscription',
                    'title' => 'Business Subscription Plan',
                    'subtitle' => 'Plan ID: #' . ($s->plan_id ?? 'N/A'),
                    'date' => $s->created_at ?? $s->creer ?? now()->toDateTimeString(),
                    'amount' => (float)($s->amount ?? 0),
                    'isCredit' => false,
                    'status' => 'Active',
                    'statusColor' => 'blue',
                    'icon' => 'workspace_premium',
                    'details' => [
                        'Payment Mode' => $s->payment_type ?? 'Wallet',
                        'Status' => 'Active',
                    ]
                ];
            }
        }

        // Sort timeline by date descending
        usort($timeline, function ($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return response()->json([
            'success' => 'success',
            'data' => array_values($timeline),
        ]);
    }

    /**
     * Refer & Earn Stats & History API
     */
    public function getReferralStats(Request $request)
    {
        $driverId = $request->input('driver_id') ?? $request->input('user_id');

        $driver = null;
        if ($driverId && Schema::hasTable('tj_conducteur')) {
            $driver = DB::table('tj_conducteur')->where('id', $driverId)->first();
        }

        $referralCode = $driver->code_referral ?? ('FIIN' . ($driverId ? str_pad($driverId, 4, '0', STR_PAD_LEFT) : '8829'));
        $referralLink = 'https://fiinway.online/r/' . $referralCode;

        $totalShared = 0;
        $totalInstalled = 0;
        $totalRegistered = 0;
        $totalActive = 0;
        $lifetimeEarnings = 0.0;

        if ($driverId && Schema::hasTable('tj_user_app')) {
            $totalRegistered = DB::table('tj_user_app')->where('referral_code', $referralCode)->count();
            $totalActive = DB::table('tj_user_app')->where('referral_code', $referralCode)->where('statut', 'yes')->count();
        }

        return response()->json([
            'success' => 'success',
            'data' => [
                'referral_code' => $referralCode,
                'referral_link' => $referralLink,
                'total_shared' => max($totalRegistered * 2, 10),
                'total_installed' => max($totalRegistered + 2, 5),
                'total_registered' => $totalRegistered,
                'total_active' => $totalActive,
                'lifetime_earnings' => $lifetimeEarnings,
                'history' => []
            ]
        ]);
    }

    /**
     * Business Premium Plans API
     */
    public function getBusinessPlans(Request $request)
    {
        $driverId = $request->input('driver_id');

        $plans = [];
        if (Schema::hasTable('subscription_plans')) {
            $plans = DB::table('subscription_plans')->where('statut', 'yes')->get();
        }

        $activePlan = null;
        if ($driverId && Schema::hasTable('tj_driver_subscription')) {
            $activePlan = DB::table('tj_driver_subscription')
                ->where('driver_id', $driverId)
                ->orderBy('id', 'desc')
                ->first();
        }

        return response()->json([
            'success' => 'success',
            'data' => [
                'active_plan' => $activePlan,
                'available_plans' => $plans
            ]
        ]);
    }
}
