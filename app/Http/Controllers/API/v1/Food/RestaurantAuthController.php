<?php

namespace App\Http\Controllers\API\v1\Food;

use App\Http\Controllers\Controller;
use App\Models\Food\FoodOwner;
use App\Models\Food\FoodRestaurant;
use App\Models\Food\FoodRestaurantType;
use App\Services\PhoneService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RestaurantAuthController extends Controller
{
    public function sendOtp(Request $request)
    {
        $phone = PhoneService::normalize(trim((string) $request->get('phone')));
        $mode = $request->get('mode', 'login'); // login|signup

        if (empty($phone) || !preg_match('/^\+91[6-9]\d{9}$/', $phone)) {
            return response()->json(['success' => false, 'error' => 'Valid Indian mobile (+91XXXXXXXXXX) required.']);
        }

        $owner = FoodOwner::where('phone', $phone)->first();
        if ($mode === 'signup' && $owner) {
            return response()->json(['success' => false, 'error' => 'Phone already registered. Please login.']);
        }
        if ($mode === 'login' && !$owner) {
            return response()->json(['success' => false, 'error' => 'No restaurant account found. Please register.']);
        }

        $otp = '1234'; // mirrors existing Fiinway OTP fallback; SMS gateway can replace later
        if (!$owner) {
            $owner = FoodOwner::create([
                'phone' => $phone,
                'name' => $request->get('name'),
                'status' => 'active',
            ]);
        }

        $owner->otp = $otp;
        $owner->otp_expires_at = now()->addMinutes(10);
        $owner->save();

        return response()->json([
            'success' => true,
            'message' => 'OTP sent successfully.',
            'data' => [
                'phone' => $phone,
                'otp_debug' => config('app.debug') ? $otp : null,
            ],
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $phone = PhoneService::normalize(trim((string) $request->get('phone')));
        $otp = trim((string) $request->get('otp'));

        $owner = FoodOwner::where('phone', $phone)->first();
        if (!$owner) {
            return response()->json(['success' => false, 'error' => 'Account not found.']);
        }
        if (empty($owner->otp) || $owner->otp !== $otp) {
            return response()->json(['success' => false, 'error' => 'Invalid OTP.']);
        }
        if ($owner->otp_expires_at && $owner->otp_expires_at->isPast()) {
            return response()->json(['success' => false, 'error' => 'OTP expired.']);
        }

        $token = $this->issueToken($owner);
        $owner->otp = null;
        $owner->otp_expires_at = null;
        if ($request->filled('name')) {
            $owner->name = $request->get('name');
        }
        if ($request->filled('email')) {
            $owner->email = $request->get('email');
        }
        if ($request->filled('password')) {
            $owner->password = Hash::make($request->get('password'));
        }
        if ($request->filled('fcm_token')) {
            $owner->fcm_token = $request->get('fcm_token');
        }
        $owner->save();

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => $this->ownerPayload($owner, $token),
        ]);
    }

    public function loginPassword(Request $request)
    {
        $phone = PhoneService::normalize(trim((string) $request->get('phone')));
        $password = (string) $request->get('password');
        $owner = FoodOwner::where('phone', $phone)->first();
        if (!$owner || empty($owner->password) || !Hash::check($password, $owner->password)) {
            return response()->json(['success' => false, 'error' => 'Invalid phone or password.']);
        }
        if ($owner->status !== 'active') {
            return response()->json(['success' => false, 'error' => 'Account blocked. Contact support.']);
        }
        $token = $this->issueToken($owner);
        return response()->json([
            'success' => true,
            'data' => $this->ownerPayload($owner, $token),
        ]);
    }

    public function profile(Request $request)
    {
        /** @var FoodOwner $owner */
        $owner = $request->attributes->get('food_owner');
        return response()->json([
            'success' => true,
            'data' => $this->ownerPayload($owner, $owner->access_token),
        ]);
    }

    public function updateProfile(Request $request)
    {
        /** @var FoodOwner $owner */
        $owner = $request->attributes->get('food_owner');
        foreach (['name', 'email', 'fcm_token'] as $field) {
            if ($request->filled($field)) {
                $owner->{$field} = $request->get($field);
            }
        }
        if ($request->filled('password')) {
            $owner->password = Hash::make($request->get('password'));
        }
        $owner->save();
        return response()->json(['success' => true, 'data' => $this->ownerPayload($owner, $owner->access_token)]);
    }

    public function logout(Request $request)
    {
        /** @var FoodOwner $owner */
        $owner = $request->attributes->get('food_owner');
        $owner->access_token = null;
        $owner->save();
        return response()->json(['success' => true, 'message' => 'Logged out.']);
    }

    public function types()
    {
        $types = FoodRestaurantType::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'code', 'name', 'description', 'onboarding_fee', 'approval_mode']);
        return response()->json(['success' => true, 'data' => $types]);
    }

    protected function issueToken(FoodOwner $owner): string
    {
        $token = hash('sha256', Str::uuid()->toString() . $owner->id . microtime(true));
        $owner->access_token = $token;
        $owner->save();
        return $token;
    }

    protected function ownerPayload(FoodOwner $owner, ?string $token = null): array
    {
        $restaurants = FoodRestaurant::where('owner_id', $owner->id)->orderByDesc('id')->get();
        return [
            'owner' => [
                'id' => $owner->id,
                'name' => $owner->name,
                'phone' => $owner->phone,
                'email' => $owner->email,
                'status' => $owner->status,
            ],
            'accesstoken' => $token ?: $owner->access_token,
            'restaurants' => $restaurants,
            'has_restaurant' => $restaurants->count() > 0,
            'onboarding_required' => $restaurants->where('onboarding_status', 'active')->count() === 0,
        ];
    }
}
