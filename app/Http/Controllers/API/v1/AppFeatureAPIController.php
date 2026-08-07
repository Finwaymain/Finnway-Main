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

        usort($timeline, function ($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return response()->json([
            'success' => 'success',
            'data' => array_values($timeline),
        ]);
    }

    /**
     * Refer & Earn Stats API
     */
    public function getReferralStats(Request $request)
    {
        $driverId = $request->input('driver_id') ?? $request->input('user_id');
        $stats = $this->buildDriverReferralStats($driverId);

        return response()->json([
            'success' => 'success',
            'data' => $stats,
        ]);
    }

    /**
     * Refer & Earn History API (summary + recent referred users)
     */
    public function getReferralHistory(Request $request)
    {
        $driverId = $request->input('driver_id') ?? $request->input('user_id');
        $stats = $this->buildDriverReferralStats($driverId);
        $recentUsers = $this->buildDriverReferralUsers($driverId);

        return response()->json([
            'success' => 'success',
            'data' => [
                'stats' => $stats,
                'summary' => [
                    'app_installed' => $stats['app_installed'],
                    'registered' => $stats['registered'],
                    'verified' => $stats['verified'],
                    'active_business' => $stats['active_business'],
                    'active_services' => $stats['active_services'],
                    'total_transactions' => $stats['total_transactions'],
                ],
                'recent_users' => $recentUsers,
            ],
        ]);
    }

    private function buildDriverReferralStats($driverId): array
    {
        $driver = null;
        $customer = null;
        if ($driverId && Schema::hasTable('tj_conducteur')) {
            $driver = DB::table('tj_conducteur')->where('id', $driverId)->first();
        }
        if (!$driver && $driverId && Schema::hasTable('tj_user_app')) {
            $customer = DB::table('tj_user_app')->where('id', $driverId)->first();
        }

        $referralCode = $this->resolveDriverReferralCode($driverId, $driver);
        $referralLink = 'https://fiinway.online/r/' . $referralCode;
        $walletBalance = (float)($driver->amount ?? $customer->amount ?? 0.0);

        $referredUserIds = $this->getReferredUserIds($driverId, $referralCode);
        $totalReferrals = count($referredUserIds);

        $appInstalled = 0;
        $registered = 0;
        $verified = 0;
        $activeUsers = 0;
        $activeBusiness = 0;
        $activeServices = 0;
        $totalTransactions = 0;
        $earnings = 0.0;

        if (!empty($referredUserIds)) {
            if (Schema::hasTable('tj_user_app')) {
                $userQuery = DB::table('tj_user_app')->whereIn('id', $referredUserIds);

                if (Schema::hasColumn('tj_user_app', 'fcm_id')) {
                    $appInstalled += (clone $userQuery)->whereNotNull('fcm_id')->where('fcm_id', '!=', '')->count();
                } elseif (Schema::hasColumn('tj_user_app', 'device_id')) {
                    $appInstalled += (clone $userQuery)->whereNotNull('device_id')->where('device_id', '!=', '')->count();
                } else {
                    $appInstalled += (clone $userQuery)->count();
                }

                if (Schema::hasColumn('tj_user_app', 'statut')) {
                    $registered += (clone $userQuery)->where('statut', 'yes')->count();
                    $activeUsers += (clone $userQuery)->where('statut', 'yes')->count();
                } else {
                    $registered += (clone $userQuery)->count();
                    $activeUsers += (clone $userQuery)->count();
                }

                if (Schema::hasColumn('tj_user_app', 'statut_nic')) {
                    $verified += (clone $userQuery)->where('statut_nic', 'yes')->count();
                } elseif (Schema::hasColumn('tj_user_app', 'status')) {
                    $verified += (clone $userQuery)->where('status', 'approved')->count();
                } else {
                    $verified += (clone $userQuery)->where('statut', 'yes')->count();
                }
            }

            if (Schema::hasTable('tj_conducteur')) {
                $driverQuery = DB::table('tj_conducteur')->whereIn('id', $referredUserIds);

                if (Schema::hasColumn('tj_conducteur', 'fcm_id')) {
                    $appInstalled += (clone $driverQuery)->whereNotNull('fcm_id')->where('fcm_id', '!=', '')->count();
                } elseif (Schema::hasColumn('tj_conducteur', 'device_id')) {
                    $appInstalled += (clone $driverQuery)->whereNotNull('device_id')->where('device_id', '!=', '')->count();
                } else {
                    $appInstalled += (clone $driverQuery)->count();
                }

                if (Schema::hasColumn('tj_conducteur', 'statut')) {
                    $registered += (clone $driverQuery)->where('statut', 'yes')->count();
                    $activeBusiness += (clone $driverQuery)->where('statut', 'yes')->count();
                    $activeUsers += (clone $driverQuery)->where('statut', 'yes')->count();
                } else {
                    $registered += (clone $driverQuery)->count();
                    $activeBusiness += (clone $driverQuery)->count();
                }

                if (Schema::hasColumn('tj_conducteur', 'is_verified')) {
                    $verified += (clone $driverQuery)->where('is_verified', 1)->count();
                } else {
                    $verified += (clone $driverQuery)->where('statut', 'yes')->count();
                }

                if (Schema::hasTable('tj_conducteur_categories')) {
                    $activeServices = DB::table('tj_conducteur_categories')
                        ->whereIn('driver_id', $referredUserIds)
                        ->distinct('driver_id')
                        ->count('driver_id');
                }
            }

            if (Schema::hasTable('tj_requete')) {
                $totalTransactions = DB::table('tj_requete')
                    ->whereIn('id_user_app', $referredUserIds)
                    ->count();
            }

            $earnings = $this->calculateReferralEarnings($driverId, $referredUserIds);
        }

        return [
            'referral_code' => $referralCode,
            'referral_link' => $referralLink,
            'total_referrals' => $totalReferrals,
            'app_installed' => $appInstalled,
            'registered' => $registered,
            'verified' => $verified,
            'active_business' => $activeBusiness,
            'active_services' => $activeServices,
            'total_transactions' => $totalTransactions,
            'earnings' => '₹' . number_format($earnings, 2),
            'wallet_balance' => '₹' . number_format($walletBalance, 2),
            'active_users' => $activeUsers,
        ];
    }

    private function buildDriverReferralUsers($driverId): array
    {
        if (!$driverId || !Schema::hasTable('referral')) {
            return [];
        }

        $driver = Schema::hasTable('tj_conducteur')
            ? DB::table('tj_conducteur')->where('id', $driverId)->first()
            : null;
        $referralCode = $this->resolveDriverReferralCode($driverId, $driver);

        $referrals = DB::table('referral')
            ->where('referral_by_id', (string) $driverId)
            ->orderByDesc('creer')
            ->limit(20)
            ->get();

        if ($referrals->isEmpty() && $referralCode && Schema::hasTable('tj_user_app') && Schema::hasColumn('tj_user_app', 'referral_code')) {
            $fallbackUsers = DB::table('tj_user_app')
                ->where('referral_code', $referralCode)
                ->orderByDesc('creer')
                ->limit(20)
                ->get();

            $users = [];
            foreach ($fallbackUsers as $user) {
                $users[] = $this->mapReferredUser(
                    (string) $user->id,
                    trim(($user->prenom ?? '') . ' ' . ($user->nom ?? '')),
                    'Customer',
                    $user->statut ?? 'pending',
                    $this->getReferralRewardAmount((string) $user->id)
                );
            }

            return $users;
        }

        $users = [];
        foreach ($referrals as $referral) {
            $userId = (string) ($referral->user_id ?? '');
            if ($userId === '') {
                continue;
            }

            $mapped = $this->findReferredUserProfile($userId);
            if ($mapped) {
                $mapped['amount'] = '₹' . number_format($this->getReferralRewardAmount($userId), 2);
                $users[] = $mapped;
            }
        }

        return $users;
    }

    private function findReferredUserProfile(string $userId): ?array
    {
        if (Schema::hasTable('tj_conducteur')) {
            $driver = DB::table('tj_conducteur')->where('id', $userId)->first();
            if ($driver) {
                $category = $driver->business_name ?? 'Cab Driver';
                if (Schema::hasTable('tj_conducteur_categories') && Schema::hasTable('tj_categorie_user')) {
                    $categoryRow = DB::table('tj_conducteur_categories')
                        ->join('tj_categorie_user', 'tj_conducteur_categories.category_id', '=', 'tj_categorie_user.id')
                        ->where('tj_conducteur_categories.driver_id', $userId)
                        ->select('tj_categorie_user.libelle')
                        ->first();
                    if ($categoryRow && !empty($categoryRow->libelle)) {
                        $category = $categoryRow->libelle;
                    }
                }

                return $this->mapReferredUser(
                    $userId,
                    trim(($driver->prenom ?? '') . ' ' . ($driver->nom ?? '')),
                    $category,
                    $driver->statut ?? 'pending',
                    0
                );
            }
        }

        if (Schema::hasTable('tj_user_app')) {
            $user = DB::table('tj_user_app')->where('id', $userId)->first();
            if ($user) {
                return $this->mapReferredUser(
                    $userId,
                    trim(($user->prenom ?? '') . ' ' . ($user->nom ?? '')),
                    'Customer',
                    $user->statut ?? 'pending',
                    0
                );
            }
        }

        return null;
    }

    private function mapReferredUser(string $userId, string $name, string $category, string $status, float $amount): array
    {
        $displayName = trim($name) !== '' ? trim($name) : 'User #' . $userId;
        $isActive = in_array(strtolower((string) $status), ['yes', 'active', 'approved', '1'], true);

        return [
            'id' => $userId,
            'name' => $displayName,
            'category' => $category,
            'status' => $isActive ? 'Active' : ucfirst((string) $status),
            'amount' => '₹' . number_format($amount, 2),
            'type' => strtolower($category) === 'customer' ? 'customer' : 'business',
        ];
    }

    private function getReferredUserIds($driverId, string $referralCode): array
    {
        $ids = [];

        if ($driverId && Schema::hasTable('referral')) {
            $ids = DB::table('referral')
                ->where('referral_by_id', (string) $driverId)
                ->pluck('user_id')
                ->filter()
                ->map(fn ($id) => (string) $id)
                ->values()
                ->all();
        }

        if (empty($ids) && $referralCode && Schema::hasTable('tj_user_app') && Schema::hasColumn('tj_user_app', 'referral_code')) {
            $ids = DB::table('tj_user_app')
                ->where('referral_code', $referralCode)
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->values()
                ->all();
        }

        return array_values(array_unique($ids));
    }

    private function resolveDriverReferralCode($driverId, $driver = null): string
    {
        if ($driverId && Schema::hasTable('referral')) {
            $driverReferral = DB::table('referral')->where('user_id', $driverId)->first();
            if ($driverReferral && !empty($driverReferral->referral_code)) {
                return $driverReferral->referral_code;
            }
        }

        if ($driver && !empty($driver->code_referral)) {
            return $driver->code_referral;
        }

        return 'FIIN' . ($driverId ? str_pad((string) $driverId, 4, '0', STR_PAD_LEFT) : '8829');
    }

    private function calculateReferralEarnings($driverId, array $referredUserIds): float
    {
        $earnings = 0.0;
        $rewardAmount = 50.0;

        if (Schema::hasTable('tj_settings')) {
            $settings = DB::table('tj_settings')->first();
            if ($settings && isset($settings->referral_amount)) {
                $rewardAmount = (float) $settings->referral_amount;
            }
        }

        if (Schema::hasTable('referral')) {
            $usedCount = DB::table('referral')
                ->where('referral_by_id', (string) $driverId)
                ->whereIn('user_id', $referredUserIds)
                ->where('code_used', 'true')
                ->count();
            $earnings += $usedCount * $rewardAmount;
        }

        if (Schema::hasTable('tj_conducteur_transaction') && $driverId) {
            $txnEarnings = DB::table('tj_conducteur_transaction')
                ->where('id_conducteur', (string) $driverId)
                ->where(function ($query) {
                    $query->where('payment_method', 'Referral')
                        ->orWhere('payment_method', 'like', '%Referral%');
                })
                ->sum(DB::raw('CAST(amount AS DECIMAL(10,2))'));
            $earnings += (float) $txnEarnings;
        }

        if (Schema::hasTable('tj_transaction') && $driverId) {
            $userTxnEarnings = DB::table('tj_transaction')
                ->where('id_user_app', (string) $driverId)
                ->where(function ($query) {
                    $query->where('payment_method', 'Referral')
                        ->orWhere('payment_method', 'like', '%Referral%');
                })
                ->sum(DB::raw('CAST(amount AS DECIMAL(10,2))'));
            $earnings += (float) $userTxnEarnings;
        }

        return $earnings;
    }

    private function getReferralRewardAmount(string $userId): float
    {
        $rewardAmount = 50.0;

        if (Schema::hasTable('tj_settings')) {
            $settings = DB::table('tj_settings')->first();
            if ($settings && isset($settings->referral_amount)) {
                $rewardAmount = (float) $settings->referral_amount;
            }
        }

        if (Schema::hasTable('referral')) {
            $referral = DB::table('referral')->where('user_id', $userId)->first();
            if ($referral && ($referral->code_used ?? '') === 'true') {
                return $rewardAmount;
            }
        }

        return 0.0;
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
