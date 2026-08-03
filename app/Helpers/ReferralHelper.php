<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\ApiKeySetting;

class ReferralHelper
{
    /**
     * Process referral reward for a given event
     */
    public static function processReward($userId, string $eventType, float $eventAmount = 0)
    {
        $user = DB::table('tj_user_app')->where('id', $userId)->first();
        if (!$user) {
            $user = DB::table('tj_conducteur')->where('id', $userId)->first();
        }

        if (!$user) {
            return false;
        }

        $rewardMode = ApiKeySetting::getApiKeyValue('referral_reward_mode', 'percentage');
        $rewardValue = floatval(ApiKeySetting::getApiKeyValue('referral_reward_value', '2.0'));

        $reward = 0;
        if ($rewardMode === 'percentage' && $eventAmount > 0) {
            $reward = round($eventAmount * ($rewardValue / 100), 2);
        } else {
            $reward = $rewardValue;
        }

        if ($reward > 0) {
            if (Schema::hasTable('tj_user_app') && Schema::hasColumn('tj_user_app', 'amount')) {
                DB::table('tj_user_app')->where('id', $userId)->increment('amount', $reward);
                return true;
            }
        }

        return false;
    }
}
