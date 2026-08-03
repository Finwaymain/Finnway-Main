<?php

namespace App\Http\Controllers;

use App\Models\ApiKeySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReferralRewardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // Tab 1: Reward Settings
        $rewardMode  = ApiKeySetting::getApiKeyValue('referral_reward_mode', 'percentage');
        $rewardValue = ApiKeySetting::getApiKeyValue('referral_reward_value', '1.0');
        $rewardMin   = ApiKeySetting::getApiKeyValue('referral_reward_min', '1');

        // Tab 2: Event Rules Defaults
        $eventRules = [
            'app_install'          => ['name' => 'App Install', 'enable' => ApiKeySetting::getApiKeyValue('event_app_install_enable', '1'), 'type' => ApiKeySetting::getApiKeyValue('event_app_install_type', 'percentage'), 'val' => ApiKeySetting::getApiKeyValue('event_app_install_val', '1%')],
            'registration'         => ['name' => 'Registration', 'enable' => ApiKeySetting::getApiKeyValue('event_registration_enable', '1'), 'type' => ApiKeySetting::getApiKeyValue('event_registration_type', 'percentage'), 'val' => ApiKeySetting::getApiKeyValue('event_registration_val', '1%')],
            'user_subscription'    => ['name' => 'User Subscription', 'enable' => ApiKeySetting::getApiKeyValue('event_user_subscription_enable', '1'), 'type' => ApiKeySetting::getApiKeyValue('event_user_subscription_type', 'percentage'), 'val' => ApiKeySetting::getApiKeyValue('event_user_subscription_val', '2%')],
            'service_booking'      => ['name' => 'Service Booking', 'enable' => ApiKeySetting::getApiKeyValue('event_service_booking_enable', '1'), 'type' => ApiKeySetting::getApiKeyValue('event_service_booking_type', 'percentage'), 'val' => ApiKeySetting::getApiKeyValue('event_service_booking_val', '2%')],
            'marketplace_purchase' => ['name' => 'Marketplace Purchase', 'enable' => ApiKeySetting::getApiKeyValue('event_marketplace_purchase_enable', '1'), 'type' => ApiKeySetting::getApiKeyValue('event_marketplace_purchase_type', 'percentage'), 'val' => ApiKeySetting::getApiKeyValue('event_marketplace_purchase_val', '2%')],
            'wallet_transfer'      => ['name' => 'Wallet Payment / Transfer', 'enable' => ApiKeySetting::getApiKeyValue('event_wallet_transfer_enable', '1'), 'type' => ApiKeySetting::getApiKeyValue('event_wallet_transfer_type', 'percentage'), 'val' => ApiKeySetting::getApiKeyValue('event_wallet_transfer_val', '2%')],
            'qr_payment'           => ['name' => 'QR Payment', 'enable' => ApiKeySetting::getApiKeyValue('event_qr_payment_enable', '1'), 'type' => ApiKeySetting::getApiKeyValue('event_qr_payment_type', 'percentage'), 'val' => ApiKeySetting::getApiKeyValue('event_qr_payment_val', '2%')],
            'system_accepted'      => ['name' => 'System Accepted', 'enable' => ApiKeySetting::getApiKeyValue('event_system_accepted_enable', '1'), 'type' => ApiKeySetting::getApiKeyValue('event_system_accepted_type', 'flat'), 'val' => ApiKeySetting::getApiKeyValue('event_system_accepted_val', '₹10')],
        ];

        // Tab 3: Service-Wise Rewards Defaults
        $consumerServices = [
            'cab_ride'         => ['name' => 'Cab / Bike Ride', 'type' => 'Percentage', 'val' => ApiKeySetting::getApiKeyValue('srv_cab_ride_val', '2%'), 'status' => ApiKeySetting::getApiKeyValue('srv_cab_ride_status', '1')],
            'home_service'     => ['name' => 'Home Service', 'type' => 'Percentage', 'val' => ApiKeySetting::getApiKeyValue('srv_home_service_val', '2%'), 'status' => ApiKeySetting::getApiKeyValue('srv_home_service_status', '1')],
            'food_delivery'    => ['name' => 'Food Delivery', 'type' => 'Percentage', 'val' => ApiKeySetting::getApiKeyValue('srv_food_delivery_val', '2%'), 'status' => ApiKeySetting::getApiKeyValue('srv_food_delivery_status', '1')],
            'hotel_booking'    => ['name' => 'Hotel Booking', 'type' => 'Percentage', 'val' => ApiKeySetting::getApiKeyValue('srv_hotel_booking_val', '2%'), 'status' => ApiKeySetting::getApiKeyValue('srv_hotel_booking_status', '1')],
            'travel_booking'   => ['name' => 'Travel Booking', 'type' => 'Percentage', 'val' => ApiKeySetting::getApiKeyValue('srv_travel_booking_val', '2%'), 'status' => ApiKeySetting::getApiKeyValue('srv_travel_booking_status', '1')],
            'parcel_delivery'  => ['name' => 'Parcel Delivery', 'type' => 'Percentage', 'val' => ApiKeySetting::getApiKeyValue('srv_parcel_delivery_val', '2%'), 'status' => ApiKeySetting::getApiKeyValue('srv_parcel_delivery_status', '1')],
            'healthcare_card'  => ['name' => 'Healthcare Card', 'type' => 'Percentage', 'val' => ApiKeySetting::getApiKeyValue('srv_healthcare_card_val', '2%'), 'status' => ApiKeySetting::getApiKeyValue('srv_healthcare_card_status', '1')],
            'payment_received' => ['name' => 'Payment Received', 'type' => 'Percentage', 'val' => ApiKeySetting::getApiKeyValue('srv_payment_received_val', '2%'), 'status' => ApiKeySetting::getApiKeyValue('srv_payment_received_status', '1')],
        ];

        // Stats & Reports
        $totalReferrals = 125;
        $totalInstalled = 110;
        $totalRegistered = 105;
        $totalVerified = 97;
        $consumerUsers = 75;
        $businessUsers = 30;
        $activeUsers = 80;
        $totalTransactions = 4860;
        $totalReferralIncome = 18750.00;
        $avgMonthlyIncome = 1560.00;

        if (Schema::hasTable('tj_user_app')) {
            $realCount = DB::table('tj_user_app')->count();
            if ($realCount > 0) {
                $totalReferrals = $realCount;
            }
        }

        $earningsLogs = [
            ['activity' => 'Cab Ride – Rahul', 'amount' => '+₹8', 'date' => '30 Jul 2026', 'type' => 'Consumer'],
            ['activity' => 'Marketplace – Priya', 'amount' => '+₹25', 'date' => '30 Jul 2026', 'type' => 'Consumer'],
            ['activity' => 'Subscription – Amit', 'amount' => '+₹50', 'date' => '31 Jul 2026', 'type' => 'Business'],
            ['activity' => 'Medicash Card – Neha', 'amount' => '+₹31', 'date' => '31 Jul 2026', 'type' => 'Consumer'],
        ];

        return view('referral.index', compact(
            'rewardMode', 'rewardValue', 'rewardMin',
            'eventRules', 'consumerServices',
            'totalReferrals', 'totalInstalled', 'totalRegistered', 'totalVerified',
            'consumerUsers', 'businessUsers', 'activeUsers', 'totalTransactions',
            'totalReferralIncome', 'avgMonthlyIncome', 'earningsLogs'
        ));
    }

    public function update(Request $request)
    {
        if (Schema::hasTable('api_key_settings')) {
            // Save Tab 1
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

            // Save Tab 2 (Event Rules)
            if ($request->has('event_rules_submit')) {
                $rules = ['app_install', 'registration', 'user_subscription', 'service_booking', 'marketplace_purchase', 'wallet_transfer', 'qr_payment', 'system_accepted'];
                foreach ($rules as $rule) {
                    ApiKeySetting::updateOrCreate(
                        ['key_name' => "event_{$rule}_enable"],
                        ['group' => 'referral', 'provider' => 'engine', 'key_value' => $request->input("event_{$rule}_enable", '0'), 'is_active' => true]
                    );
                    if ($request->has("event_{$rule}_val")) {
                        ApiKeySetting::updateOrCreate(
                            ['key_name' => "event_{$rule}_val"],
                            ['group' => 'referral', 'provider' => 'engine', 'key_value' => $request->input("event_{$rule}_val"), 'is_active' => true]
                        );
                    }
                }
            }

            // Save Tab 3 (Service-wise Rewards)
            if ($request->has('service_rewards_submit')) {
                $services = ['cab_ride', 'home_service', 'food_delivery', 'hotel_booking', 'travel_booking', 'parcel_delivery', 'healthcare_card', 'payment_received'];
                foreach ($services as $srv) {
                    ApiKeySetting::updateOrCreate(
                        ['key_name' => "srv_{$srv}_status"],
                        ['group' => 'referral', 'provider' => 'engine', 'key_value' => $request->input("srv_{$srv}_status", '0'), 'is_active' => true]
                    );
                    if ($request->has("srv_{$srv}_val")) {
                        ApiKeySetting::updateOrCreate(
                            ['key_name' => "srv_{$srv}_val"],
                            ['group' => 'referral', 'provider' => 'engine', 'key_value' => $request->input("srv_{$srv}_val"), 'is_active' => true]
                        );
                    }
                }
            }
        }

        return redirect()->back()->with('success', 'Referral Engine Settings Updated Successfully!');
    }
}
