<?php

namespace App\Http\Controllers;

use App\Models\ApiKeySetting;
use App\Models\UserCategory;
use App\Models\ServiceRewardConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReferralRewardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        // 1. Reward Settings
        $rewardMode  = ApiKeySetting::getApiKeyValue('referral_reward_mode', 'percentage');
        $rewardValue = ApiKeySetting::getApiKeyValue('referral_reward_value', '1.0');
        $rewardMin   = ApiKeySetting::getApiKeyValue('referral_reward_min', '1');

        // 2. Fetch Parent & Sub-Service Categories from Database
        $categoriesWithSubs = collect();
        if (Schema::hasTable('tj_categorie_user')) {
            $categoriesWithSubs = UserCategory::where(function($q) {
                $q->whereNull('parent_id')->orWhere('parent_id', 0);
            })->with('subcategories')->get();
        }

        // Fetch Stored Service Reward Configurations from Database
        $serviceRewardConfigs = collect();
        if (Schema::hasTable('service_reward_configs')) {
            $serviceRewardConfigs = ServiceRewardConfig::all()->keyBy(function ($item) {
                return $item->category_id ? 'cat_' . $item->category_id : 'slug_' . $item->service_slug;
            });
        }

        // Fallback default service category structure if DB table is empty
        if ($categoriesWithSubs->isEmpty()) {
            $defaultCategories = [
                'Transportation' => ['Cab Ride', 'Bike Taxi', 'Auto Rickshaw', 'E-Rickshaw'],
                'Home Services' => [
                    'Electrician' => ['Fan Repair & Installation', 'Switchboard & Wiring', 'Light & Chandelier Fitting', 'Inverter Repair'],
                    'Plumber' => ['Tap & Sink Repair', 'Pipe Leakage Fix', 'Water Tank Cleaning', 'Bathroom Fitting'],
                    'AC & Appliance' => ['AC Service & Repair', 'Washing Machine Repair', 'Refrigerator Repair', 'RO Purifier Service'],
                    'Cleaning & Pest' => ['Full House Deep Cleaning', 'Bathroom Cleaning', 'Sofa & Carpet Cleaning', 'Pest Control'],
                    'Salon & Grooming' => ['Men\'s Haircut & Shave', 'Women\'s Beauty & Facial', 'Makeup & Styling'],
                ],
                'Food Services' => ['Restaurant Delivery', 'Bakery & Sweets', 'Grocery Delivery'],
                'Marketplace' => ['Electronics & Gadgets', 'Automotive Accessories', 'Tools & Equipment'],
                'Medical Services' => ['Doctor Home Visit', 'Lab Test Sample Collection', 'Medicash OPD Card'],
                'Financial Services' => ['Micro-Loan Processing', 'Insurance Policy Renewal'],
            ];
        } else {
            $defaultCategories = null;
        }

        // 3. Query Real Database Counts
        $totalReferrals = 0;
        $totalInstalled = 0;
        $totalRegistered = 0;
        $totalVerified = 0;
        $consumerUsers = 0;
        $activeUsers = 0;

        if (Schema::hasTable('tj_user_app')) {
            $totalReferrals = DB::table('tj_user_app')->count();

            if (Schema::hasColumn('tj_user_app', 'fcm_id')) {
                $totalInstalled = DB::table('tj_user_app')->whereNotNull('fcm_id')->where('fcm_id', '!=', '')->count();
            } else {
                $totalInstalled = $totalReferrals;
            }

            if (Schema::hasColumn('tj_user_app', 'statut')) {
                $totalRegistered = DB::table('tj_user_app')->where('statut', 'yes')->count();
            } else {
                $totalRegistered = $totalReferrals;
            }

            if (Schema::hasColumn('tj_user_app', 'status')) {
                $totalVerified = DB::table('tj_user_app')->where('status', 'approved')->count();
            } elseif (Schema::hasColumn('tj_user_app', 'statut')) {
                $totalVerified = DB::table('tj_user_app')->where('statut', 'yes')->count();
            } else {
                $totalVerified = $totalReferrals;
            }

            $consumerUsers = $totalReferrals;
            $activeUsers = $totalRegistered;
        }

        $businessUsers = 0;
        if (Schema::hasTable('tj_conducteur')) {
            $businessUsers = DB::table('tj_conducteur')->count();
        }

        $totalTransactions = 0;
        $totalReferralIncome = 0.0;
        if (Schema::hasTable('tj_requete')) {
            $totalTransactions = DB::table('tj_requete')->count();
            if (Schema::hasColumn('tj_requete', 'statut')) {
                $totalReferralIncome = (float) DB::table('tj_requete')->where('statut', 'completed')->sum('montant') * 0.02;
            } else {
                $totalReferralIncome = (float) DB::table('tj_requete')->sum('montant') * 0.02;
            }
        }

        // 4. Calculate Dynamic One-Time & Multiple-Time Earnings from Database
        $oneTimeTotal = $totalRegistered * 50.00;
        $multipleTimeTotal = $totalTransactions > 0 ? $totalReferralIncome : 0.00;

        $oneTimeEarningsTable = [
            ['activity' => 'User Registration Referral Bonus', 'count' => $totalRegistered, 'earnings' => '₹' . number_format($totalRegistered * 50, 2)],
            ['activity' => 'Driver Partner Onboarding Referral', 'count' => $businessUsers, 'earnings' => '₹' . number_format($businessUsers * 100, 2)],
        ];

        $multipleTimeEarningsTable = [
            ['activity' => 'Cab / Bike Rides Referral Bonus', 'times_used' => $totalTransactions, 'earnings' => '₹' . number_format($totalReferralIncome, 2)],
        ];

        // 5. Fetch Dynamic Earnings Logs
        $earningsLogs = [];
        if (Schema::hasTable('tj_transaction')) {
            $rawLogs = DB::table('tj_transaction')
                ->orderBy('id', 'desc')
                ->limit(10)
                ->get();

            foreach ($rawLogs as $log) {
                $earningsLogs[] = [
                    'activity' => $log->libelle ?? 'Referral Transaction Bonus',
                    'amount'   => '+₹' . number_format((float)($log->amount ?? 0), 2),
                    'date'     => $log->creer ?? now()->format('d M Y'),
                    'type'     => 'Ecosystem'
                ];
            }
        }

        // 6. Referral Event Rules Configuration
        $eventRules = [
            'app_install_user' => [
                'name'         => 'App Install User',
                'enable'       => (ApiKeySetting::getApiKeyValue('event_rule_app_install_user_enable', ApiKeySetting::getApiKeyValue('event_rule_app_install_enable', '1')) == '1'),
                'type'         => ApiKeySetting::getApiKeyValue('event_rule_app_install_user_type', ApiKeySetting::getApiKeyValue('event_rule_app_install_type', 'flat')),
                'value'        => ApiKeySetting::getApiKeyValue('event_rule_app_install_user_value', ApiKeySetting::getApiKeyValue('event_rule_app_install_value', '10')),
                'min_services' => ApiKeySetting::getApiKeyValue('event_rule_app_install_user_min_services', ApiKeySetting::getApiKeyValue('event_rule_app_install_min_services', '1')),
                'min_amount'   => ApiKeySetting::getApiKeyValue('event_rule_app_install_user_min_amount', ApiKeySetting::getApiKeyValue('event_rule_app_install_min_amount', '0')),
            ],
            'app_install_business' => [
                'name'         => 'App Install Business',
                'enable'       => (ApiKeySetting::getApiKeyValue('event_rule_app_install_business_enable', ApiKeySetting::getApiKeyValue('event_rule_registration_enable', '1')) == '1'),
                'type'         => ApiKeySetting::getApiKeyValue('event_rule_app_install_business_type', ApiKeySetting::getApiKeyValue('event_rule_registration_type', 'flat')),
                'value'        => ApiKeySetting::getApiKeyValue('event_rule_app_install_business_value', ApiKeySetting::getApiKeyValue('event_rule_registration_value', '5')),
                'min_services' => ApiKeySetting::getApiKeyValue('event_rule_app_install_business_min_services', ApiKeySetting::getApiKeyValue('event_rule_registration_min_services', '1')),
                'min_amount'   => ApiKeySetting::getApiKeyValue('event_rule_app_install_business_min_amount', ApiKeySetting::getApiKeyValue('event_rule_registration_min_amount', '0')),
            ],
            'user_subscription' => [
                'name'         => 'User Subscription',
                'enable'       => ApiKeySetting::getApiKeyValue('event_rule_user_subscription_enable', '1') == '1',
                'type'         => ApiKeySetting::getApiKeyValue('event_rule_user_subscription_type', 'percentage'),
                'value'        => ApiKeySetting::getApiKeyValue('event_rule_user_subscription_value', '2'),
                'min_services' => ApiKeySetting::getApiKeyValue('event_rule_user_subscription_min_services', '0'),
                'min_amount'   => ApiKeySetting::getApiKeyValue('event_rule_user_subscription_min_amount', '0'),
            ],
            'service_booking' => [
                'name'         => 'Service Booking',
                'enable'       => ApiKeySetting::getApiKeyValue('event_rule_service_booking_enable', '1') == '1',
                'type'         => ApiKeySetting::getApiKeyValue('event_rule_service_booking_type', 'percentage'),
                'value'        => ApiKeySetting::getApiKeyValue('event_rule_service_booking_value', '2'),
                'min_services' => ApiKeySetting::getApiKeyValue('event_rule_service_booking_min_services', '0'),
                'min_amount'   => ApiKeySetting::getApiKeyValue('event_rule_service_booking_min_amount', '0'),
            ],
            'marketplace_purchase' => [
                'name'         => 'Marketplace Purchase',
                'enable'       => ApiKeySetting::getApiKeyValue('event_rule_marketplace_purchase_enable', '1') == '1',
                'type'         => ApiKeySetting::getApiKeyValue('event_rule_marketplace_purchase_type', 'percentage'),
                'value'        => ApiKeySetting::getApiKeyValue('event_rule_marketplace_purchase_value', '2'),
                'min_services' => ApiKeySetting::getApiKeyValue('event_rule_marketplace_purchase_min_services', '0'),
                'min_amount'   => ApiKeySetting::getApiKeyValue('event_rule_marketplace_purchase_min_amount', '0'),
            ],
            'wallet_payment_transfer' => [
                'name'         => 'Wallet Payment / Transfer',
                'enable'       => ApiKeySetting::getApiKeyValue('event_rule_wallet_payment_transfer_enable', '1') == '1',
                'type'         => ApiKeySetting::getApiKeyValue('event_rule_wallet_payment_transfer_type', 'percentage'),
                'value'        => ApiKeySetting::getApiKeyValue('event_rule_wallet_payment_transfer_value', '2'),
                'min_services' => ApiKeySetting::getApiKeyValue('event_rule_wallet_payment_transfer_min_services', '0'),
                'min_amount'   => ApiKeySetting::getApiKeyValue('event_rule_wallet_payment_transfer_min_amount', '0'),
            ],
            'qr_payment' => [
                'name'         => 'QR Payment',
                'enable'       => ApiKeySetting::getApiKeyValue('event_rule_qr_payment_enable', '1') == '1',
                'type'         => ApiKeySetting::getApiKeyValue('event_rule_qr_payment_type', 'percentage'),
                'value'        => ApiKeySetting::getApiKeyValue('event_rule_qr_payment_value', '2'),
                'min_services' => ApiKeySetting::getApiKeyValue('event_rule_qr_payment_min_services', '0'),
                'min_amount'   => ApiKeySetting::getApiKeyValue('event_rule_qr_payment_min_amount', '0'),
            ],
            'system_accepted' => [
                'name'         => 'System Accepted',
                'enable'       => ApiKeySetting::getApiKeyValue('event_rule_system_accepted_enable', '1') == '1',
                'type'         => ApiKeySetting::getApiKeyValue('event_rule_system_accepted_type', 'flat'),
                'value'        => ApiKeySetting::getApiKeyValue('event_rule_system_accepted_value', '10'),
                'min_services' => ApiKeySetting::getApiKeyValue('event_rule_system_accepted_min_services', '0'),
                'min_amount'   => ApiKeySetting::getApiKeyValue('event_rule_system_accepted_min_amount', '0'),
            ],
        ];

        return view('referral.index', compact(
            'rewardMode', 'rewardValue', 'rewardMin',
            'categoriesWithSubs', 'defaultCategories', 'serviceRewardConfigs',
            'totalReferrals', 'totalInstalled', 'totalRegistered', 'totalVerified',
            'consumerUsers', 'businessUsers', 'activeUsers', 'totalTransactions',
            'totalReferralIncome', 'earningsLogs',
            'oneTimeTotal', 'multipleTimeTotal',
            'oneTimeEarningsTable', 'multipleTimeEarningsTable',
            'eventRules'
        ));
    }

    public function update(Request $request)
    {
        if (Schema::hasTable('api_key_settings')) {
            if ($request->has('reward_mode')) {
                $rewardMode = $request->reward_mode === 'flat' ? 'flat' : 'percentage';
                $rawVal = floatval($request->reward_value ?? 1.0);
                if ($rewardMode === 'percentage') {
                    $rawVal = min(100.0, max(0.0, $rawVal));
                }

                ApiKeySetting::updateOrCreate(
                    ['key_name' => 'referral_reward_mode'],
                    ['group' => 'referral', 'provider' => 'engine', 'key_value' => $rewardMode, 'is_active' => true]
                );
                ApiKeySetting::updateOrCreate(
                    ['key_name' => 'referral_reward_value'],
                    ['group' => 'referral', 'provider' => 'engine', 'key_value' => (string) $rawVal, 'is_active' => true]
                );
                ApiKeySetting::updateOrCreate(
                    ['key_name' => 'referral_reward_min'],
                    ['group' => 'referral', 'provider' => 'engine', 'key_value' => $request->reward_min ?? '1', 'is_active' => true]
                );
            }

            if ($request->has('service_rewards_submit')) {
                $allInputs = $request->all();

                $catIds = [];
                $slugKeys = [];
                foreach ($allInputs as $key => $val) {
                    if (preg_match('/^srv_cat_(\d+)_(business_val|customer_val|status|mode)$/', $key, $m)) {
                        $catIds[$m[1]] = true;
                    } elseif (preg_match('/^srv_([a-zA-Z0-9_\-]+)_(business_val|customer_val|status|mode)$/', $key, $m)) {
                        $slugKeys[$m[1]] = true;
                    }
                }

                if (Schema::hasTable('service_reward_configs')) {
                    foreach (array_keys($catIds) as $catId) {
                        $mode = trim((string) $request->input("srv_cat_{$catId}_mode", 'percentage'));
                        $bizVal = trim((string) $request->input("srv_cat_{$catId}_business_val", '2%'));
                        $custVal = trim((string) $request->input("srv_cat_{$catId}_customer_val", '2%'));
                        
                        // If percentage mode, cap numeric part to 100
                        if ($mode === 'percentage') {
                            $bizNum = min(100.0, max(0.0, floatval(str_replace('%', '', $bizVal))));
                            $bizVal = $bizNum . '%';
                            $custNum = min(100.0, max(0.0, floatval(str_replace('%', '', $custVal))));
                            $custVal = $custNum . '%';
                        }

                        $status = $request->has("srv_cat_{$catId}_status");
                        $catName = DB::table('tj_categorie_user')->where('id', $catId)->value('libelle') ?? ('Service #' . $catId);

                        ServiceRewardConfig::updateOrCreate(
                            ['category_id' => $catId],
                            [
                                'service_name' => $catName,
                                'service_slug' => \Illuminate\Support\Str::slug($catName),
                                'reward_mode' => $mode === 'flat' ? 'flat' : 'percentage',
                                'business_value' => $bizVal !== '' ? $bizVal : ($mode === 'flat' ? '50' : '2%'),
                                'customer_value' => $custVal !== '' ? $custVal : ($mode === 'flat' ? '50' : '2%'),
                                'is_active' => $status,
                            ]
                        );
                    }

                    foreach (array_keys($slugKeys) as $slug) {
                        $mode = trim((string) $request->input("srv_{$slug}_mode", 'percentage'));
                        $bizVal = trim((string) $request->input("srv_{$slug}_business_val", '2%'));
                        $custVal = trim((string) $request->input("srv_{$slug}_customer_val", '2%'));

                        if ($mode === 'percentage') {
                            $bizNum = min(100.0, max(0.0, floatval(str_replace('%', '', $bizVal))));
                            $bizVal = $bizNum . '%';
                            $custNum = min(100.0, max(0.0, floatval(str_replace('%', '', $custVal))));
                            $custVal = $custNum . '%';
                        }

                        $status = $request->has("srv_{$slug}_status");
                        $servName = ucwords(str_replace(['_', '-'], ' ', $slug));

                        ServiceRewardConfig::updateOrCreate(
                            ['service_slug' => $slug],
                            [
                                'category_id' => null,
                                'service_name' => $servName,
                                'reward_mode' => $mode === 'flat' ? 'flat' : 'percentage',
                                'business_value' => $bizVal !== '' ? $bizVal : ($mode === 'flat' ? '50' : '2%'),
                                'customer_value' => $custVal !== '' ? $custVal : ($mode === 'flat' ? '50' : '2%'),
                                'is_active' => $status,
                            ]
                        );
                    }
                }

                foreach ($request->except(['_token', 'service_rewards_submit']) as $key => $value) {
                    ApiKeySetting::updateOrCreate(
                        ['key_name' => 'srv_rule_' . $key],
                        ['group' => 'referral', 'provider' => 'service_rule', 'key_value' => (string)$value, 'is_active' => true]
                    );
                }

                return redirect()->back()->with('success', 'Service-wise Reward Configurations Saved Successfully!');
            }

            if ($request->has('event_rules_submit')) {
                $events = ['app_install_user', 'app_install_business', 'app_install', 'registration', 'user_subscription', 'service_booking', 'marketplace_purchase', 'wallet_payment_transfer', 'qr_payment', 'system_accepted'];
                foreach ($events as $evt) {
                    if (!$request->has("event_{$evt}_type") && !$request->has("event_{$evt}_value") && !$request->has("event_{$evt}_min_services") && !$request->has("event_{$evt}_enable")) {
                        continue;
                    }
                    $enableVal      = $request->has("event_{$evt}_enable") ? '1' : '0';
                    $typeVal        = $request->input("event_{$evt}_type", 'percentage');
                    $valueVal       = $request->input("event_{$evt}_value", '0');
                    $minServicesVal = (int) $request->input("event_{$evt}_min_services", '0');
                    $minAmountVal   = (float) $request->input("event_{$evt}_min_amount", '0');

                    if ($typeVal === 'percentage') {
                        $valueVal = (string) min(100.0, max(0.0, floatval($valueVal)));
                    }

                    ApiKeySetting::updateOrCreate(
                        ['key_name' => "event_rule_{$evt}_enable"],
                        ['group' => 'referral_event', 'provider' => 'rule', 'key_value' => (string)$enableVal, 'is_active' => true]
                    );
                    ApiKeySetting::updateOrCreate(
                        ['key_name' => "event_rule_{$evt}_type"],
                        ['group' => 'referral_event', 'provider' => 'rule', 'key_value' => (string)$typeVal, 'is_active' => true]
                    );
                    ApiKeySetting::updateOrCreate(
                        ['key_name' => "event_rule_{$evt}_value"],
                        ['group' => 'referral_event', 'provider' => 'rule', 'key_value' => (string)$valueVal, 'is_active' => true]
                    );
                    ApiKeySetting::updateOrCreate(
                        ['key_name' => "event_rule_{$evt}_min_services"],
                        ['group' => 'referral_event', 'provider' => 'rule', 'key_value' => (string)$minServicesVal, 'is_active' => true]
                    );
                    ApiKeySetting::updateOrCreate(
                        ['key_name' => "event_rule_{$evt}_min_amount"],
                        ['group' => 'referral_event', 'provider' => 'rule', 'key_value' => (string)$minAmountVal, 'is_active' => true]
                    );
                }
                return redirect()->back()->with('success', 'Referral Event Rules Saved Successfully!');
            }
        }

        return redirect()->back()->with('success', 'Referral Configuration Saved Successfully!');
    }
}
