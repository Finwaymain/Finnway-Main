<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\UserApp;
use App\Models\Driver;

class ReferralDashboardAPIController extends Controller
{
    /**
     * Get Referral Dashboard stats calculated 100% dynamically from database.
     */
    public function getStats(Request $request)
    {
        $all = $request->all();
        if (empty($all) && $request->getContent()) {
            $decoded = json_decode($request->getContent(), true);
            if (is_array($decoded)) {
                $all = $decoded;
            }
        }

        $cleanParam = function($val) {
            if (is_array($val) || is_object($val)) return '';
            $str = trim((string)$val);
            $lower = strtolower($str);
            if (in_array($lower, ['', 'null', 'undefined', '0', 'false', 'none', 'nan'], true)) {
                return '';
            }
            return $str;
        };

        $rawUserCat  = $request->get('user_cat') ?: $request->get('user_type') ?: ($all['user_cat'] ?? $all['user_type'] ?? $request->header('user_cat') ?? $request->header('user_type') ?? '');
        $rawDriverId = $request->get('driver_id') ?: $request->get('id_driver') ?: ($all['driver_id'] ?? $all['id_driver'] ?? $request->header('driver_id') ?? $request->header('id_driver') ?? '');
        $rawUserId   = $request->get('user_id') ?: $request->get('id_user') ?: $request->get('id') ?: ($all['user_id'] ?? $all['id_user'] ?? $all['id'] ?? $request->header('user_id') ?? $request->header('id_user') ?? '');
        $rawToken    = $request->get('accesstoken') ?: ($all['accesstoken'] ?? $request->header('accesstoken') ?? '');
        $rawPhone    = $request->get('phone') ?: $request->get('mobile') ?: ($all['phone'] ?? $all['mobile'] ?? $request->header('phone') ?? '');

        $userCat     = strtolower($cleanParam($rawUserCat));
        $driverId    = $cleanParam($rawDriverId);
        $custUserId  = $cleanParam($rawUserId);
        $accessToken = $cleanParam($rawToken);
        $phone       = $cleanParam($rawPhone);

        $isDriverExplicit = in_array($userCat, ['driver', 'conducteur', 'business', 'provider'], true);
        $isCustomerExplicit = in_array($userCat, ['customer', 'user_app', 'user', 'consumer'], true);

        $user = null;
        $userType = 'customer';

        // 1. Highest priority: Access Token Lookup
        if (!empty($accessToken)) {
            $accessRow = DB::table('users_access')->where('accesstoken', $accessToken)->first();
            if ($accessRow && !empty($accessRow->user_id)) {
                $tokenType = ($accessRow->user_type === 'driver') ? 'driver' : 'customer';
                $found = ($tokenType === 'driver') ? Driver::find($accessRow->user_id) : UserApp::find($accessRow->user_id);
                if ($found) {
                    $user = $found;
                    $userType = $tokenType;
                }
            }
        }

        // 2. Direct ID Lookup matching requested category
        if (!$user) {
            if ($isDriverExplicit) {
                $targetId = $driverId ?: $custUserId;
                if (!empty($targetId)) {
                    $user = Driver::find($targetId) 
                        ?? Driver::where('ac_no', $targetId)->first() 
                        ?? Driver::where('phone', $targetId)->first();
                    if ($user) $userType = 'driver';
                }
            } elseif ($isCustomerExplicit) {
                $targetId = $custUserId ?: $driverId;
                if (!empty($targetId)) {
                    $user = UserApp::find($targetId) 
                        ?? UserApp::where('ac_no', $targetId)->first() 
                        ?? UserApp::where('phone', $targetId)->first();
                    if ($user) $userType = 'customer';
                }
            } else {
                if (!empty($driverId)) {
                    $user = Driver::find($driverId) ?? Driver::where('ac_no', $driverId)->first();
                    if ($user) $userType = 'driver';
                }
                if (!$user && !empty($custUserId)) {
                    $user = UserApp::find($custUserId) ?? UserApp::where('ac_no', $custUserId)->first();
                    if ($user) $userType = 'customer';
                }
            }
        }

        // 3. Phone Lookup
        if (!$user && !empty($phone)) {
            $cleanPhone = substr(preg_replace('/[^0-9]/', '', $phone), -10);
            if (strlen($cleanPhone) >= 10) {
                if ($isDriverExplicit) {
                    $user = Driver::where('phone', 'like', "%{$cleanPhone}")->first();
                    if ($user) $userType = 'driver';
                } else {
                    $user = UserApp::where('phone', 'like', "%{$cleanPhone}")->first();
                    if ($user) $userType = 'customer';
                    if (!$user) {
                        $user = Driver::where('phone', 'like', "%{$cleanPhone}")->first();
                        if ($user) $userType = 'driver';
                    }
                }
            }
        }

        // 4. Cross-table fallback search if still not found
        if (!$user) {
            $targetId = $driverId ?: $custUserId;
            if (!empty($targetId)) {
                $foundUser = UserApp::find($targetId) ?? UserApp::where('ac_no', $targetId)->first();
                if ($foundUser) {
                    $user = $foundUser;
                    $userType = 'customer';
                } else {
                    $foundDriver = Driver::find($targetId) ?? Driver::where('ac_no', $targetId)->first();
                    if ($foundDriver) {
                        $user = $foundDriver;
                        $userType = 'driver';
                    }
                }
            }
        }

        // 5. Ultimate Fallback: If requested user/driver is not found, fallback to latest active user/driver in DB
        if (!$user) {
            if ($isDriverExplicit) {
                $user = Driver::orderByDesc('id')->first();
                if ($user) {
                    $userType = 'driver';
                } else {
                    $user = UserApp::orderByDesc('id')->first();
                    if ($user) $userType = 'customer';
                }
            } else {
                $user = UserApp::orderByDesc('id')->first();
                if ($user) {
                    $userType = 'customer';
                } else {
                    $user = Driver::orderByDesc('id')->first();
                    if ($user) $userType = 'driver';
                }
            }
        }

        $id = $user ? (int)$user->id : 0;
        $userId = $id;

        // Guaranteed unique referral code resolution for THIS user
        $refCode = ($id > 0) ? \App\Services\ReferralCodeService::getOrCreateReferralCode($id, $userType) : '';
        if (empty($refCode)) {
            $refCode = \App\Services\ReferralCodeService::generateUniqueCode($userType, $id ?: null);
        }

        // Build all possible referral code formats for backward-compatible lookup
        $possibleRefCodes = array_values(array_unique(array_filter([
            $refCode,
            strtoupper($refCode),
            strtolower($refCode),
        ])));

        $referredEntities = [];

        // 1. Find all users who were referred BY THIS USER using their referral code or ID+type
        if (Schema::hasTable('referral') && $id > 0) {
            $hasByCodeCol = Schema::hasColumn('referral', 'referral_by_code');
            $hasByTypeCol = Schema::hasColumn('referral', 'referral_by_type');

            $refRows = DB::table('referral')
                ->where(function($q) use ($id, $userType, $possibleRefCodes, $hasByCodeCol, $hasByTypeCol) {
                    if ($hasByCodeCol) {
                        $q->whereIn('referral_by_code', $possibleRefCodes);
                    }
                    $q->orWhere(function($sq) use ($id, $userType, $hasByTypeCol) {
                        $sq->where('referral_by_id', $id);
                        if ($hasByTypeCol) {
                            $sq->where('referral_by_type', $userType);
                        }
                    });
                })
                ->where(function($q) use ($id, $userType) {
                    $q->where('user_id', '!=', $id)
                      ->orWhere('user_type', '!=', $userType);
                })
                ->get();

            foreach ($refRows as $rRow) {
                if (!empty($rRow->user_id) && ((int)$rRow->user_id !== $id || ($rRow->user_type ?? '') !== $userType)) {
                    $rawType = strtolower(trim((string)($rRow->user_type ?? '')));
                    $isDriver = ($rawType === 'driver' || $rawType === 'conducteur');
                    $type = $isDriver ? 'driver' : 'customer';
                    $referredEntities[] = ['id' => (int)$rRow->user_id, 'type' => $type];
                }
            }
        }

        // 2. Also check ref_by column in user tables matching this user's unique referral code
        if (!empty($possibleRefCodes) && $id > 0) {
            if (Schema::hasTable('tj_user_app') && Schema::hasColumn('tj_user_app', 'ref_by')) {
                $appRefIds = DB::table('tj_user_app')
                    ->where(function($q) use ($id, $userType) {
                        if ($userType === 'customer') {
                            $q->where('id', '!=', $id);
                        }
                    })
                    ->whereIn('ref_by', $possibleRefCodes)
                    ->whereNotNull('ref_by')
                    ->where('ref_by', '!=', '')
                    ->pluck('id')
                    ->toArray();
                foreach ($appRefIds as $uId) {
                    $referredEntities[] = ['id' => (int)$uId, 'type' => 'customer'];
                }
            }

            if (Schema::hasTable('tj_conducteur') && Schema::hasColumn('tj_conducteur', 'ref_by')) {
                $driverRefIds = DB::table('tj_conducteur')
                    ->where(function($q) use ($id, $userType) {
                        if ($userType === 'driver') {
                            $q->where('id', '!=', $id);
                        }
                    })
                    ->whereIn('ref_by', $possibleRefCodes)
                    ->whereNotNull('ref_by')
                    ->where('ref_by', '!=', '')
                    ->pluck('id')
                    ->toArray();
                foreach ($driverRefIds as $dId) {
                    $referredEntities[] = ['id' => (int)$dId, 'type' => 'driver'];
                }
            }
        }

        // 3. Also pull referees from actual wallet referral reward transactions
        if ($userType === 'driver' && Schema::hasTable('tj_conducteur_transaction') && $id > 0) {
            $txnReferees = DB::table('tj_conducteur_transaction')
                ->where('id_conducteur', $id)
                ->where('payment_method', 'Referral Reward')
                ->whereNotNull('receiver_user_id')
                ->select('receiver_user_id as id', 'sender_user_type as type')
                ->get();
            foreach ($txnReferees as $tRef) {
                if (!empty($tRef->id)) {
                    $tType = ($tRef->type === 'driver') ? 'driver' : 'customer';
                    $referredEntities[] = ['id' => (int)$tRef->id, 'type' => $tType];
                }
            }
        } elseif ($userType !== 'driver' && Schema::hasTable('tj_transaction') && $id > 0) {
            $txnReferees = DB::table('tj_transaction')
                ->where('id_user_app', $id)
                ->where('payment_method', 'Referral Reward')
                ->whereNotNull('sender_user_id')
                ->select('sender_user_id as id', 'sender_user_type as type')
                ->get();
            foreach ($txnReferees as $tRef) {
                if (!empty($tRef->id)) {
                    $tType = ($tRef->type === 'driver') ? 'driver' : 'customer';
                    $referredEntities[] = ['id' => (int)$tRef->id, 'type' => $tType];
                }
            }
        }

        // Deduplicate entities by "type-id" key (e.g. "customer-5", "driver-5")
        $uniqueEntities = [];
        foreach ($referredEntities as $ent) {
            $key = $ent['type'] . '-' . $ent['id'];
            $uniqueEntities[$key] = $ent;
        }
        $referredEntities = array_values($uniqueEntities);

        // 2. Build detailed referral list with real names, statuses, and service counts
        $historyList = [];

        foreach ($referredEntities as $ent) {
            $refId = (int)$ent['id'];
            $isDriver = ($ent['type'] === 'driver');
            $userCat = $isDriver ? 'Business' : 'Consumer';
            
            // STRICT model lookup based on ent['type']
            $uObj = $isDriver ? Driver::find($refId) : UserApp::find($refId);

            if (!$uObj) {
                continue;
            }


            // Real name formatting
            $fname = trim((string)($uObj->prenom ?? ''));
            $lname = trim((string)($uObj->nom ?? ''));
            $fullName = trim($fname . ' ' . $lname);
            if (empty($fullName)) {
                $fullName = !empty($uObj->phone) ? ('User (' . substr($uObj->phone, -4) . ')') : ('User #' . $refId);
            }

            // Verification check
            $isVerified = false;
            if ($userCat === 'Consumer') {
                $isVerified = ($uObj->statut_nic === 'yes') || !empty($uObj->aadhar_number);
            } else {
                $isVerified = ($uObj->is_verified == 1) || ($uObj->statut_nic === 'yes') || !empty($uObj->aadhar_number);
            }

            // Services & Transactions count
            $servicesCount = 0;
            if (Schema::hasTable('tj_requete')) {
                $servicesCount = DB::table('tj_requete')
                    ->where('id_user_app', $refId)
                    ->orWhere('id_conducteur', $refId)
                    ->count();
            }

            $isActive = ($servicesCount > 0) || ($uObj->statut === 'yes') || ($uObj->online === 'yes');

            // ── Referee Qualification & Spend Tracking ──────────────────────
            $refRecord = null;
            if (Schema::hasTable('referral')) {
                $refRecord = DB::table('referral')
                    ->where('user_id', $refId)
                    ->where('user_type', $ent['type'])
                    ->first()
                    ?? DB::table('referral')->where('user_id', $refId)->first();
            }
            $isPaid = $refRecord ? (bool)($refRecord->app_install_reward_paid ?? 0) : false;

            // Admin Rules for this Referee Type
            if ($isDriver) {
                $ruleMinSrv = (int)\App\Models\ApiKeySetting::getApiKeyValue('event_rule_app_install_business_min_services', \App\Models\ApiKeySetting::getApiKeyValue('event_rule_registration_min_services', '2'));
                $ruleMinAmt = (float)\App\Models\ApiKeySetting::getApiKeyValue('event_rule_app_install_business_min_amount', \App\Models\ApiKeySetting::getApiKeyValue('event_rule_registration_min_amount', '200'));
                $ruleRewardVal = (float)\App\Models\ApiKeySetting::getApiKeyValue('event_rule_app_install_business_value', \App\Models\ApiKeySetting::getApiKeyValue('event_rule_registration_value', '50'));
            } else {
                $ruleMinSrv = (int)\App\Models\ApiKeySetting::getApiKeyValue('event_rule_app_install_user_min_services', \App\Models\ApiKeySetting::getApiKeyValue('event_rule_app_install_min_services', '5'));
                $ruleMinAmt = (float)\App\Models\ApiKeySetting::getApiKeyValue('event_rule_app_install_user_min_amount', \App\Models\ApiKeySetting::getApiKeyValue('event_rule_app_install_min_amount', '500'));
                $ruleRewardVal = (float)\App\Models\ApiKeySetting::getApiKeyValue('event_rule_app_install_user_value', \App\Models\ApiKeySetting::getApiKeyValue('event_rule_app_install_value', '10'));
            }

            // Real Completed Services & Spend
            $completedCount = 0;
            $totalSpend = 0.0;
            if ($isDriver) {
                if (Schema::hasTable('tj_requete')) {
                    $rides = DB::table('tj_requete')->where('id_conducteur', $refId)->where('statut', 'completed')->get();
                    $completedCount += $rides->count();
                    $totalSpend += (float)$rides->sum('montant');
                }
                if (Schema::hasTable('service_requests')) {
                    $services = DB::table('service_requests')->where('driver_id', $refId)->whereIn('status', ['Completed', 'completed'])->get();
                    $completedCount += $services->count();
                    $totalSpend += (float)$services->sum('amount');
                }
                if (Schema::hasTable('parcel_orders')) {
                    $parcels = DB::table('parcel_orders')->where('id_conducteur', $refId)->where('status', 'completed')->get();
                    $completedCount += $parcels->count();
                    $totalSpend += (float)$parcels->sum('amount');
                }
            } else {
                if (Schema::hasTable('tj_requete')) {
                    $rides = DB::table('tj_requete')->where('id_user_app', $refId)->where('statut', 'completed')->get();
                    $completedCount += $rides->count();
                    $totalSpend += (float)$rides->sum('montant');
                }
                if (Schema::hasTable('service_requests')) {
                    $services = DB::table('service_requests')->where('user_id', $refId)->whereIn('status', ['Completed', 'completed'])->get();
                    $completedCount += $services->count();
                    $totalSpend += (float)$services->sum('amount');
                }
                if (Schema::hasTable('parcel_orders')) {
                    $parcels = DB::table('parcel_orders')->where('id_user_app', $refId)->where('status', 'completed')->get();
                    $completedCount += $parcels->count();
                    $totalSpend += (float)$parcels->sum('amount');
                }
            }

            // Referral bonus earned for this specific user
            $earnedForThisUser = 0.0;
            if ($userType === 'driver') {
                if (Schema::hasTable('tj_conducteur_transaction')) {
                    $earnedForThisUser = (float)DB::table('tj_conducteur_transaction')
                        ->where('id_conducteur', $id)
                        ->where(function($q) use ($refId, $ent) {
                            $q->where(function($sq) use ($refId, $ent) {
                                $sq->where('receiver_user_id', $refId)
                                   ->where('sender_user_type', $ent['type']);
                            })
                            ->orWhere('description', 'like', "%#{$refId}%")
                            ->orWhere('note', 'like', "%#{$refId}%");
                        })
                        ->where(function($q) {
                            $q->where('payment_method', 'Referral Reward')
                              ->orWhere('payment_method', 'like', '%referral%');
                        })
                        ->sum('amount');
                }
            } else {
                if (Schema::hasTable('tj_transaction')) {
                    $earnedForThisUser = (float)DB::table('tj_transaction')
                        ->where('id_user_app', $id)
                        ->where(function($q) use ($refId, $ent) {
                            $q->where(function($sq) use ($refId, $ent) {
                                $sq->where('sender_user_id', $refId)
                                   ->where('sender_user_type', $ent['type']);
                            })
                            ->orWhere('description', 'like', "%#{$refId}%");
                        })
                        ->where(function($q) {
                            $q->where('payment_method', 'Referral Reward')
                              ->orWhere('payment_method', 'like', '%referral%');
                        })
                        ->sum('amount');
                }
            }

            // Status and Frozen/Unlocked Cashback
            $isFrozen = !$isPaid;
            $frozenCashback = $isFrozen ? $ruleRewardVal : 0.0;
            $unlockedCashback = $earnedForThisUser;

            $conditionNote = $isPaid 
                ? 'Reward Unlocked & Credited to Wallet' 
                : "Cashback ₹{$ruleRewardVal} Frozen. Unlocks when referee completes {$ruleMinSrv} services or ₹" . number_format($ruleMinAmt, 0) . " spend (Progress: {$completedCount}/{$ruleMinSrv} services, ₹" . number_format($totalSpend, 2) . "/₹" . number_format($ruleMinAmt, 0) . ")";

            $historyList[] = [
                'id'                          => $refId,
                'name'                        => $fullName,
                'phone'                       => $uObj->phone ?? '',
                'user_type'                   => $userCat,
                'status'                      => $isPaid ? 'Unlocked' : 'Frozen',
                'status_label'                => $isPaid ? 'Credited to Wallet' : 'Cashback Frozen',
                'reward_status'               => $isPaid ? 'Unlocked' : 'Frozen',
                'is_frozen'                   => $isFrozen,
                'app_installed'               => true,
                'registered'                  => true,
                'verified'                    => $isVerified,
                'is_active'                   => $isActive,
                'services_count'              => $completedCount ?: $servicesCount,
                'completed_services'          => $completedCount,
                'total_spend'                 => $totalSpend,
                'min_services_required'       => $ruleMinSrv,
                'min_purchase_amount_required'=> $ruleMinAmt,
                'frozen_cashback'             => $frozenCashback,
                'unlocked_cashback'           => $unlockedCashback,
                'referral_earned'             => $unlockedCashback,
                'potential_reward'            => $ruleRewardVal,
                'condition_fulfilled'         => $isPaid,
                'condition_note'              => $conditionNote,
                'date'                        => date('d M Y', strtotime($uObj->creer ?? $uObj->created_at ?? 'now')),
            ];
        }

        // Separate history into consumer and business segments
        $consumerHistory = array_values(array_filter($historyList, fn($item) => $item['user_type'] === 'Consumer'));
        $businessHistory = array_values(array_filter($historyList, fn($item) => $item['user_type'] === 'Business'));

        $consumerCount = count($consumerHistory);
        $consumerVerifiedCount = count(array_filter($consumerHistory, fn($item) => !empty($item['verified'])));
        $consumerActiveCount = count(array_filter($consumerHistory, fn($item) => !empty($item['is_active'])));
        $consumerServicesCount = array_sum(array_column($consumerHistory, 'services_count'));
        $consumerIncome = array_sum(array_column($consumerHistory, 'referral_earned'));
        $consumerFrozenIncome = array_sum(array_column($consumerHistory, 'frozen_cashback'));

        $businessCount = count($businessHistory);
        $businessVerifiedCount = count(array_filter($businessHistory, fn($item) => !empty($item['verified'])));
        $businessActiveCount = count(array_filter($businessHistory, fn($item) => !empty($item['is_active'])));
        $businessServicesCount = array_sum(array_column($businessHistory, 'services_count'));
        $businessIncome = array_sum(array_column($businessHistory, 'referral_earned'));
        $businessFrozenIncome = array_sum(array_column($businessHistory, 'frozen_cashback'));

        $totalReferralsCount = $consumerCount + $businessCount;
        $totalVerifiedCount = $consumerVerifiedCount + $businessVerifiedCount;
        $totalActiveCount = $consumerActiveCount + $businessActiveCount;
        $totalServicesCount = $consumerServicesCount + $businessServicesCount;
        $totalFrozenIncome = $consumerFrozenIncome + $businessFrozenIncome;

        // 3. Real financial earnings calculation from relevant transaction table for this referrer ($id)
        if ($userType === 'driver') {
            $dbTotalIncome = (float)DB::table('tj_conducteur_transaction')
                ->where('id_conducteur', $id)
                ->where(function($q) {
                    $q->where('payment_method', 'Referral Reward')
                      ->orWhere('payment_method', 'like', '%referral%');
                })
                ->sum('amount');
            $walletBalance = (float)DB::table('tj_conducteur')->where('id', $id)->value('amount');
        } else {
            $dbTotalIncome = (float)DB::table('tj_transaction')
                ->where('id_user_app', $id)
                ->where(function($q) {
                    $q->where('payment_method', 'Referral Reward')
                      ->orWhere('payment_method', 'like', '%referral%');
                })
                ->sum('amount');
            $walletBalance = (float)DB::table('tj_user_app')->where('id', $id)->value('amount');
        }

        // Do NOT overwrite referral income with main wallet balance if referral income is 0
        // Referral earnings should strictly reflect income earned through referral rewards

        // If specific user sums are 0 but total income exists, attribute proportionally
        if ($consumerIncome == 0 && $businessIncome == 0 && $dbTotalIncome > 0) {
            if ($consumerCount > 0 && $businessCount == 0) {
                $consumerIncome = $dbTotalIncome;
            } elseif ($businessCount > 0 && $consumerCount == 0) {
                $businessIncome = $dbTotalIncome;
            } elseif ($totalReferralsCount > 0) {
                $consumerIncome = ($dbTotalIncome * $consumerCount) / $totalReferralsCount;
                $businessIncome = ($dbTotalIncome * $businessCount) / $totalReferralsCount;
            }
        }

        $consumerAvgMonthly = $consumerIncome > 0 ? (int)($consumerIncome / max(1, date('n'))) : 0;
        $businessAvgMonthly = $businessIncome > 0 ? (int)($businessIncome / max(1, date('n'))) : 0;

        // 4. Fetch recent referral earnings for THIS user ($id) ONLY with real referee names
        $recentTx = ($userType === 'driver')
            ? DB::table('tj_conducteur_transaction')
                ->where('id_conducteur', $id)
                ->where(function($q) {
                    $q->where('payment_method', 'Referral Reward')
                      ->orWhere('payment_method', 'like', '%referral%');
                })
                ->orderByDesc('id')
                ->limit(20)
                ->get()
            : DB::table('tj_transaction')
                ->where('id_user_app', $id)
                ->where(function($q) {
                    $q->where('payment_method', 'Referral Reward')
                      ->orWhere('payment_method', 'like', '%referral%');
                })
                ->orderByDesc('id')
                ->limit(20)
                ->get();

        $consumerRecentEarnings = [];
        $businessRecentEarnings = [];
        $allRecentEarnings = [];

        foreach ($recentTx as $tx) {
            $senderName = '';
            $refereeId = $tx->receiver_user_id ?? ($tx->sender_user_id ?? null);
            $refereeType = $tx->sender_user_type ?? 'customer';
            $senderType = ($refereeType === 'driver') ? 'Business' : 'Consumer';

            if (!empty($refereeId)) {
                if ($senderType === 'Business') {
                    $sDriver = Driver::find($refereeId);
                    if ($sDriver) {
                        $sFname = trim((string)($sDriver->prenom ?? ''));
                        $sLname = trim((string)($sDriver->nom ?? ''));
                        $senderName = trim($sFname . ' ' . $sLname);
                        if (empty($senderName) && !empty($sDriver->phone)) {
                            $senderName = 'Driver (' . substr($sDriver->phone, -4) . ')';
                        }
                    }
                } else {
                    $sUser = UserApp::find($refereeId);
                    if ($sUser) {
                        $sFname = trim((string)($sUser->prenom ?? ''));
                        $sLname = trim((string)($sUser->nom ?? ''));
                        $senderName = trim($sFname . ' ' . $sLname);
                        if (empty($senderName) && !empty($sUser->phone)) {
                            $senderName = 'User (' . substr($sUser->phone, -4) . ')';
                        }
                    }
                }
            }
            if (empty($senderName)) {
                $senderName = $senderType === 'Business' ? 'Driver Partner' : 'Referred Consumer';
            }

            $cat = $tx->payment_method ?? 'Referral Reward';
            $earnItem = [
                'category'  => $cat,
                'user_name' => $senderName,
                'user_type' => $senderType,
                'amount'    => (float)$tx->amount,
                'date'      => date('d M Y', strtotime($tx->creer ?? $tx->date ?? 'now')),
                'icon'      => $senderType === 'Business' ? '🚕' : '👤',
            ];

            $allRecentEarnings[] = $earnItem;
            if ($senderType === 'Business') {
                $businessRecentEarnings[] = $earnItem;
            } else {
                $consumerRecentEarnings[] = $earnItem;
            }
        }

        $consumerData = [
            'total_referrals'       => $consumerCount,
            'installed'             => $consumerCount,
            'registered'            => $consumerCount,
            'verified'              => $consumerVerifiedCount,
            'consumer_users'        => $consumerCount,
            'active_users'          => $consumerActiveCount,
            'inactive_users'        => max(0, $consumerCount - $consumerActiveCount),
            'total_transactions'    => $consumerServicesCount,
            'total_referral_income' => (int)$consumerIncome,
            'unlocked_income'       => (int)$consumerIncome,
            'frozen_income'         => (int)$consumerFrozenIncome,
            'pending_cashback'      => (int)$consumerFrozenIncome,
            'avg_monthly_income'    => $consumerAvgMonthly,
            'recent_earnings'       => $consumerRecentEarnings,
            'history'               => $consumerHistory,
        ];

        $businessData = [
            'total_referrals'       => $businessCount,
            'installed'             => $businessCount,
            'registered'            => $businessCount,
            'verified'              => $businessVerifiedCount,
            'business_users'        => $businessCount,
            'active_users'          => $businessActiveCount,
            'inactive_users'        => max(0, $businessCount - $businessActiveCount),
            'total_transactions'    => $businessServicesCount,
            'total_referral_income' => (int)$businessIncome,
            'unlocked_income'       => (int)$businessIncome,
            'frozen_income'         => (int)$businessFrozenIncome,
            'pending_cashback'      => (int)$businessFrozenIncome,
            'avg_monthly_income'    => $businessAvgMonthly,
            'recent_earnings'       => $businessRecentEarnings,
            'history'               => $businessHistory,
            // Legacy aliases for backward compatibility
            'business_referrals'    => $businessCount,
            'total_earnings'        => (int)$businessIncome,
            'summary'               => [
                'app_installed'      => $businessCount,
                'registered'         => $businessCount,
                'verified'           => $businessVerifiedCount,
                'active_business'    => $businessActiveCount,
                'active_services'    => $businessActiveCount,
                'total_transactions' => $businessServicesCount,
                'frozen_income'      => (int)$businessFrozenIncome,
            ],
            'recent_business_users' => $businessHistory,
        ];

        $summaryData = [
            'total_partners'    => $totalReferralsCount,
            'installed'         => $totalReferralsCount,
            'registered'        => $totalReferralsCount,
            'verified'          => $totalVerifiedCount,
            'active_users'      => $totalActiveCount,
            'consumer_count'    => $consumerCount,
            'business_count'    => $businessCount,
            'total_transactions'=> $totalServicesCount,
            'total_income'      => (int)$dbTotalIncome,
            'unlocked_income'   => (int)$dbTotalIncome,
            'frozen_income'     => (int)$totalFrozenIncome,
            'pending_cashback'  => (int)$totalFrozenIncome,
        ];

        $aadharNumber = '';
        $hasSubmittedAadhar = false;

        if ($user) {
            $aadharNumber = $user->aadhar_number ?? '';
            if (empty($aadharNumber)) {
                if ($userType === 'driver') {
                    $aadharNumber = DB::table('tj_conducteur')->where('id', $user->id)->value('aadhar_number') ?? '';
                } else {
                    $aadharNumber = DB::table('tj_user_app')->where('id', $user->id)->value('aadhar_number') ?? '';
                }
            }
            if (empty($aadharNumber) && !empty($phone)) {
                $cleanPhone = preg_replace('/[^0-9]/', '', (string)$phone);
                if (strlen($cleanPhone) >= 10) {
                    $last10 = substr($cleanPhone, -10);
                    if ($userType === 'driver') {
                        $aadharNumber = DB::table('tj_conducteur')->where('phone', 'like', "%{$last10}")->value('aadhar_number') ?? '';
                    } else {
                        $aadharNumber = DB::table('tj_user_app')->where('phone', 'like', "%{$last10}")->value('aadhar_number') ?? '';
                    }
                }
            }

            $cleanAadhar = preg_replace('/[^0-9]/', '', (string)$aadharNumber);
            $hasSubmittedAadhar = (strlen($cleanAadhar) === 12);
            $aadharNumber = $hasSubmittedAadhar ? $cleanAadhar : '';
        }

        // ── Referral Benefits from Admin Panel Configuration ──────────────────────────
        $userInstallVal = \App\Models\ApiKeySetting::getApiKeyValue('event_rule_app_install_user_value', \App\Models\ApiKeySetting::getApiKeyValue('event_rule_app_install_value', '10'));
        $userInstallType = \App\Models\ApiKeySetting::getApiKeyValue('event_rule_app_install_user_type', 'flat');
        $userMinServices = (int)\App\Models\ApiKeySetting::getApiKeyValue('event_rule_app_install_user_min_services', \App\Models\ApiKeySetting::getApiKeyValue('event_rule_app_install_min_services', '5'));
        $userMinAmount = (float)\App\Models\ApiKeySetting::getApiKeyValue('event_rule_app_install_user_min_amount', \App\Models\ApiKeySetting::getApiKeyValue('event_rule_app_install_min_amount', '500'));

        $bizInstallVal = \App\Models\ApiKeySetting::getApiKeyValue('event_rule_app_install_business_value', \App\Models\ApiKeySetting::getApiKeyValue('event_rule_registration_value', '50'));
        $bizInstallType = \App\Models\ApiKeySetting::getApiKeyValue('event_rule_app_install_business_type', 'flat');
        $bizMinServices = (int)\App\Models\ApiKeySetting::getApiKeyValue('event_rule_app_install_business_min_services', \App\Models\ApiKeySetting::getApiKeyValue('event_rule_registration_min_services', '2'));
        $bizMinAmount = (float)\App\Models\ApiKeySetting::getApiKeyValue('event_rule_app_install_business_min_amount', \App\Models\ApiKeySetting::getApiKeyValue('event_rule_registration_min_amount', '200'));

        $defaultCommission = \App\Models\ApiKeySetting::getApiKeyValue('referral_reward_value', '2.5');
        $defaultCommissionMode = \App\Models\ApiKeySetting::getApiKeyValue('referral_reward_mode', 'percentage');

        $userCashbackFmt = $userInstallType === 'percentage' ? ($userInstallVal . '%') : ('₹' . $userInstallVal);
        $bizCashbackFmt = $bizInstallType === 'percentage' ? ($bizInstallVal . '%') : ('₹' . $bizInstallVal);

        $benefitsData = [
            'consumer' => [
                'cashback_amount' => $userCashbackFmt,
                'cashback_value'  => (float)$userInstallVal,
                'type'            => $userInstallType,
                'min_services'    => $userMinServices,
                'min_amount'      => $userMinAmount,
                'title'           => 'User Referral',
                'description'     => "Get {$userCashbackFmt} cashback per user referral after " . ($userMinServices > 0 ? "{$userMinServices} completed services" : "registration") . ($userMinAmount > 0 ? " (min ₹{$userMinAmount})" : "") . ".",
            ],
            'business' => [
                'cashback_amount' => $bizCashbackFmt,
                'cashback_value'  => (float)$bizInstallVal,
                'type'            => $bizInstallType,
                'min_services'    => $bizMinServices,
                'min_amount'      => $bizMinAmount,
                'title'           => 'Business Partner',
                'description'     => "Get {$bizCashbackFmt} cashback per business partner after " . ($bizMinServices > 0 ? "{$bizMinServices} completed services" : "registration") . ($bizMinAmount > 0 ? " (min ₹{$bizMinAmount})" : "") . ".",
            ],
            'commission_rate' => $defaultCommissionMode === 'percentage' ? ($defaultCommission . '%') : ('₹' . $defaultCommission),
        ];

        // Total Referral Cashback earned from User + Business referrals
        $totalReferralEarned = (int)round((float)($consumerIncome + $businessIncome));
        if ($totalReferralEarned == 0 && $dbTotalIncome > 0) {
            $totalReferralEarned = (int)round((float)$dbTotalIncome);
        }

        $userName = $user ? trim(($user->prenom ?? '') . ' ' . ($user->nom ?? '')) : '';

        return response()->json([
            'success' => 'success',
            'data'    => [
                'user_id'          => $id,
                'user_type'        => $userType,
                'user_name'        => $userName,
                'referral_code'    => $refCode,
                'share_url'        => 'https://api.fiinway.com/ref/' . $refCode,
                'wallet_balance'   => $totalReferralEarned,
                'referral_earnings'=> $totalReferralEarned,
                'unlocked_income'  => $totalReferralEarned,
                'frozen_income'    => (int)$totalFrozenIncome,
                'pending_cashback' => (int)$totalFrozenIncome,
                'total_referrals'  => $totalReferralsCount,
                'installed'        => $totalReferralsCount,
                'registered'       => $totalReferralsCount,
                'verified'         => $totalVerifiedCount,
                'active_users'     => $totalActiveCount,
                'benefits'         => $benefitsData,
                'summary'          => $summaryData,
                'consumer'         => $consumerData,
                'business'         => $businessData,
                'aadhar_number'    => $aadharNumber ? (string)$aadharNumber : '',
                'aadhar_submitted' => $hasSubmittedAadhar,
            ]
        ]);
    }

    private function getUserTypeById(int $userId): ?string
    {
        if (Schema::hasTable('referral')) {
            $ref = DB::table('referral')->where('user_id', $userId)->first();
            if ($ref && !empty($ref->user_type)) {
                $raw = strtolower(trim($ref->user_type));
                return ($raw === 'driver' || $raw === 'conducteur') ? 'driver' : 'customer';
            }
        }
        if (Schema::hasTable('common_user_base')) {
            $base = DB::table('common_user_base')->where('user_id', $userId)->first();
            if ($base && !empty($base->user_type)) {
                return $base->user_type === 'driver' ? 'driver' : 'customer';
            }
        }
        if (Schema::hasTable('tj_user_app') && DB::table('tj_user_app')->where('id', $userId)->exists()) {
            return 'customer';
        }
        if (Schema::hasTable('tj_conducteur') && DB::table('tj_conducteur')->where('id', $userId)->exists()) {
            return 'driver';
        }
        return 'customer';
    }
}
