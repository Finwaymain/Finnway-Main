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
        $rewardMode  = ApiKeySetting::getApiKeyValue('referral_reward_mode', 'percentage');
        $rewardValue = ApiKeySetting::getApiKeyValue('referral_reward_value', '2.0');

        $totalReferrals = 0;
        if (Schema::hasTable('tj_user_app')) {
            $totalReferrals = DB::table('tj_user_app')->count();
        }

        $totalPaid = 0;
        if (Schema::hasTable('tbl_earning') && Schema::hasColumn('tbl_earning', 'earn_wallet')) {
            $totalPaid = DB::table('tbl_earning')->where('description', 'LIKE', '%referral%')->sum('earn_wallet');
        } elseif (Schema::hasTable('tj_transaction') && Schema::hasColumn('tj_transaction', 'amount')) {
            $totalPaid = DB::table('tj_transaction')->where('type', 'referral_bonus')->sum('amount');
        }

        $topReferrers = collect();

        return view('referral.index', compact('rewardMode', 'rewardValue', 'totalReferrals', 'totalPaid', 'topReferrers'));
    }

    public function update(Request $request)
    {
        if (Schema::hasTable('api_key_settings')) {
            ApiKeySetting::updateOrCreate(
                ['key_name' => 'referral_reward_mode'],
                ['group' => 'referral', 'provider' => 'engine', 'key_value' => $request->mode ?? 'percentage', 'is_active' => true]
            );

            ApiKeySetting::updateOrCreate(
                ['key_name' => 'referral_reward_value'],
                ['group' => 'referral', 'provider' => 'engine', 'key_value' => $request->value ?? '2.0', 'is_active' => true]
            );
        }

        return redirect()->back()->with('success', 'Referral settings updated successfully.');
    }
}
