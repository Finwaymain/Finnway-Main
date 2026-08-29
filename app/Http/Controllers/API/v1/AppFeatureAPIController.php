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

        $referralCode = $this->resolveDriverReferralCode($driverId, $driver ?: $customer);
        $referralLink = 'https://fiinway.online/r/' . $referralCode;
        $walletBalance = (float)($driver->amount ?? $customer->amount ?? 0.0);

        $referredUsers = $this->getReferredUsersDetails($driverId, $referralCode);
        $totalReferrals = count($referredUsers);

        $appInstalled = 0;
        $registered = 0;
        $verified = 0;
        $activeUsers = 0;
        $activeBusiness = 0;
        $activeServices = 0;
        $totalTransactions = 0;
        $earnings = 0.0;

        foreach ($referredUsers as $ref) {
            $registered++;
            if (!empty($ref['fcm_id']) || !empty($ref['device_id']) || $ref['status_raw'] === 'yes') {
                $appInstalled++;
            }
            if ($ref['is_active']) {
                $activeUsers++;
                if ($ref['user_type'] === 'driver') {
                    $activeBusiness++;
                }
            }
            if ($ref['is_verified']) {
                $verified++;
            }
            if ($ref['user_type'] === 'driver' && Schema::hasTable('tj_conducteur_categories')) {
                $hasCat = DB::table('tj_conducteur_categories')->where('driver_id', $ref['id'])->exists();
                if ($hasCat) {
                    $activeServices++;
                }
            }
            if (Schema::hasTable('tj_requete')) {
                $userRides = DB::table('tj_requete')
                    ->where('id_user_app', $ref['id'])
                    ->orWhere('id_conducteur', $ref['id'])
                    ->count();
                $totalTransactions += $userRides;
            }
        }

        $earnings = $this->calculateReferralEarnings($driverId, array_column($referredUsers, 'id'));

        $aadharNumber = $customer->aadhar_number ?? $driver->aadhar_number ?? null;
        $hasSubmittedAadhar = !empty($aadharNumber);

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
            'aadhar_number' => $aadharNumber,
            'has_submitted_aadhar' => $hasSubmittedAadhar,
        ];
    }

    private function getReferredUsersDetails($driverId, string $referralCode): array
    {
        if (!$driverId) {
            return [];
        }

        $referralRows = [];
        if (Schema::hasTable('referral')) {
            $referralRows = DB::table('referral')
                ->where('referral_by_id', (string) $driverId)
                ->orWhere('referral_by_id', (int) $driverId)
                ->get();
        }

        $results = [];
        $seenUserKeys = [];

        foreach ($referralRows as $refRow) {
            $userId = (int) $refRow->user_id;
            if ($userId <= 0) continue;

            // Check common_user_base to accurately determine type
            $base = DB::table('common_user_base')->where('user_id', $userId)->first();
            $userType = ($base && $base->user_type === 'driver') ? 'driver' : 'customer';

            $userKey = $userType . '_' . $userId;
            if (isset($seenUserKeys[$userKey])) continue;
            $seenUserKeys[$userKey] = true;

            $profile = $this->fetchUserProfile($userId, $userType);
            if ($profile) {
                $results[] = $profile;
            }
        }

        return $results;
    }

    private function fetchUserProfile(int $userId, string $userType): ?array
    {
        if ($userType === 'driver' && Schema::hasTable('tj_conducteur')) {
            $driver = DB::table('tj_conducteur')->where('id', $userId)->first();
            if ($driver) {
                $category = 'Cab Driver';
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

                $isActive = in_array(strtolower((string)($driver->statut ?? '')), ['yes', 'active', '1'], true);
                $isVerified = ($driver->is_verified ?? 0) == 1 || $isActive;

                return [
                    'id' => (string) $userId,
                    'user_type' => 'driver',
                    'name' => trim(($driver->prenom ?? '') . ' ' . ($driver->nom ?? '')) ?: 'Driver #' . $userId,
                    'category' => $category,
                    'status' => $isActive ? 'Active' : 'Pending',
                    'status_raw' => $driver->statut ?? 'no',
                    'is_active' => $isActive,
                    'is_verified' => $isVerified,
                    'fcm_id' => $driver->fcm_id ?? null,
                    'device_id' => $driver->device_id ?? null,
                ];
            }
        }

        if (Schema::hasTable('tj_user_app')) {
            $user = DB::table('tj_user_app')->where('id', $userId)->first();
            if ($user) {
                $isActive = in_array(strtolower((string)($user->statut ?? '')), ['yes', 'active', '1'], true);
                $isVerified = strtolower((string)($user->statut_nic ?? '')) === 'yes' || $isActive;

                return [
                    'id' => (string) $userId,
                    'user_type' => 'customer',
                    'name' => trim(($user->prenom ?? '') . ' ' . ($user->nom ?? '')) ?: 'Customer #' . $userId,
                    'category' => 'Customer',
                    'status' => $isActive ? 'Active' : 'Pending',
                    'status_raw' => $user->statut ?? 'no',
                    'is_active' => $isActive,
                    'is_verified' => $isVerified,
                    'fcm_id' => $user->fcm_id ?? null,
                    'device_id' => $user->device_id ?? null,
                ];
            }
        }

        return null;
    }

    private function buildDriverReferralUsers($driverId): array
    {
        if (!$driverId) {
            return [];
        }

        $referralCode = $this->resolveDriverReferralCode($driverId);
        $referredUsers = $this->getReferredUsersDetails($driverId, $referralCode);

        $rewardAmount = 50.0;
        $settings = DB::table('tj_settings')->first();
        if ($settings && isset($settings->referral_amount) && (float)$settings->referral_amount > 0) {
            $rewardAmount = (float)$settings->referral_amount;
        }

        $list = [];
        foreach ($referredUsers as $user) {
            $list[] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'category' => $user['category'],
                'status' => $user['status'],
                'amount' => '₹' . number_format($rewardAmount, 2),
                'type' => $user['user_type'],
            ];
        }

        return $list;
    }

    private function resolveDriverReferralCode($driverId, $driver = null): string
    {
        if (!$driverId) {
            return \App\Services\ReferralCodeService::generateUniqueCode('driver');
        }

        return \App\Services\ReferralCodeService::getOrCreateReferralCode((int)$driverId, 'driver');
    }

    private function calculateReferralEarnings($driverId, array $referredUserIds): float
    {
        $rewardAmount = 50.0;
        if (Schema::hasTable('tj_settings')) {
            $settings = DB::table('tj_settings')->first();
            if ($settings && isset($settings->referral_amount) && (float)$settings->referral_amount > 0) {
                $rewardAmount = (float) $settings->referral_amount;
            }
        }

        $txnEarnings = 0.0;
        if (Schema::hasTable('tj_transaction') && $driverId) {
            $txnEarnings = (float) DB::table('tj_transaction')
                ->where('id_user_app', $driverId)
                ->where('payment_method', 'Referral Reward')
                ->sum('amount');
        }

        if ($txnEarnings > 0) {
            return $txnEarnings;
        }

        return count($referredUserIds) * $rewardAmount;
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

    /**
     * Submit Aadhaar for Partner / Joint Registration & Auto Approve
     */
    public function submitAadhar(Request $request)
    {
        $userId = $request->input('user_id') 
            ?? $request->input('driver_id') 
            ?? $request->input('id_user') 
            ?? $request->input('id_conducteur');

        $rawAadhar = $request->input('aadhar_number') 
            ?? $request->input('aadhar') 
            ?? $request->input('user_aadhar_number');

        $aadhar = trim(preg_replace('/[^0-9]/', '', (string)$rawAadhar));

        if (empty($aadhar) || strlen($aadhar) !== 12) {
            return response()->json([
                'success' => false,
                'status' => false,
                'res' => 'error',
                'message' => 'Please enter a valid 12-digit Aadhaar number.',
            ], 200);
        }

        $phone = $request->input('phone') ?? $request->input('mobile');

        // If userId is missing, try lookup by phone
        if (empty($userId) && !empty($phone) && Schema::hasTable('tj_user_app')) {
            $cleanPhone = preg_replace('/[^0-9]/', '', (string)$phone);
            if (strlen($cleanPhone) >= 10) {
                $last10 = substr($cleanPhone, -10);
                $userByPhone = DB::table('tj_user_app')->where('phone', 'like', "%{$last10}")->first();
                if ($userByPhone) {
                    $userId = $userByPhone->id;
                } else {
                    $driverByPhone = DB::table('tj_conducteur')->where('phone', 'like', "%{$last10}")->first();
                    if ($driverByPhone) {
                        $userId = $driverByPhone->id;
                    }
                }
            }
        }

        // Resolve current user's phone (last 10 digits) for accurate duplicate checking
        $currentPhoneLast10 = '';
        if (!empty($phone)) {
            $cleanPhone = preg_replace('/[^0-9]/', '', (string)$phone);
            if (strlen($cleanPhone) >= 10) {
                $currentPhoneLast10 = substr($cleanPhone, -10);
            }
        }
        if (empty($currentPhoneLast10) && !empty($userId)) {
            $uRow = DB::table('tj_user_app')->where('id', $userId)->first();
            if (!$uRow) {
                $uRow = DB::table('tj_conducteur')->where('id', $userId)->first();
            }
            if ($uRow && !empty($uRow->phone)) {
                $cleanPhone = preg_replace('/[^0-9]/', '', (string)$uRow->phone);
                if (strlen($cleanPhone) >= 10) {
                    $currentPhoneLast10 = substr($cleanPhone, -10);
                }
            }
        }

        // 1. Check for Duplicate Aadhaar in tj_user_app
        if (Schema::hasTable('tj_user_app')) {
            $duplicateUserQuery = DB::table('tj_user_app')
                ->where('aadhar_number', $aadhar);

            if (!empty($userId)) {
                $duplicateUserQuery->where('id', '!=', $userId);
            }

            $duplicateUser = $duplicateUserQuery->first();
            if ($duplicateUser) {
                $dupPhone = preg_replace('/[^0-9]/', '', (string)($duplicateUser->phone ?? ''));
                $dupLast10 = strlen($dupPhone) >= 10 ? substr($dupPhone, -10) : '';

                if (empty($currentPhoneLast10) || empty($dupLast10) || $dupLast10 !== $currentPhoneLast10) {
                    return response()->json([
                        'success' => false,
                        'status' => false,
                        'res' => 'error',
                        'message' => 'This Aadhaar card number is already registered with a different mobile number.',
                    ], 200);
                }
            }
        }

        // 2. Check for Duplicate Aadhaar in tj_conducteur
        if (Schema::hasTable('tj_conducteur')) {
            $duplicateDriverQuery = DB::table('tj_conducteur')
                ->where('aadhar_number', $aadhar);

            if (!empty($userId)) {
                $duplicateDriverQuery->where('id', '!=', $userId);
            }

            $duplicateDriver = $duplicateDriverQuery->first();
            if ($duplicateDriver) {
                $dupPhone = preg_replace('/[^0-9]/', '', (string)($duplicateDriver->phone ?? ''));
                $dupLast10 = strlen($dupPhone) >= 10 ? substr($dupPhone, -10) : '';

                if (empty($currentPhoneLast10) || empty($dupLast10) || $dupLast10 !== $currentPhoneLast10) {
                    return response()->json([
                        'success' => false,
                        'status' => false,
                        'res' => 'error',
                        'message' => 'This Aadhaar card number is already registered with a different mobile number.',
                    ], 200);
                }
            }
        }

        // 3. Update User App Table & Auto Approve
        if (!empty($userId) && Schema::hasTable('tj_user_app')) {
            $updateData = [
                'aadhar_number' => $aadhar,
                'statut' => 'yes',
            ];
            if (Schema::hasColumn('tj_user_app', 'kyc_status')) {
                $updateData['kyc_status'] = '1';
            }

            DB::table('tj_user_app')
                ->where('id', $userId)
                ->update($updateData);
        }

        // 4. Update Driver Table & Auto Approve
        if (!empty($userId) && Schema::hasTable('tj_conducteur')) {
            $updateDriverData = [
                'aadhar_number' => $aadhar,
                'statut' => 'yes',
            ];
            if (Schema::hasColumn('tj_conducteur', 'kyc_status')) {
                $updateDriverData['kyc_status'] = '1';
            }
            if (Schema::hasColumn('tj_conducteur', 'is_verified')) {
                $updateDriverData['is_verified'] = 1;
            }

            DB::table('tj_conducteur')
                ->where('id', $userId)
                ->update($updateDriverData);
        }

        return response()->json([
            'success' => true,
            'status' => true,
            'res' => 'success',
            'message' => 'Aadhaar verified successfully! Account approved.',
            'aadhar_number' => $aadhar,
        ], 200);
    }
}

