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

        $owner = $this->findOwnerByPhone($phone);
        $hasRestaurant = $owner ? FoodRestaurant::where('owner_id', $owner->id)->exists() : false;

        if ($mode === 'signup' && $owner && $hasRestaurant) {
            return response()->json(['success' => false, 'error' => 'Restaurant already registered with this mobile. Please login.']);
        }
        if ($mode === 'login' && (!$owner || !$hasRestaurant)) {
            return response()->json(['success' => false, 'error' => 'No restaurant account found. Please register.']);
        }

        // Generate random 4-digit OTP for real phone verification
        $otp = (string) rand(1000, 9999);

        // Clean 10-digit Indian mobile number
        $cleanMobile = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($cleanMobile) > 10) {
            $cleanMobile = substr($cleanMobile, -10);
        }

        // Dispatch VoiceFortius OBD OTP call
        try {
            \Illuminate\Support\Facades\Http::timeout(10)->get('http://voicefortius.com/api/OBDOTP/otpcall', [
                'apikey'       => 'h9Tcpa5cYkudx88vWmgZ8w',
                'callerId'     => '5226930826',
                'mobileNumber' => $cleanMobile,
                'fileName'     => '8 July.wav',
                'otp'          => $otp,
                'retry'        => '0',
            ]);
            \Illuminate\Support\Facades\Log::info("VoiceFortius Restaurant OTP dispatched to $cleanMobile");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("VoiceFortius OTP Dispatch Error: " . $e->getMessage());
        }

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
            'message' => 'OTP sent successfully to your mobile number.',
            'data' => [
                'phone' => $phone,
            ],
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $phone = PhoneService::normalize(trim((string) $request->get('phone')));
        $otp = trim((string) $request->get('otp'));

        $owner = $this->findOwnerByPhone($phone);
        if (!$owner) {
            return response()->json(['success' => false, 'error' => 'Account not found. Please request OTP again.']);
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

        // Also check if any restaurant has this phone as owner_phone
        $restaurant = FoodRestaurant::whereIn('owner_phone', $variants)->first();

        if ($restaurant && $restaurant->owner_id) {
            return FoodOwner::find($restaurant->owner_id);
        }

        return null;
    }

    public function checkUser(Request $request)
    {
        $rawPhone = trim((string) $request->get('phone'));
        $phone = PhoneService::normalize($rawPhone);
        if (empty($phone)) {
            return response()->json(['success' => false, 'error' => 'Valid Indian mobile (+91XXXXXXXXXX) required.']);
        }

        $owner = $this->findOwnerByPhone($phone);
        $hasRestaurant = $owner ? FoodRestaurant::where('owner_id', $owner->id)->exists() : false;

        if (!$owner || !$hasRestaurant) {
            return response()->json([
                'success' => true,
                'data' => [
                    'exists' => false,
                    'has_mpin' => false,
                    'profile_completed' => false,
                    'phone' => $phone,
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'exists' => true,
                'has_mpin' => !empty($owner->mpin),
                'profile_completed' => !empty($owner->name),
                'name' => $owner->name,
                'phone' => $owner->phone,
                'status' => $owner->status,
            ],
        ]);
    }

    public function loginMpin(Request $request)
    {
        $rawPhone = trim((string) $request->get('phone'));
        $phone = PhoneService::normalize($rawPhone);
        $mpin = trim((string) $request->get('mpin'));

        $owner = $this->findOwnerByPhone($phone);
        $hasRestaurant = $owner ? FoodRestaurant::where('owner_id', $owner->id)->exists() : false;

        if (!$owner || !$hasRestaurant) {
            return response()->json(['success' => false, 'error' => 'No active restaurant found for this account. Please register.']);
        }
        if ($owner->status !== 'active') {
            return response()->json(['success' => false, 'error' => 'Account blocked. Contact support.']);
        }

        if (empty($owner->mpin)) {
            // First-time MPIN set for existing owner logging in
            if (strlen($mpin) === 4 && ctype_digit($mpin)) {
                $owner->mpin = Hash::make($mpin);
                $owner->save();
            } else {
                return response()->json(['success' => false, 'error' => 'Please enter a 4-digit MPIN.']);
            }
        } elseif (!Hash::check($mpin, $owner->mpin)) {
            return response()->json(['success' => false, 'error' => 'Invalid MPIN.']);
        }

        $token = $this->issueToken($owner);
        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => $this->ownerPayload($owner, $token),
        ]);
    }

    public function setupMpin(Request $request)
    {
        $phone = PhoneService::normalize(trim((string) $request->get('phone')));
        $mpin = trim((string) $request->get('mpin'));

        if (strlen($mpin) !== 4 || !ctype_digit($mpin)) {
            return response()->json(['success' => false, 'error' => 'MPIN must be 4 digits.']);
        }

        $owner = FoodOwner::where('phone', $phone)->first();
        if (!$owner) {
            return response()->json(['success' => false, 'error' => 'Account not found.']);
        }

        $owner->mpin = Hash::make($mpin);
        if ($request->filled('name')) {
            $owner->name = $request->get('name');
        }
        if ($request->filled('email')) {
            $owner->email = $request->get('email');
        }
        $owner->save();

        $token = $this->issueToken($owner);
        return response()->json([
            'success' => true,
            'message' => 'MPIN setup successfully.',
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
                'has_mpin' => !empty($owner->mpin),
                'profile_completed' => !empty($owner->name),
            ],
            'accesstoken' => $token ?: $owner->access_token,
            'token' => $token ?: $owner->access_token,
            'restaurants' => $restaurants,
            'has_restaurant' => $restaurants->count() > 0,
            'onboarding_required' => $restaurants->where('onboarding_status', 'active')->count() === 0,
        ];
    }
}
