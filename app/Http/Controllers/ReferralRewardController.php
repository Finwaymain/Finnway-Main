<?php

namespace App\Http\Controllers;

use App\Models\ApiKeySetting;
use App\Models\UserCategory;
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

        return view('referral.index', compact(
            'rewardMode', 'rewardValue', 'rewardMin',
            'categoriesWithSubs', 'defaultCategories',
            'totalReferrals', 'totalInstalled', 'totalRegistered', 'totalVerified',
            'consumerUsers', 'businessUsers', 'activeUsers', 'totalTransactions',
            'totalReferralIncome', 'earningsLogs',
            'oneTimeTotal', 'multipleTimeTotal',
            'oneTimeEarningsTable', 'multipleTimeEarningsTable'
        ));
    }

    public function update(Request $request)
    {
        if (Schema::hasTable('api_key_settings')) {
            if ($request->has('reward_mode')) {
                ApiKeySetting::updateOrCreate(
                    ['key_name' => 'referral_reward_mode'],
                    ['group' => 'referral', 'provider' => 'engine', 'key_value' => $request->reward_mode, 'is_active' => true]
                );
                ApiKeySetting::updateOrCreate(
                    ['key_name' => 'referral_reward_value'],
                    ['group' => 'referral', 'provider' => 'engine', 'key_value' => $request->reward_value ?? '1.0', 'is_active' => true]
                );
                ApiKeySetting::updateOrCreate(
                    ['key_name' => 'referral_reward_min'],
                    ['group' => 'referral', 'provider' => 'engine', 'key_value' => $request->reward_min ?? '1', 'is_active' => true]
                );
            }

            if ($request->has('service_rewards_submit')) {
                foreach ($request->except(['_token', 'service_rewards_submit']) as $key => $value) {
                    ApiKeySetting::updateOrCreate(
                        ['key_name' => 'srv_rule_' . $key],
                        ['group' => 'referral', 'provider' => 'service_rule', 'key_value' => (string)$value, 'is_active' => true]
                    );
                }
            }
        }

        return redirect()->back()->with('success', 'Referral & Service Reward Configuration Saved Successfully!');
    }
}
