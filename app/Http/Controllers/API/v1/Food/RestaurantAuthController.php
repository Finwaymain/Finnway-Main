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
        $phone = PhoneService::normalize(trim((string) $request->input('phone')));
        $mode = $request->input('mode', 'login'); // login|signup

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
                'name' => $request->input('name'),
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
        $phone = PhoneService::normalize(trim((string) $request->input('phone')));
        $otp = trim((string) $request->input('otp'));

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
            $owner->name = $request->input('name');
        }
        if ($request->filled('email')) {
            $owner->email = $request->input('email');
        }
        if ($request->filled('password')) {
            $owner->password = Hash::make($request->input('password'));
        }
        if ($request->filled('fcm_token')) {
            $owner->fcm_token = $request->input('fcm_token');
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
        $rawPhone = trim((string) $request->input('phone'));
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
        $rawPhone = trim((string) $request->input('phone'));
        $phone = PhoneService::normalize($rawPhone);
        $mpin = trim((string) $request->input('mpin'));

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

        if ($request->filled('fcm_token')) {
            $owner->fcm_token = $request->input('fcm_token');
            $owner->save();
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
        $phone = PhoneService::normalize(trim((string) $request->input('phone')));
        $mpin = trim((string) $request->input('mpin'));

        if (strlen($mpin) !== 4 || !ctype_digit($mpin)) {
            return response()->json(['success' => false, 'error' => 'MPIN must be 4 digits.']);
        }

        $owner = FoodOwner::where('phone', $phone)->first();
        if (!$owner) {
            return response()->json(['success' => false, 'error' => 'Account not found.']);
        }

        $owner->mpin = Hash::make($mpin);
        if ($request->filled('name')) {
            $owner->name = $request->input('name');
        }
        if ($request->filled('email')) {
            $owner->email = $request->input('email');
        }
        if ($request->filled('fcm_token')) {
            $owner->fcm_token = $request->input('fcm_token');
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
        $phone = PhoneService::normalize(trim((string) $request->input('phone')));
        $password = (string) $request->input('password');
        $owner = FoodOwner::where('phone', $phone)->first();
        if (!$owner || empty($owner->password) || !Hash::check($password, $owner->password)) {
            return response()->json(['success' => false, 'error' => 'Invalid phone or password.']);
        }
        if ($owner->status !== 'active') {
            return response()->json(['success' => false, 'error' => 'Account blocked. Contact support.']);
        }
        if ($request->filled('fcm_token')) {
            $owner->fcm_token = $request->input('fcm_token');
            $owner->save();
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
        foreach (['name', 'email', 'fcm_token', 'pan_number'] as $field) {
            if ($request->filled($field)) {
                $owner->{$field} = $request->input($field);
            }
        }
        if ($request->filled('password')) {
            $owner->password = Hash::make($request->input('password'));
        }

        // Handle owner photo/image upload (file upload or base64)
        $imageFile = $request->file('image') ?: $request->file('owner_image');
        if ($imageFile) {
            $dir = 'food/owners/' . $owner->id;
            $path = $imageFile->store($dir, 'public');
            $owner->image = $path;
        } else {
            $rawImage = $request->input('image') ?: $request->input('owner_image');
            if (is_string($rawImage) && str_starts_with($rawImage, 'data:image')) {
                try {
                    $parts = explode(',', $rawImage);
                    $data = base64_decode($parts[1] ?? '');
                    if (!empty($data)) {
                        $filename = 'food/owners/' . $owner->id . '/owner_' . time() . '.jpg';
                        \Illuminate\Support\Facades\Storage::disk('public')->put($filename, $data);
                        $owner->image = $filename;
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Base64 owner image save error: ' . $e->getMessage());
                }
            } elseif (is_string($rawImage) && !empty($rawImage) && !str_starts_with($rawImage, 'data:image')) {
                $owner->image = $rawImage;
            }
        }

        $owner->save();

        // Synchronize restaurant owner info
        FoodRestaurant::where('owner_id', $owner->id)->update([
            'owner_name' => $owner->name,
            'owner_email' => $owner->email,
            'owner_image' => $owner->image,
            'pan_number' => $owner->pan_number ?: \DB::raw('pan_number'),
        ]);

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

        // Ensure restaurant owner has a unique referral code (FIINR...)
        $referralCode = null;
        try {
            $referralCode = \App\Services\ReferralCodeService::getOrCreateReferralCode($owner->id, 'restaurant');
        } catch (\Throwable $e) {
            $referralCode = $owner->referral_code ?? ('FIINR' . strtoupper(substr(md5((string)$owner->id), 0, 6)));
        }

        // Check if referral code is already applied
        $refBy = $owner->ref_by ?? null;
        if (empty($refBy) && \Illuminate\Support\Facades\Schema::hasTable('referral')) {
            $refBy = \Illuminate\Support\Facades\DB::table('referral')
                ->where('user_id', $owner->id)
                ->where('user_type', 'restaurant')
                ->value('referral_by_code');
        }
        if (empty($refBy) && \Illuminate\Support\Facades\Schema::hasTable('marketing_acquisitions')) {
            $acq = \Illuminate\Support\Facades\DB::table('marketing_acquisitions')
                ->where('acquired_user_id', $owner->id)
                ->where('acquired_user_type', 'restaurant')
                ->first();
            if ($acq) {
                if (!empty($acq->team_member_id)) {
                    $refBy = \Illuminate\Support\Facades\DB::table('marketing_team_members')->where('id', $acq->team_member_id)->value('member_code');
                }
                if (empty($refBy) && !empty($acq->vendor_id)) {
                    $refBy = \Illuminate\Support\Facades\DB::table('marketing_vendors')->where('id', $acq->vendor_id)->value('vendor_code');
                }
            }
        }

        $referrerInfo = !empty($refBy) ? $this->resolveReferrerDisplayInfo($refBy) : null;

        return [
            'owner' => [
                'id' => $owner->id,
                'name' => $owner->name,
                'phone' => $owner->phone,
                'email' => $owner->email,
                'image' => $owner->image ? (str_starts_with($owner->image, 'http') ? $owner->image : asset('storage/' . $owner->image)) : null,
                'image_path' => $owner->image,
                'pan_number' => $owner->pan_number,
                'status' => $owner->status,
                'has_mpin' => !empty($owner->mpin),
                'profile_completed' => !empty($owner->name),
                'referral_code' => $referralCode,
                'ref_by' => $refBy,
                'referrer_info' => $referrerInfo,
            ],
            'accesstoken' => $token ?: $owner->access_token,
            'token' => $token ?: $owner->access_token,
            'restaurants' => $restaurants,
            'has_restaurant' => $restaurants->count() > 0,
            'onboarding_required' => $restaurants->where('onboarding_status', 'active')->count() === 0,
        ];
    }

    /**
     * Apply a Referral or Partner Code for Restaurant
     */
    public function applyReferral(Request $request)
    {
        /** @var FoodOwner $owner */
        $owner = $request->attributes->get('food_owner');
        if (!$owner) {
            return response()->json(['success' => false, 'error' => 'Unauthorized restaurant owner.'], 401);
        }

        $referralCode = strtoupper(trim((string)($request->input('referral_code') ?? $request->get('referral_code') ?? '')));

        if (empty($referralCode)) {
            return response()->json(['success' => false, 'error' => 'Referral code is required.']);
        }

        // Check if referral code is already applied
        $existingRefBy = $owner->ref_by ?? null;
        if (empty($existingRefBy) && \Illuminate\Support\Facades\Schema::hasTable('referral')) {
            $existingRefBy = \Illuminate\Support\Facades\DB::table('referral')
                ->where('user_id', $owner->id)
                ->where('user_type', 'restaurant')
                ->whereNotNull('referral_by_code')
                ->value('referral_by_code');
        }
        if (empty($existingRefBy) && \Illuminate\Support\Facades\Schema::hasTable('marketing_acquisitions')) {
            $existingAcq = \Illuminate\Support\Facades\DB::table('marketing_acquisitions')
                ->where('acquired_user_id', $owner->id)
                ->where('acquired_user_type', 'restaurant')
                ->first();
            if ($existingAcq) {
                return response()->json(['success' => false, 'error' => 'A referral or partner code has already been applied to this restaurant account.']);
            }
        }
        if (!empty($existingRefBy)) {
            return response()->json(['success' => false, 'error' => 'A referral code has already been applied to this restaurant account.']);
        }

        // Prevent self-referral
        $myCode = strtoupper(trim((string)($owner->referral_code ?? '')));
        if (!empty($myCode) && $myCode === $referralCode) {
            return response()->json(['success' => false, 'error' => 'You cannot use your own restaurant referral code.']);
        }

        // ── 1. CHECK MARKETING VENDOR / SUB-VENDOR CODE (VR... / TM... / SV...) ──
        if (str_starts_with($referralCode, 'VR') || str_starts_with($referralCode, 'TM') || str_starts_with($referralCode, 'SV')) {
            $vendor = \App\Services\VendorTeamService::findApprovedVendorByCode($referralCode);
            if ($vendor) {
                // Ensure default team member exists for vendor or get first
                $defaultMember = \Illuminate\Support\Facades\DB::table('marketing_team_members')
                    ->where('vendor_id', $vendor->id)
                    ->orderBy('id', 'asc')
                    ->first();

                $teamMemberId = $defaultMember ? $defaultMember->id : null;
                if (!$teamMemberId) {
                    $teamMemberId = \Illuminate\Support\Facades\DB::table('marketing_team_members')->insertGetId([
                        'vendor_id'   => $vendor->id,
                        'user_id'     => $vendor->user_id,
                        'user_type'   => $vendor->user_type,
                        'member_code' => $vendor->freelancer_code ?: ('FR' . substr($vendor->vendor_code, 2)),
                        'status'      => 'active',
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                }

                // Record acquisition
                \Illuminate\Support\Facades\DB::table('marketing_acquisitions')->insert([
                    'vendor_id'            => $vendor->id,
                    'team_member_id'       => $teamMemberId,
                    'acquired_user_id'     => $owner->id,
                    'acquired_user_type'   => 'restaurant',
                    'verification_status'  => 'pending',
                    'verified_by'          => null,
                    'verified_at'          => null,
                    'payout_rate_applied'  => 0.00,
                    'payout_status'        => 'unpaid',
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ]);

                $this->saveAppliedCodeToRestaurant($owner->id, $referralCode);

                $referrerDisplay = $this->resolveReferrerDisplayInfo($referralCode);

                return response()->json([
                    'success' => true,
                    'message' => "Successfully linked to Partner: {$vendor->vendor_code}!",
                    'referrer' => $referrerDisplay,
                ]);
            }
            return response()->json(['success' => false, 'error' => 'Invalid or inactive Partner / Vendor Code. Please check and try again.']);
        }

        // ── 2. CHECK MARKETING FREELANCER CODE (FR...) ──
        if (str_starts_with($referralCode, 'FR')) {
            $teamMember = \App\Services\VendorTeamService::findActiveTeamMemberByCode($referralCode);
            $vendor = null;
            if (!$teamMember) {
                // Check if vendor has matching freelancer code
                $vendor = \App\Services\VendorTeamService::findApprovedVendorByCode($referralCode);
                if ($vendor) {
                    $teamMember = \Illuminate\Support\Facades\DB::table('marketing_team_members')->where('vendor_id', $vendor->id)->first();
                    if (!$teamMember) {
                        $teamMemberId = \Illuminate\Support\Facades\DB::table('marketing_team_members')->insertGetId([
                            'vendor_id'   => $vendor->id,
                            'user_id'     => $vendor->user_id,
                            'user_type'   => $vendor->user_type,
                            'member_code' => $vendor->freelancer_code ?: $referralCode,
                            'status'      => 'active',
                            'created_at'  => now(),
                            'updated_at'  => now(),
                        ]);
                        $teamMember = \Illuminate\Support\Facades\DB::table('marketing_team_members')->where('id', $teamMemberId)->first();
                    }
                }
            }

            if ($teamMember) {
                \Illuminate\Support\Facades\DB::table('marketing_acquisitions')->insert([
                    'vendor_id'            => $teamMember->vendor_id,
                    'team_member_id'       => $teamMember->id,
                    'acquired_user_id'     => $owner->id,
                    'acquired_user_type'   => 'restaurant',
                    'verification_status'  => 'pending',
                    'verified_by'          => null,
                    'verified_at'          => null,
                    'payout_rate_applied'  => 0.00,
                    'payout_status'        => 'unpaid',
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ]);

                $this->saveAppliedCodeToRestaurant($owner->id, $referralCode);

                $referrerDisplay = $this->resolveReferrerDisplayInfo($referralCode);

                return response()->json([
                    'success' => true,
                    'message' => "Successfully linked to Field Coordinator: {$referralCode}!",
                    'referrer' => $referrerDisplay,
                ]);
            }

            // Fallback: Check if FR code exists in standard referral system
            $fallbackReferrer = \App\Services\ReferralCodeService::resolveReferrer($referralCode);
            if ($fallbackReferrer) {
                $referrerId   = (int)$fallbackReferrer['user_id'];
                $referrerType = $fallbackReferrer['user_type'] ?? 'customer';

                if ($referrerId === (int)$owner->id && $referrerType === 'restaurant') {
                    return response()->json(['success' => false, 'error' => 'You cannot use your own referral code.']);
                }

                $existingReferral = \Illuminate\Support\Facades\DB::table('referral')
                    ->where('user_id', $owner->id)
                    ->where('user_type', 'restaurant')
                    ->first();

                if ($existingReferral) {
                    $refUpdate = [
                        'referral_by_id' => $referrerId,
                        'code_used'      => 'true',
                    ];
                    if (\Illuminate\Support\Facades\Schema::hasColumn('referral', 'referral_by_type')) {
                        $refUpdate['referral_by_type'] = $referrerType;
                    }
                    if (\Illuminate\Support\Facades\Schema::hasColumn('referral', 'referral_by_code')) {
                        $refUpdate['referral_by_code'] = $referralCode;
                    }
                    \Illuminate\Support\Facades\DB::table('referral')->where('id', $existingReferral->id)->update($refUpdate);
                } else {
                    $myReferralCode = \App\Services\ReferralCodeService::getOrCreateReferralCode($owner->id, 'restaurant');
                    $insertData = [
                        'user_id'        => $owner->id,
                        'user_type'      => 'restaurant',
                        'referral_by_id' => $referrerId,
                        'referral_code'  => $myReferralCode,
                        'code_used'      => 'true',
                        'creer'          => date('Y-m-d H:i:s'),
                    ];
                    if (\Illuminate\Support\Facades\Schema::hasColumn('referral', 'referral_by_type')) {
                        $insertData['referral_by_type'] = $referrerType;
                    }
                    if (\Illuminate\Support\Facades\Schema::hasColumn('referral', 'referral_by_code')) {
                        $insertData['referral_by_code'] = $referralCode;
                    }
                    \Illuminate\Support\Facades\DB::table('referral')->insert($insertData);
                }

                $this->saveAppliedCodeToRestaurant($owner->id, $referralCode);

                $referrerDisplay = $this->resolveReferrerDisplayInfo($referralCode);

                return response()->json([
                    'success' => true,
                    'message' => 'Referral code applied successfully!',
                    'referrer' => $referrerDisplay,
                ]);
            }

            return response()->json(['success' => false, 'error' => 'Invalid Freelancer / Field Coordinator Code. Please check and try again.']);
        }

        // ── 3. STANDARD PEER-TO-PEER CODE (FIINC... / FIINB... / FIINR...) ──
        $referrer = \App\Services\ReferralCodeService::resolveReferrer($referralCode);
        if (!$referrer) {
            return response()->json(['success' => false, 'error' => 'Invalid referral code. Please check and try again.']);
        }

        $referrerId   = (int)$referrer['user_id'];
        $referrerType = $referrer['user_type'] ?? 'customer';

        if ($referrerId === (int)$owner->id && $referrerType === 'restaurant') {
            return response()->json(['success' => false, 'error' => 'You cannot use your own referral code.']);
        }

        // Link in referral table
        $existingReferral = \Illuminate\Support\Facades\DB::table('referral')
            ->where('user_id', $owner->id)
            ->where('user_type', 'restaurant')
            ->first();

        if ($existingReferral) {
            $refUpdate = [
                'referral_by_id'   => $referrerId,
                'code_used'        => 'true',
            ];
            if (\Illuminate\Support\Facades\Schema::hasColumn('referral', 'referral_by_type')) {
                $refUpdate['referral_by_type'] = $referrerType;
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('referral', 'referral_by_code')) {
                $refUpdate['referral_by_code'] = $referralCode;
            }
            \Illuminate\Support\Facades\DB::table('referral')->where('id', $existingReferral->id)->update($refUpdate);
        } else {
            $myReferralCode = \App\Services\ReferralCodeService::getOrCreateReferralCode($owner->id, 'restaurant');
            $insertData = [
                'user_id'          => $owner->id,
                'user_type'        => 'restaurant',
                'referral_by_id'   => $referrerId,
                'referral_code'    => $myReferralCode,
                'code_used'        => 'true',
                'creer'            => date('Y-m-d H:i:s'),
            ];
            if (\Illuminate\Support\Facades\Schema::hasColumn('referral', 'referral_by_type')) {
                $insertData['referral_by_type'] = $referrerType;
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('referral', 'referral_by_code')) {
                $insertData['referral_by_code'] = $referralCode;
            }
            \Illuminate\Support\Facades\DB::table('referral')->insert($insertData);
        }

        $this->saveAppliedCodeToRestaurant($owner->id, $referralCode);

        $referrerDisplay = $this->resolveReferrerDisplayInfo($referralCode);

        return response()->json([
            'success' => true,
            'message' => 'Referral code applied successfully!',
            'referrer' => $referrerDisplay,
        ]);
    }

    /**
     * Helper to save applied referral code to food_owners and food_restaurants
     */
    private function saveAppliedCodeToRestaurant(int $ownerId, string $referralCode): void
    {
        if (\Illuminate\Support\Facades\Schema::hasTable('food_owners') && \Illuminate\Support\Facades\Schema::hasColumn('food_owners', 'ref_by')) {
            \Illuminate\Support\Facades\DB::table('food_owners')->where('id', $ownerId)->update(['ref_by' => $referralCode]);
        }
        if (\Illuminate\Support\Facades\Schema::hasTable('food_restaurants') && \Illuminate\Support\Facades\Schema::hasColumn('food_restaurants', 'ref_by')) {
            \Illuminate\Support\Facades\DB::table('food_restaurants')->where('owner_id', $ownerId)->update(['ref_by' => $referralCode]);
        }
    }

    /**
     * Resolve user-friendly display metadata for a referral code
     */
    public function resolveReferrerDisplayInfo(string $referralCode): array
    {
        $code = strtoupper(trim($referralCode));

        // 1. Vendor
        if (str_starts_with($code, 'VR') || str_starts_with($code, 'TM') || str_starts_with($code, 'SV')) {
            $v = \Illuminate\Support\Facades\DB::table('marketing_vendors')->where(function($q) use ($code) {
                $q->where('vendor_code', $code)
                  ->orWhere('sub_vendor_code', $code);
            })->first();

            if ($v) {
                $label = ($v->hierarchy_level > 0 || str_starts_with($code, 'SV')) ? 'Sub-Vendor Partner' : 'Head Vendor Partner';
                return [
                    'code'       => $code,
                    'name'       => $v->designation ?: $label,
                    'type'       => 'vendor',
                    'type_label' => $label,
                    'verified'   => $v->status === 'approved',
                ];
            }
        }

        // 2. Freelancer
        if (str_starts_with($code, 'FR')) {
            $m = \Illuminate\Support\Facades\DB::table('marketing_team_members')->where('member_code', $code)->first();
            if ($m) {
                $mName = 'Field Freelancer';
                if ($m->user_type === 'customer') {
                    $u = \Illuminate\Support\Facades\DB::table('tj_user_app')->where('id', $m->user_id)->first();
                    if ($u) $mName = trim(($u->prenom ?? '') . ' ' . ($u->nom ?? '')) ?: $mName;
                } else {
                    $d = \Illuminate\Support\Facades\DB::table('tj_conducteur')->where('id', $m->user_id)->first();
                    if ($d) $mName = trim(($d->prenom ?? '') . ' ' . ($d->nom ?? '')) ?: $mName;
                }
                return [
                    'code'       => $code,
                    'name'       => $mName,
                    'type'       => 'freelancer',
                    'type_label' => 'Field Coordinator',
                    'verified'   => $m->status === 'active',
                ];
            }
        }

        // 3. Consumer / Driver / Restaurant
        $ref = \App\Services\ReferralCodeService::resolveReferrer($code);
        if ($ref) {
            $rType = $ref['user_type'] ?? 'customer';
            $rName = 'Fiinway Member';
            if ($rType === 'customer') {
                $u = \Illuminate\Support\Facades\DB::table('tj_user_app')->where('id', $ref['user_id'])->first();
                if ($u) $rName = trim(($u->prenom ?? '') . ' ' . ($u->nom ?? '')) ?: $rName;
                $label = 'User App Referral';
            } elseif ($rType === 'restaurant') {
                $rest = \Illuminate\Support\Facades\DB::table('food_restaurants')->where('id', $ref['user_id'])->orWhere('owner_id', $ref['user_id'])->first();
                if ($rest) $rName = $rest->name ?: $rName;
                $label = 'Restaurant Partner';
            } else {
                $d = \Illuminate\Support\Facades\DB::table('tj_conducteur')->where('id', $ref['user_id'])->first();
                if ($d) $rName = trim(($d->prenom ?? '') . ' ' . ($d->nom ?? '')) ?: $rName;
                $label = 'Business Driver App';
            }
            return [
                'code'       => $code,
                'name'       => $rName,
                'type'       => $rType,
                'type_label' => $label,
                'verified'   => true,
            ];
        }

        return [
            'code'       => $code,
            'name'       => 'Partner',
            'type'       => 'general',
            'type_label' => 'Referral Code',
            'verified'   => true,
        ];
    }

    public function sendTestNotification(Request $request)
    {
        $restaurantId = $request->input('restaurant_id', 8);
        $phone = $request->input('phone', '9669454554');

        $restaurant = null;
        if ($restaurantId) {
            $restaurant = FoodRestaurant::find($restaurantId);
        }

        $owner = null;
        if ($restaurant && $restaurant->owner_id) {
            $owner = FoodOwner::find($restaurant->owner_id);
        }

        if (!$owner && $phone) {
            $owner = $this->findOwnerByPhone($phone);
        }

        if (!$restaurant && $owner) {
            $restaurant = FoodRestaurant::where('owner_id', $owner->id)->first();
        }

        $tokens = [];
        if ($owner && !empty($owner->fcm_token)) {
            $tokens[] = ['source' => 'food_owners table (Restaurant App)', 'token' => $owner->fcm_token];
        }

        $checkPhones = array_filter([$phone, $owner->phone ?? null, $restaurant->owner_phone ?? null]);
        foreach ($checkPhones as $ph) {
            $variants = PhoneService::getVariants($ph);
            $uTokens = DB::table('tj_user_app')->whereIn('phone', $variants)->whereNotNull('fcm_id')->where('fcm_id', '!=', '')->pluck('fcm_id')->toArray();
            foreach ($uTokens as $ut) {
                $tokens[] = ['source' => 'tj_user_app (User App)', 'token' => $ut];
            }
            $dTokens = DB::table('tj_conducteur')->whereIn('phone', $variants)->whereNotNull('fcm_id')->where('fcm_id', '!=', '')->pluck('fcm_id')->toArray();
            foreach ($dTokens as $dt) {
                $tokens[] = ['source' => 'tj_conducteur (Driver App)', 'token' => $dt];
            }
        }

        $uniqueTokens = [];
        $seen = [];
        foreach ($tokens as $item) {
            if (!isset($seen[$item['token']])) {
                $seen[$item['token']] = true;
                $uniqueTokens[] = $item;
            }
        }

        $testPayload = [
            'id' => 'test_' . time(),
            'order_id' => '8888',
            'order_number' => 'FIIN-TEST-' . rand(1000, 9999),
            'title' => '🔔 Test Order Alert for ' . ($restaurant ? $restaurant->name : 'Restaurant'),
            'message' => 'Test Notification: Order FIIN-TEST incoming. Ringtone & alert check!',
            'body' => 'Test Notification: Order FIIN-TEST incoming. Ringtone & alert check!',
            'sound' => 'alert',
            'sound_type' => 'order_alarm',
            'type' => 'food_new_order',
            'android_channel_id' => 'food_orders_channel',
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
        ];

        $results = [];
        foreach ($uniqueTokens as $t) {
            $res = \App\Http\Controllers\API\v1\GcmController::sendNotification($t['token'], $testPayload);
            $results[] = [
                'source' => $t['source'],
                'token' => substr($t['token'], 0, 25) . '...',
                'response' => ($res instanceof \Illuminate\Http\JsonResponse) ? $res->getData() : $res,
            ];
        }

        return response()->json([
            'success' => count($results) > 0,
            'restaurant' => $restaurant ? ['id' => $restaurant->id, 'name' => $restaurant->name, 'status' => $restaurant->onboarding_status] : null,
            'owner' => $owner ? ['id' => $owner->id, 'name' => $owner->name, 'phone' => $owner->phone, 'has_fcm' => !empty($owner->fcm_token)] : null,
            'tokens_found_count' => count($uniqueTokens),
            'dispatches' => $results,
            'note' => empty($uniqueTokens)
                ? 'No FCM token is currently registered for Mohammed in food_owners table. Mohammed needs to launch the updated Restaurant App on his phone so the app registers his device FCM token with the server.'
                : 'Notification dispatched to registered device token(s).'
        ]);
    }
}
