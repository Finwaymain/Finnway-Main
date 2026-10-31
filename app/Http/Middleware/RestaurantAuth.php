<?php

namespace App\Http\Middleware;

use App\Models\Food\FoodOwner;
use App\Models\Food\FoodRestaurant;
use App\Services\PhoneService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class RestaurantAuth
{
    public function handle(Request $request, Closure $next)
    {
        // 1. Resolve token from headers, bearer, query, or request body
        $token = $request->header('X-Restaurant-Token')
            ?: $request->header('accesstoken')
            ?: $request->header('token')
            ?: $request->bearerToken()
            ?: $request->input('accesstoken')
            ?: $request->input('token')
            ?: $request->get('accesstoken')
            ?: $request->get('token');

        // 2. Resolve phone from headers, query, or request body
        $phone = $request->header('X-Owner-Phone')
            ?: $request->header('X-Restaurant-Phone')
            ?: $request->header('X-User-Phone')
            ?: $request->header('phone')
            ?: $request->input('phone')
            ?: $request->get('phone');

        $owner = null;

        // Step A: Find directly in food_owners by access_token
        if (!empty($token)) {
            $owner = FoodOwner::query()
                ->where('access_token', $token)
                ->where(function ($q) {
                    $q->whereNull('status')
                      ->orWhereNotIn('status', ['blocked', 'banned', 'suspended']);
                })
                ->first();
        }

        // Step B: Check users_access table (e.g. driver or user token passed into webview)
        if (!$owner && !empty($token) && Schema::hasTable('users_access')) {
            $userAccess = DB::table('users_access')->where('accesstoken', $token)->first();
            if ($userAccess && !empty($userAccess->user_id)) {
                $userPhone = null;
                if ($userAccess->user_type === 'driver' && Schema::hasTable('tj_conducteur')) {
                    $userPhone = DB::table('tj_conducteur')->where('id', $userAccess->user_id)->value('phone');
                } elseif (Schema::hasTable('tj_user_app')) {
                    $userPhone = DB::table('tj_user_app')->where('id', $userAccess->user_id)->value('phone');
                }

                if ($userPhone) {
                    $owner = $this->findOwnerByPhone($userPhone);
                    if ($owner) {
                        $owner->access_token = $token;
                        $owner->save();
                    }
                }
            }
        }

        // Step C: Fallback to phone lookup if token was stale or missing
        if (!$owner && !empty($phone)) {
            $owner = $this->findOwnerByPhone((string) $phone);
            if ($owner) {
                if (!empty($token)) {
                    $owner->access_token = $token;
                    $owner->save();
                }
            }
        }

        // If still no owner could be authenticated
        if (!$owner) {
            if (empty($token) && empty($phone)) {
                return response()->json(['success' => false, 'error' => 'Restaurant access token or phone required.'], 401);
            }
            return response()->json(['success' => false, 'error' => 'Invalid or expired restaurant token.'], 401);
        }

        // Check if account is explicitly suspended
        if (in_array(strtolower((string) $owner->status), ['blocked', 'banned', 'suspended'], true)) {
            return response()->json(['success' => false, 'error' => 'Your restaurant owner account has been suspended. Please contact support.'], 403);
        }

        $request->attributes->set('food_owner', $owner);
        $request->setUserResolver(function () use ($owner) {
            return $owner;
        });
        $request->merge(['_food_owner_id' => $owner->id]);

        return $next($request);
    }

    protected function findOwnerByPhone(string $phone): ?FoodOwner
    {
        $variants = PhoneService::getVariants($phone);
        if (empty($variants)) {
            return null;
        }

        $owner = FoodOwner::whereIn('phone', $variants)->first();
        if ($owner) {
            return $owner;
        }

        $restaurant = FoodRestaurant::whereIn('owner_phone', $variants)->first();
        if ($restaurant && $restaurant->owner_id) {
            return FoodOwner::find($restaurant->owner_id);
        }

        return null;
    }
}
