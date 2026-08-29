<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Settings;
use App\Services\ReferralCodeService;
use Illuminate\Http\Request;

class GetUserReferralCode extends Controller
{
    public function getData(Request $request)
    {
        $userId = $request->get('id_user') ?: $request->get('user_id') ?: $request->get('id_driver') ?: $request->get('driver_id');
        $userCat = $request->get('user_cat') ?: $request->get('user_type');

        if (!$userId) {
            return response()->json([
                'success' => 'Failed',
                'error'   => 'User ID required',
                'message' => 'Please provide a valid user or driver ID'
            ], 400);
        }

        $userId = (int)$userId;

        // Auto-detect user category if not explicitly provided
        if (empty($userCat)) {
            if ($request->filled('id_driver') || $request->filled('driver_id')) {
                $userCat = 'driver';
            } else {
                $token = $request->header('accesstoken') ?: $request->get('accesstoken');
                if (!empty($token)) {
                    $access = \Illuminate\Support\Facades\DB::table('users_access')->where('accesstoken', $token)->first();
                    if ($access && !empty($access->user_type)) {
                        $userCat = $access->user_type;
                    }
                }
                if (empty($userCat)) {
                    if (\Illuminate\Support\Facades\DB::table('tj_conducteur')->where('id', $userId)->exists() && !\Illuminate\Support\Facades\DB::table('tj_user_app')->where('id', $userId)->exists()) {
                        $userCat = 'driver';
                    } else {
                        $userCat = 'customer';
                    }
                }
            }
        }

        $refCode = ReferralCodeService::getOrCreateReferralCode($userId, (string)$userCat);

        if (!empty($refCode)) {
            $setting = Settings::first();
            $row['referral_amount'] = (string)($setting->referral_amount ?? '20');
            $row['referral_code']   = $refCode;
            $response['success']    = 'success';
            $response['error']      = null;
            $response['message']    = 'referral code fetched successfully';
            $response['data']       = $row;
        } else {
            $response['success']    = 'Failed';
            $response['error']      = 'Not Found';
        }

        return response()->json($response);
    }
}
