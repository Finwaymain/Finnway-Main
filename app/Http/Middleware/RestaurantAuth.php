<?php

namespace App\Http\Middleware;

use App\Models\Food\FoodOwner;
use Closure;
use Illuminate\Http\Request;

class RestaurantAuth
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('X-Restaurant-Token')
            ?: $request->header('accesstoken')
            ?: $request->header('token')
            ?: $request->get('accesstoken')
            ?: $request->get('token')
            ?: $request->bearerToken();

        if (empty($token)) {
            return response()->json(['success' => false, 'error' => 'Restaurant access token required.'], 401);
        }

        $owner = FoodOwner::query()
            ->where('access_token', $token)
            ->where('status', 'active')
            ->first();

        if (!$owner) {
            return response()->json(['success' => false, 'error' => 'Invalid or expired restaurant token.'], 401);
        }

        $request->attributes->set('food_owner', $owner);
        $request->setUserResolver(function () use ($owner) {
            return $owner;
        });
        $request->merge(['_food_owner_id' => $owner->id]);

        return $next($request);
    }
}
