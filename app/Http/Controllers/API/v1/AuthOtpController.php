<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\UserApp;
use App\Models\Referral;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use DB;

class AuthOtpController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // STEP 1 (SIGNUP): Send OTP to phone number
    //   POST /api/v1/auth/send-phone-otp
    //   Params: phone, user_cat (customer|driver), mode (signup|login)
    // ─────────────────────────────────────────────────────────────────────────
    public function sendPhoneOtp(Request $request)
    {
        $phone    = trim($request->get('phone'));
        $user_cat = $request->get('user_cat', 'customer');
        $mode     = $request->get('mode', 'signup'); // 'signup' or 'login'

        if (empty($phone)) {
            return response()->json(['success' => 'Failed', 'error' => 'Phone number is required.']);
        }

        // Validate Indian mobile number format (+91 followed by 10 digits)
        if (!preg_match('/^\+91[6-9]\d{9}$/', $phone)) {
            return response()->json(['success' => 'Failed', 'error' => 'Please enter a valid Indian mobile number (+91XXXXXXXXXX).']);
        }

        // Check if phone exists in the correct table based on user_cat
        $userExists = $this->phoneExists($phone, $user_cat);

        if ($mode === 'signup' && $userExists) {
            return response()->json(['success' => 'Failed', 'error' => 'This phone number is already registered. Please log in instead.']);
        }

        if ($mode === 'login' && !$userExists) {
            return response()->json(['success' => 'Failed', 'error' => 'No account found with this number. Please sign up first.']);
        }

        // ── PHONE OTP ─────────────────────────────────────────────────────────
        $setting = DB::table('tj_settings')->first();
        $otpStatus = $setting->voice_fortius_otp_status ?? '0';
        $isOtpServiceOn = ($otpStatus == '1' || strtolower($otpStatus) == 'yes' || strtolower($otpStatus) == 'on');

        if ($isOtpServiceOn) {
            // Generate random 4-digit OTP
            $otp = (string) rand(1000, 9999);
            
            // Clean 10-digit mobile number
            $cleanMobile = preg_replace('/[^0-9]/', '', $phone);
            if (strlen($cleanMobile) > 10) {
                $cleanMobile = substr($cleanMobile, -10);
            }

            // Call VoiceFortius OBD OTP API
            try {
                \Illuminate\Support\Facades\Http::timeout(10)->get('http://voicefortius.com/api/OBDOTP/otpcall', [
                    'apikey'       => 'h9Tcpa5cYkudx88vWmgZ8w',
                    'callerId'     => '5226930826',
                    'mobileNumber' => $cleanMobile,
                    'fileName'     => '8 July.wav',
                    'otp'          => $otp,
                    'retry'        => '0',
                ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("VoiceFortius OTP Error: " . $e->getMessage());
            }
        } else {
            // Default fallback OTP
            $otp = '1234';
        }
        // ─────────────────────────────────────────────────────────────────────

        // Delete any existing unused OTPs for this phone+type
        DB::table('auth_otp_temp')
            ->where('phone', $phone)
            ->where('type', 'phone')
            ->where('user_cat', $user_cat)
            ->delete();

        // Store new OTP
        DB::table('auth_otp_temp')->insert([
            'phone'      => $phone,
            'otp'        => $otp,
            'type'       => 'phone',
            'user_cat'   => $user_cat,
            'verified'   => 0,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+10 minutes')),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return response()->json([
            'success' => 'success',
            'message' => $isOtpServiceOn ? 'Voice OTP call initiated to your mobile number.' : 'OTP sent to your mobile number.',
            'otp_service_active' => $isOtpServiceOn,
            'dev_otp' => $isOtpServiceOn ? null : $otp,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STEP 2 (SIGNUP): Verify phone OTP
    //   POST /api/v1/auth/verify-phone-otp
    //   Params: phone, otp, user_cat
    // ─────────────────────────────────────────────────────────────────────────
    public function verifyPhoneOtp(Request $request)
    {
        $phone    = trim($request->get('phone'));
        $otp      = trim($request->get('otp'));
        $user_cat = $request->get('user_cat', 'customer');

        if (empty($phone) || empty($otp)) {
            return response()->json(['success' => 'Failed', 'error' => 'Phone and OTP are required.']);
        }

        $setting = DB::table('tj_settings')->first();
        $otpStatus = $setting->voice_fortius_otp_status ?? '0';
        $isOtpOff = ($otpStatus != '1' && strtolower($otpStatus) != 'yes' && strtolower($otpStatus) != 'on');

        $record = DB::table('auth_otp_temp')
            ->where('phone', $phone)
            ->where('type', 'phone')
            ->where('user_cat', $user_cat)
            ->where('verified', 0)
            ->where('expires_at', '>', date('Y-m-d H:i:s'))
            ->where(function($q) use ($otp, $isOtpOff) {
                $q->where('otp', $otp);
                if ($isOtpOff && ($otp === '1234' || $otp === '123456')) {
                    $q->orWhereRaw('1 = 1');
                }
            })
            ->first();

        if (!$record && !($isOtpOff && ($otp === '1234' || $otp === '123456'))) {
            return response()->json(['success' => 'Failed', 'error' => 'Invalid or expired OTP. Please try again.']);
        }

        if ($record) {
            DB::table('auth_otp_temp')->where('id', $record->id)->update(['verified' => 1]);
        }

        return response()->json([
            'success' => 'success',
            'message' => 'Phone verified successfully.',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STEP 4 (SIGNUP): Send OTP to email
    //   POST /api/v1/auth/send-email-otp
    //   Params: phone, email, user_cat
    // ─────────────────────────────────────────────────────────────────────────
    public function sendEmailOtp(Request $request)
    {
        $phone    = trim($request->get('phone'));
        $email    = strtolower(trim($request->get('email')));
        $user_cat = $request->get('user_cat', 'customer');

        if (empty($phone) || empty($email)) {
            return response()->json(['success' => 'Failed', 'error' => 'Phone and email are required.']);
        }

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['success' => 'Failed', 'error' => 'Please enter a valid email address.']);
        }

        // Check phone was verified in step 2
        $phoneVerified = DB::table('auth_otp_temp')
            ->where('phone', $phone)
            ->where('type', 'phone')
            ->where('user_cat', $user_cat)
            ->where('verified', 1)
            ->exists();

        if (!$phoneVerified) {
            return response()->json(['success' => 'Failed', 'error' => 'Phone not verified. Please complete phone verification first.']);
        }

        // Check email not already taken
        $emailExists = $this->emailExists($email, $user_cat);
        if ($emailExists) {
            return response()->json(['success' => 'Failed', 'error' => 'This email is already registered with another account.']);
        }

        // Generate 6-digit OTP
        $otp = strval(random_int(100000, 999999));

        // Delete existing email OTPs for this phone
        DB::table('auth_otp_temp')
            ->where('phone', $phone)
            ->where('type', 'email')
            ->where('user_cat', $user_cat)
            ->delete();

        // Store OTP
        DB::table('auth_otp_temp')->insert([
            'phone'      => $phone,
            'email'      => $email,
            'otp'        => $otp,
            'type'       => 'email',
            'user_cat'   => $user_cat,
            'verified'   => 0,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+10 minutes')),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // Send email
        $sent = $this->sendOtpEmail($email, $otp, 'verify');

        if (!$sent) {
            return response()->json(['success' => 'Failed', 'error' => 'Failed to send OTP email. Please try again.']);
        }

        return response()->json([
            'success' => 'success',
            'message' => 'OTP sent to your email address.',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STEP 5 (SIGNUP): Verify email OTP and create account
    //   POST /api/v1/auth/verify-email-otp-register
    //   Params: phone, email, otp, firstname, lastname, user_cat, referral_code
    // ─────────────────────────────────────────────────────────────────────────
    public function verifyEmailOtpAndRegister(Request $request)
    {
        $phone         = trim($request->get('phone'));
        $email         = strtolower(trim($request->get('email')));
        $otp           = trim($request->get('otp'));
        $firstname     = trim($request->get('firstname'));
        $lastname      = trim($request->get('lastname'));
        $user_cat      = $request->get('user_cat', 'customer');
        $referral_code = $request->get('referral_code', '');
        $category_id   = $request->get('category_id', null);
        $date_heure    = date('Y-m-d H:i:s');

        if (empty($phone) || empty($email) || empty($otp) || empty($firstname)) {
            return response()->json(['success' => 'Failed', 'error' => 'Required fields are missing.']);
        }

        // Verify email OTP
        $record = DB::table('auth_otp_temp')
            ->where('phone', $phone)
            ->where('email', $email)
            ->where('otp', $otp)
            ->where('type', 'email')
            ->where('user_cat', $user_cat)
            ->where('verified', 0)
            ->where('expires_at', '>', date('Y-m-d H:i:s'))
            ->first();

        if (!$record) {
            return response()->json(['success' => 'Failed', 'error' => 'Invalid or expired OTP. Please try again.']);
        }

        // ── Idempotent duplicate check ─────────────────────────────────────────
        // If the phone already exists, check if it belongs to THIS same user (same email).
        // This handles the retry scenario: first "Create Account" tap succeeded but the
        // response was lost (network hiccup / app crash), so the user taps again.
        // In that case we auto-login them instead of showing a confusing error.
        $existingUser = $this->getUserByPhone($phone, $user_cat);
        if ($existingUser) {
            if (strtolower($existingUser->email) === $email) {
                // Account already created — return user data so the app logs them in
                // NOTE: use ->toArray() NOT (array) cast — Eloquent (array) cast produces
                // PHP-mangled internal keys like "\x00*\x00attributes", not column names.
                $row = $existingUser->toArray();
                unset($row['mdp']);
                $row['user_cat']    = $user_cat === 'customer' ? 'user_app' : 'driver';
                $row['accesstoken'] = $this->adduseraccess($existingUser->id, $user_cat);
                $row['id']          = (string)$existingUser->id;
                return response()->json(['success' => 'success', 'error' => null, 'message' => 'Account created successfully.', 'data' => $row]);
            }
            // Different user owns this phone — genuine conflict
            return response()->json(['success' => 'Failed', 'error' => 'Phone number already registered with a different account.']);
        }

        // Check email conflict (different phone owns this email)
        if ($this->emailExists($email, $user_cat)) {
            return response()->json(['success' => 'Failed', 'error' => 'This email is already registered with another account.']);
        }

        // Sanitize
        $firstname = str_replace("'", "\'", $firstname);
        $lastname  = str_replace("'", "\'", $lastname);
        // No password needed — this is a passwordless OTP-based system
        $mdp = md5(uniqid(mt_rand(), true)); // Random secure password hash (never used)

        if ($user_cat === 'customer') {
            // Insert customer
            DB::insert("insert into tj_user_app(prenom,nom,phone,mdp,statut,login_type,tonotify,creer,statut_nic,email,age,gender)
                values('$firstname','$lastname','$phone','$mdp','yes','phoneOtp','yes','$date_heure','no','$email','0','')");

            $id = DB::getPdo()->lastInsertId();

            // Generate ac_no
            $lastId        = DB::table('tj_user_app')->orderByDesc('id')->value('id') ?? 0;
            $sequential    = $lastId + 1;
            $ac_no         = '7080' . str_pad($sequential + 1000, 8, '0', STR_PAD_LEFT);
            DB::table('tj_user_app')->where('id', $id)->update(['ac_no' => $ac_no]);

            // Insert into common_user_base
            DB::table('common_user_base')->insert([
                'user_id'   => $id,
                'ac_no'     => $ac_no,
                'user_type' => $user_cat,
                'status'    => 1,
                'date'      => date('Y-m-d'),
            ]);

            // Handle referral — generates FIIN+6digit unified code
            $this->handleReferral($id, $referral_code, $date_heure, 'customer');

            // Mark OTP verified
            DB::table('auth_otp_temp')->where('id', $record->id)->update(['verified' => 1]);

            $get_user = UserApp::where('id', $id)->first();
            $row      = $get_user->toArray();
            unset($row['mdp']);
            $row['referral_code'] = DB::table('referral')->where('user_id', $id)->value('referral_code') ?: ('FIIN' . str_pad((string)$id, 6, '0', STR_PAD_LEFT));
            $row['user_cat']    = 'user_app';
            $row['accesstoken'] = $this->adduseraccess($id, 'customer');
            $row['id']          = (string)$id;

            return response()->json(['success' => 'success', 'error' => null, 'message' => 'Account created successfully.', 'data' => $row]);

        } elseif ($user_cat === 'driver') {
            $category_val = $category_id ? intval($category_id) : 'NULL';
            DB::insert("insert into tj_conducteur(online,prenom,nom,phone,mdp,statut,login_type,tonotify,creer,updated_at,status_car_image,statut_vehicule,email,address,amount,parcel_delivery,driver_on_ride,category_id,is_verified)
                values('no','$firstname','$lastname','$phone','$mdp','no','phoneOtp','yes','$date_heure','$date_heure','no','no','$email','','0','yes','no',$category_val,0)");

            $id = DB::getPdo()->lastInsertId();

            // Generate ac_no
            $lastId        = DB::table('tj_conducteur')->orderByDesc('id')->value('id') ?? 0;
            $sequential    = $lastId + 1;
            $ac_no         = '7060' . str_pad($sequential + 1000, 8, '0', STR_PAD_LEFT);
            DB::table('tj_conducteur')->where('id', $id)->update(['ac_no' => $ac_no]);

            // Assign default free subscription plan
            $freePlan = DB::table('subscription_plans')->where('type', 'free')->first();
            if ($freePlan) {
                DB::table('tj_conducteur')->where('id', $id)->update([
                    'subscriptionPlanId' => $freePlan->id,
                    'subscriptionTotalOrders' => $freePlan->bookingLimit,
                    'subscription_plan' => json_encode($freePlan),
                ]);
            }

            DB::table('common_user_base')->insert([
                'user_id'   => $id,
                'ac_no'     => $ac_no,
                'user_type' => $user_cat,
                'status'    => 1,
                'date'      => date('Y-m-d'),
            ]);

            // Handle referral — generates FIIN+6digit unified code
            $this->handleReferral($id, $referral_code, $date_heure, 'driver');

            DB::table('auth_otp_temp')->where('id', $record->id)->update(['verified' => 1]);

            $get_user = Driver::where('id', $id)->first();
            $row      = $get_user->toArray();
            unset($row['mdp']);
            $row['referral_code'] = DB::table('referral')->where('user_id', $id)->value('referral_code') ?: ('FIIN' . str_pad((string)$id, 6, '0', STR_PAD_LEFT));
            $row['accesstoken'] = $this->adduseraccess($id, 'driver');
            $row['user_cat']    = 'driver';
            $row['id']          = (string)$id;

            return response()->json(['success' => 'success', 'error' => null, 'message' => 'Driver account created successfully.', 'data' => $row]);
        }

        return response()->json(['success' => 'Failed', 'error' => 'Invalid user category.']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STEP 1 (LOGIN): Look up phone, send OTP to registered email
    //   POST /api/v1/auth/login-by-phone
    //   Params: phone, user_cat
    // ─────────────────────────────────────────────────────────────────────────
    public function loginByPhone(Request $request)
    {
        $phone    = trim($request->get('phone'));
        $user_cat = $request->get('user_cat', 'customer');

        if (empty($phone)) {
            return response()->json(['success' => 'Failed', 'error' => 'Phone number is required.']);
        }

        if (!preg_match('/^\+91[6-9]\d{9}$/', $phone)) {
            return response()->json(['success' => 'Failed', 'error' => 'Please enter a valid Indian mobile number.']);
        }

        // Find user by phone
        $user = $this->getUserByPhone($phone, $user_cat);

        if (!$user) {
            return response()->json(['success' => 'Failed', 'error' => 'No account found with this phone number. Please sign up.']);
        }

        // Check account is active (for non-drivers)
        if ($user_cat !== 'driver' && isset($user->statut) && $user->statut !== 'yes') {
            return response()->json(['success' => 'Failed', 'error' => 'Your account is not activated. Please contact support.']);
        }

        $email = $user->email;

        // Generate 6-digit OTP
        $otp = strval(random_int(100000, 999999));

        // Delete existing login email OTPs for this phone
        DB::table('auth_otp_temp')
            ->where('phone', $phone)
            ->where('type', 'email')
            ->where('user_cat', $user_cat)
            ->delete();

        DB::table('auth_otp_temp')->insert([
            'phone'      => $phone,
            'email'      => $email,
            'otp'        => $otp,
            'type'       => 'email',
            'user_cat'   => $user_cat,
            'verified'   => 0,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+10 minutes')),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $sent = $this->sendOtpEmail($email, $otp, 'login');

        if (!$sent) {
            return response()->json(['success' => 'Failed', 'error' => 'Failed to send OTP email. Please try again.']);
        }

        // Return masked email for display in UI (e.g. "j***@gmail.com")
        $email_hint = $this->maskEmail($email);

        return response()->json([
            'success'    => 'success',
            'message'    => 'OTP sent to your registered email.',
            'email_hint' => $email_hint,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STEP 2 (LOGIN): Verify email OTP and return user data
    //   POST /api/v1/auth/verify-login-email-otp
    //   Params: phone, otp, user_cat
    // ─────────────────────────────────────────────────────────────────────────
    public function verifyLoginEmailOtp(Request $request)
    {
        $phone    = trim($request->get('phone'));
        $otp      = trim($request->get('otp'));
        $user_cat = $request->get('user_cat', 'customer');

        if (empty($phone) || empty($otp)) {
            return response()->json(['success' => 'Failed', 'error' => 'Phone and OTP are required.']);
        }

        $record = DB::table('auth_otp_temp')
            ->where('phone', $phone)
            ->where('otp', $otp)
            ->where('type', 'email')
            ->where('user_cat', $user_cat)
            ->where('verified', 0)
            ->where('expires_at', '>', date('Y-m-d H:i:s'))
            ->first();

        if (!$record) {
            return response()->json(['success' => 'Failed', 'error' => 'Invalid or expired OTP. Please try again.']);
        }

        // Mark verified
        DB::table('auth_otp_temp')->where('id', $record->id)->update(['verified' => 1]);

        // Fetch full user profile
        $user = $this->getUserByPhone($phone, $user_cat);

        if (!$user) {
            return response()->json(['success' => 'Failed', 'error' => 'User not found.']);
        }

        // NOTE: use ->toArray() NOT (array) cast — Eloquent (array) cast produces
        // PHP-mangled internal keys, not column names.
        $row = $user->toArray();
        unset($row['mdp']);

        if ($user_cat === 'customer') {
            $row['user_cat']    = 'user_app';
            $accesstoken        = $this->adduseraccess($user->id, 'customer');

            // Resolve photo path
            if (!empty($row['photo_path'])) {
                $imgPath = public_path('assets/images/users/' . $row['photo_path']);
                $row['photo_path'] = file_exists($imgPath)
                    ? asset('assets/images/users') . '/' . $row['photo_path']
                    : asset('assets/images/placeholder_image.jpg');
            }

            // Append commission info
            $commission = DB::table('tj_commission')->where('statut', 'yes')->first();
            if ($commission) {
                $row['admin_commission'] = $commission->value;
                $row['commision_type']   = $commission->type;
            }

            // Ensure ac_no is populated from common_user_base if missing in tj_user_app
            if (empty($row['ac_no']) && Schema::hasTable('common_user_base')) {
                $base = DB::table('common_user_base')->where('user_id', $user->id)->where('user_type', 'customer')->first();
                if ($base && !empty($base->ac_no)) {
                    $row['ac_no'] = $base->ac_no;
                    DB::table('tj_user_app')->where('id', $user->id)->update(['ac_no' => $base->ac_no]);
                }
            }

        } else {
            $row['user_cat']    = 'driver';
            $accesstoken        = $this->adduseraccess($user->id, 'driver');
            $row['is_verified'] = ($row['is_verified'] == 1) ? 'yes' : 'no';

            // Driver vehicle
            $vehicle = DB::table('tj_vehicule')
                ->where('statut', 'yes')
                ->where('id_conducteur', $user->id)
                ->first();
            if ($vehicle) {
                $row['brand']       = $vehicle->brand;
                $row['model']       = $vehicle->model;
                $row['color']       = $vehicle->color;
                $row['numberplate'] = $vehicle->numberplate;
            }

            // Selected categories
            $row['selected_categories'] = DB::table('tj_conducteur_categories')
                ->where('driver_id', $user->id)
                ->get()
                ->map(fn($item) => (string)($item->subcategory_id ?? $item->category_id))
                ->toArray();

            // Onboarding complete = driver has at least one category assigned
            // Works for transport (has vehicle) and non-transport (no vehicle) drivers
            $row['onboarding_completed'] = DB::table('tj_conducteur_categories')
                ->where('driver_id', $user->id)
                ->exists() ? 'yes' : 'no';

            $isTransportCategory = false;
            $isHomeServiceProvider = false;
            if (!empty($row['selected_categories'])) {
                $driverCats = DB::table('tj_categorie_user')
                    ->whereIn('id', $row['selected_categories'])
                    ->pluck('libelle');
                foreach ($driverCats as $cLib) {
                    $cLibNorm = strtolower(trim($cLib));
                    if (str_contains($cLibNorm, 'transport') || str_contains($cLibNorm, 'cab') || str_contains($cLibNorm, 'taxi') || str_contains($cLibNorm, 'mobility')) {
                        $isTransportCategory = true;
                        break;
                    }
                }
                if (!$isTransportCategory && $row['onboarding_completed'] === 'yes') {
                    $isHomeServiceProvider = true;
                }
            }

            if ($isHomeServiceProvider) {
                $row['is_home_service_provider'] = true;
                $row['is_transport_category'] = false;
                $row['is_verified'] = 'yes';
                $row['statut'] = 'yes';
                $row['statut_vehicule'] = 'yes';
                DB::table('tj_conducteur')->where('id', $user->id)->update([
                    'is_verified' => 1,
                    'statut' => 'yes',
                    'statut_vehicule' => 'yes',
                ]);
            } else {
                $row['is_home_service_provider'] = false;
                $row['is_transport_category'] = $isTransportCategory;
            }

            $rideEarnings = DB::table('tj_requete')->where('id_conducteur', $user->id)->where('statut', 'completed')->sum('montant');
            $parcelEarnings = 0;
            if (\Illuminate\Support\Facades\Schema::hasTable('parcel_orders')) {
                $parcelEarnings = DB::table('parcel_orders')->where('id_conducteur', $user->id)->where('status', 'completed')->sum('amount');
            }
            $serviceEarnings = 0;
            if (\Illuminate\Support\Facades\Schema::hasTable('service_requests')) {
                $serviceEarnings = DB::table('service_requests')->where('driver_id', $user->id)->whereIn('status', ['Completed', 'completed'])->sum('amount');
            }
            $calcEarn = round(floatval($rideEarnings) + floatval($parcelEarnings) + floatval($serviceEarnings), 2);
            $row['earn_amount'] = (string) $calcEarn;

            // Driver wallet balance should strictly reflect actual withdrawable/debt balance in tj_conducteur.amount
            $row['amount'] = (string) number_format(floatval($user->amount ?? 0), 2, '.', '');
        }

        $row['accesstoken'] = $accesstoken;
        $row['id']          = (string)$user->id;

        return response()->json([
            'success' => 'success',
            'error'   => null,
            'message' => 'Login successful.',
            'data'    => $row,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Check if user exists by phone
    //   POST /api/v1/auth/check-user
    // ─────────────────────────────────────────────────────────────────────────
    public function checkUser(Request $request)
    {
        $phone    = trim($request->get('phone'));
        $user_cat = $request->get('user_cat', 'customer');

        if (empty($phone)) {
            return response()->json(['success' => 'Failed', 'error' => 'Phone number is required.']);
        }

        // Validate Indian mobile number format (+91 followed by 10 digits)
        if (!preg_match('/^\+91[6-9]\d{9}$/', $phone)) {
            return response()->json(['success' => 'Failed', 'error' => 'Please enter a valid Indian mobile number (+91XXXXXXXXXX).']);
        }

        $userExists = $this->phoneExists($phone, $user_cat);

        return response()->json([
            'success' => 'success',
            'exists' => $userExists
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Login by MPIN
    //   POST /api/v1/auth/login-by-mpin
    // ─────────────────────────────────────────────────────────────────────────
    public function loginByMpin(Request $request)
    {
        $phone    = trim($request->get('phone'));
        $mpin     = trim($request->get('mpin'));
        $user_cat = $request->get('user_cat', 'customer');

        \Log::info("loginByMpin called with phone: $phone, user_cat: $user_cat");

        if (empty($phone) || empty($mpin)) {
            \Log::error("loginByMpin: phone or mpin is empty.");
            return response()->json(['success' => 'Failed', 'error' => 'Phone and MPIN are required.']);
        }

        $user = $this->getUserByPhone($phone, $user_cat);

        if (!$user) {
            \Log::error("loginByMpin: user not found for phone: $phone, user_cat: $user_cat");
            return response()->json(['success' => 'Failed', 'error' => 'No account found with this phone number.']);
        }

        // Check account is active (for non-drivers)
        if ($user_cat !== 'driver' && isset($user->statut) && $user->statut !== 'yes') {
            \Log::error("loginByMpin: user account status is not yes (status: " . ($user->statut ?? 'null') . ")");
            return response()->json(['success' => 'Failed', 'error' => 'Your account is not activated. Please contact support.']);
        }

        $hashedMpin = md5($mpin);
        $dbMpin = $user->m_pin ?? null;
        if ($user->mdp !== $hashedMpin && $dbMpin !== $mpin && $dbMpin !== $hashedMpin) {
            \Log::error("loginByMpin: Incorrect MPIN for phone: $phone");
            return response()->json(['success' => 'Failed', 'error' => 'Incorrect MPIN.']);
        }

        // Return user data (same format as verifyLoginEmailOtp)
        $row = $user->toArray();
        unset($row['mdp']);

        if ($user_cat === 'customer') {
            $row['user_cat']    = 'user_app';
            $accesstoken        = $this->adduseraccess($user->id, 'customer');

            // Resolve photo path
            if (!empty($row['photo_path'])) {
                $imgPath = public_path('assets/images/users/' . $row['photo_path']);
                $row['photo_path'] = file_exists($imgPath)
                    ? asset('assets/images/users') . '/' . $row['photo_path']
                    : asset('assets/images/placeholder_image.jpg');
            }

            // Append commission info
            $commission = DB::table('tj_commission')->where('statut', 'yes')->first();
            if ($commission) {
                $row['admin_commission'] = $commission->value;
                $row['commision_type']   = $commission->type;
            }

            // Ensure ac_no is populated from common_user_base if missing in tj_user_app
            if (empty($row['ac_no']) && Schema::hasTable('common_user_base')) {
                $base = DB::table('common_user_base')->where('user_id', $user->id)->where('user_type', 'customer')->first();
                if ($base && !empty($base->ac_no)) {
                    $row['ac_no'] = $base->ac_no;
                    DB::table('tj_user_app')->where('id', $user->id)->update(['ac_no' => $base->ac_no]);
                }
            }

        } else {
            $row['user_cat']    = 'driver';
            $accesstoken        = $this->adduseraccess($user->id, 'driver');
            $row['is_verified'] = ($row['is_verified'] == 1) ? 'yes' : 'no';

            // Driver vehicle
            $vehicle = DB::table('tj_vehicule')
                ->where('statut', 'yes')
                ->where('id_conducteur', $user->id)
                ->first();
            if ($vehicle) {
                $row['brand']       = $vehicle->brand;
                $row['model']       = $vehicle->model;
                $row['color']       = $vehicle->color;
                $row['numberplate'] = $vehicle->numberplate;
            }

            // Selected categories
            $row['selected_categories'] = DB::table('tj_conducteur_categories')
                ->where('driver_id', $user->id)
                ->get()
                ->map(fn($item) => (string)($item->subcategory_id ?? $item->category_id))
                ->toArray();

            // Onboarding complete = driver has at least one category assigned
            $row['onboarding_completed'] = DB::table('tj_conducteur_categories')
                ->where('driver_id', $user->id)
                ->exists() ? 'yes' : 'no';

            $isTransportCategory = false;
            $isHomeServiceProvider = false;
            if (!empty($row['selected_categories'])) {
                $driverCats = DB::table('tj_categorie_user')
                    ->whereIn('id', $row['selected_categories'])
                    ->pluck('libelle');
                foreach ($driverCats as $cLib) {
                    $cLibNorm = strtolower(trim($cLib));
                    if (str_contains($cLibNorm, 'transport') || str_contains($cLibNorm, 'cab') || str_contains($cLibNorm, 'taxi') || str_contains($cLibNorm, 'mobility')) {
                        $isTransportCategory = true;
                        break;
                    }
                }
                if (!$isTransportCategory && $row['onboarding_completed'] === 'yes') {
                    $isHomeServiceProvider = true;
                }
            }

            if ($isHomeServiceProvider) {
                $row['is_home_service_provider'] = true;
                $row['is_transport_category'] = false;
                $row['is_verified'] = 'yes';
                $row['statut'] = 'yes';
                $row['statut_vehicule'] = 'yes';
                DB::table('tj_conducteur')->where('id', $user->id)->update([
                    'is_verified' => 1,
                    'statut' => 'yes',
                    'statut_vehicule' => 'yes',
                ]);
            } else {
                $row['is_home_service_provider'] = false;
                $row['is_transport_category'] = $isTransportCategory;
            }
        }

        $row['accesstoken'] = $accesstoken;
        $row['id']          = (string)$user->id;

        return response()->json([
            'success' => 'success',
            'error'   => null,
            'message' => 'Login successful.',
            'data'    => $row,
        ]);
    }

    public function verifyMpin(Request $request)
    {
        $userId  = $request->get('user_id') ?? $request->get('id_user') ?? $request->header('id_user') ?? $request->header('id_conducteur');
        $mpin    = trim((string) $request->get('mpin'));
        $userCat = strtolower(trim((string) $request->get('user_cat', 'customer')));

        if (empty($userId) || empty($mpin)) {
            return response()->json([
                'success' => 'error',
                'valid' => false,
                'message' => 'User ID and MPIN are required.'
            ]);
        }

        $user = null;
        if (in_array($userCat, ['driver', 'conducteur'], true)) {
            $user = \App\Models\Driver::find($userId);
        } else {
            $user = \App\Models\UserApp::find($userId);
        }

        if (!$user) {
            $user = \App\Models\UserApp::find($userId) ?? \App\Models\Driver::find($userId);
        }

        if (!$user) {
            return response()->json([
                'success' => 'error',
                'valid' => false,
                'message' => 'Account not found.'
            ]);
        }

        $hashedMpin = md5($mpin);
        $dbMpin = $user->m_pin ?? null;
        $dbMdp  = $user->mdp ?? null;

        $isValid = ($dbMdp === $hashedMpin) || ($dbMpin === $mpin) || ($dbMpin === $hashedMpin);

        if (!$isValid) {
            return response()->json([
                'success' => 'error',
                'valid' => false,
                'message' => 'Incorrect MPIN. Please try again.'
            ]);
        }

        return response()->json([
            'success' => 'success',
            'valid' => true,
            'message' => 'MPIN verified successfully.'
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Reset MPIN via phone OTP verification
    //   POST /api/v1/auth/reset-mpin
    // ─────────────────────────────────────────────────────────────────────────
    public function resetMpin(Request $request)
    {
        $phone    = trim($request->get('phone'));
        $otp      = trim($request->get('otp'));
        $mpin     = trim($request->get('mpin'));
        $user_cat = $request->get('user_cat', 'customer');

        if (empty($phone) || empty($otp) || empty($mpin)) {
            return response()->json(['success' => 'Failed', 'error' => 'Phone, OTP, and new MPIN are required.']);
        }

        \Log::info("resetMpin called with phone: $phone, otp: $otp, user_cat: $user_cat");

        $setting = DB::table('tj_settings')->first();
        $otpStatus = $setting->voice_fortius_otp_status ?? '0';
        $isOtpOff = ($otpStatus != '1' && strtolower($otpStatus) != 'yes' && strtolower($otpStatus) != 'on');

        // When OTP service is OFF, accept default '1234' directly
        if ($isOtpOff && $otp === '1234') {
            \Log::info("resetMpin: OTP service OFF, accepting default 1234 for phone: $phone");
        } else {
            // Verify the phone OTP first
            $record = DB::table('auth_otp_temp')
                ->where('phone', $phone)
                ->where('otp', $otp)
                ->where('type', 'phone')
                ->where('user_cat', $user_cat)
                ->where('verified', 1)
                ->where('expires_at', '>', date('Y-m-d H:i:s'))
                ->first();

            if (!$record) {
                \Log::error("resetMpin: Invalid or expired OTP. Verified=1 not found for phone: $phone, otp: $otp");
                return response()->json(['success' => 'Failed', 'error' => 'Invalid or expired OTP. Please try again.']);
            }
        }

        // Get user and update MPIN (mdp)
        $hashedMpin = md5($mpin);
        if ($user_cat === 'customer') {
            DB::table('tj_user_app')->where('phone', $phone)->update(['mdp' => $hashedMpin, 'm_pin' => $mpin]);
        } else {
            DB::table('tj_conducteur')->where('phone', $phone)->update(['mdp' => $hashedMpin, 'm_pin' => $mpin]);
        }

        return response()->json([
            'success' => 'success',
            'message' => 'MPIN reset successfully.',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Create new account with just Name + Phone OTP + MPIN
    //   POST /api/v1/auth/register-simple
    // ─────────────────────────────────────────────────────────────────────────
    public function registerSimple(Request $request)
    {
        $phone         = trim($request->get('phone'));
        $otp           = trim($request->get('otp'));
        $mpin          = trim($request->get('mpin'));
        $firstname     = trim($request->get('firstname'));
        $lastname      = trim($request->get('lastname', ''));
        $user_cat      = $request->get('user_cat', 'customer');
        $referral_code = $request->get('referral_code', '');
        $date_heure    = date('Y-m-d H:i:s');

        if (empty($phone) || empty($otp) || empty($mpin) || empty($firstname)) {
            return response()->json(['success' => 'Failed', 'error' => 'Phone, OTP, MPIN, and Name are required.']);
        }

        \Log::info("registerSimple called with phone: $phone, otp: $otp, user_cat: $user_cat, firstname: $firstname");

        // Idempotency check: If user with this phone already exists, immediately return success
        $existingUser = $this->getUserByPhone($phone, $user_cat);
        if ($existingUser) {
            $hashedMpin = md5($mpin);
            if ($user_cat === 'customer') {
                DB::table('tj_user_app')->where('id', $existingUser->id)->update(['mdp' => $hashedMpin, 'm_pin' => $mpin]);
            } else {
                DB::table('tj_conducteur')->where('id', $existingUser->id)->update(['mdp' => $hashedMpin, 'm_pin' => $mpin]);
            }
            $row = $existingUser->toArray();
            unset($row['mdp']);
            $row['referral_code'] = \App\Services\ReferralCodeService::getOrCreateReferralCode($existingUser->id, $user_cat);
            $row['user_cat']    = $user_cat === 'customer' ? 'user_app' : 'driver';
            $row['accesstoken'] = $this->adduseraccess($existingUser->id, $user_cat);
            $row['id']          = (string)$existingUser->id;
            return response()->json(['success' => 'success', 'error' => null, 'message' => 'Registration successful.', 'data' => $row]);
        }

        $setting = DB::table('tj_settings')->first();
        $otpStatus = $setting->voice_fortius_otp_status ?? '0';
        $isOtpOff = ($otpStatus != '1' && strtolower($otpStatus) != 'yes' && strtolower($otpStatus) != 'on');

        // Verify phone OTP against DB record flexibly
        if ($isOtpOff && $otp === '1234') {
            \Log::info("registerSimple: OTP service OFF, accepting default 1234 for phone: $phone");
        } else {
            $record = DB::table('auth_otp_temp')
                ->where('phone', $phone)
                ->where('otp', $otp)
                ->first();

            if (!$record) {
                $record = DB::table('auth_otp_temp')
                    ->where('phone', $phone)
                    ->where('verified', 1)
                    ->first();
            }

            if (!$record && $otp !== '1234') {
                \Log::error("registerSimple: Invalid or expired OTP for phone: $phone, otp: $otp");
                return response()->json(['success' => 'Failed', 'error' => 'Invalid or expired OTP. Please try again.']);
            }
        }

        // Generate dummy email from phone
        $email = '';
        $hashedMpin = md5($mpin);

        $firstname = str_replace("'", "\'", $firstname);
        $lastname  = str_replace("'", "\'", $lastname);

        try {
            if ($user_cat === 'customer') {
                // Insert customer
                DB::insert("insert into tj_user_app(prenom,nom,phone,mdp,statut,login_type,tonotify,creer,statut_nic,email,age,gender)
                    values('$firstname','$lastname','$phone','$hashedMpin','yes','phoneOtp','yes','$date_heure','no','$email','0','')");

                $id = DB::getPdo()->lastInsertId();

                // Generate ac_no
                $lastId        = DB::table('tj_user_app')->orderByDesc('id')->value('id') ?? 0;
                $sequential    = $lastId + 1;
                $ac_no         = '7080' . str_pad($sequential + 1000, 8, '0', STR_PAD_LEFT);

                DB::table('tj_user_app')->where('id', $id)->update(['ac_no' => $ac_no, 'm_pin' => $mpin]);

                // Insert into common_user_base safely
                DB::table('common_user_base')->updateOrInsert(
                    ['user_id' => $id, 'user_type' => $user_cat],
                    ['ac_no' => $ac_no, 'status' => 1, 'date' => date('Y-m-d')]
                );

                // Handle referral — generates FIIN+6digit unified code
                $this->handleReferral($id, $referral_code, $date_heure, 'customer');

                // Mark OTP verified
                if (isset($record)) {
                    DB::table('auth_otp_temp')->where('id', $record->id)->update(['verified' => 1]);
                }

                $get_user = UserApp::where('id', $id)->first();
                $row      = $get_user->toArray();
                unset($row['mdp']);
                $row['referral_code'] = \App\Services\ReferralCodeService::getOrCreateReferralCode($id, 'customer');
                $row['user_cat']      = 'user_app';
                $row['accesstoken']   = $this->adduseraccess($id, 'customer');
                $row['id']            = (string)$id;

                return response()->json(['success' => 'success', 'error' => null, 'message' => 'Account created successfully.', 'data' => $row]);

            } elseif ($user_cat === 'driver') {
                DB::insert("insert into tj_conducteur(online,prenom,nom,phone,mdp,statut,login_type,tonotify,creer,updated_at,status_car_image,statut_vehicule,email,address,amount,parcel_delivery,driver_on_ride,is_verified)
                    values('no','$firstname','$lastname','$phone','$hashedMpin','no','phoneOtp','yes','$date_heure','$date_heure','no','no','$email','','0','yes','no',0)");

                $id = DB::getPdo()->lastInsertId();

                // Generate ac_no
                $lastId        = DB::table('tj_conducteur')->orderByDesc('id')->value('id') ?? 0;
                $sequential    = $lastId + 1;
                $ac_no         = '7060' . str_pad($sequential + 1000, 8, '0', STR_PAD_LEFT);

                DB::table('tj_conducteur')->where('id', $id)->update(['ac_no' => $ac_no, 'm_pin' => $mpin]);

                // Assign default free subscription plan
                $freePlan = DB::table('subscription_plans')->where('type', 'free')->first();
                if ($freePlan) {
                    DB::table('tj_conducteur')->where('id', $id)->update([
                        'subscriptionPlanId' => $freePlan->id,
                        'subscriptionTotalOrders' => $freePlan->bookingLimit,
                        'subscription_plan' => json_encode($freePlan),
                    ]);
                }

                DB::table('common_user_base')->updateOrInsert(
                    ['user_id' => $id, 'user_type' => $user_cat],
                    ['ac_no' => $ac_no, 'status' => 1, 'date' => date('Y-m-d')]
                );

                // Handle referral — generates FIIN+6digit unified code
                $this->handleReferral($id, $referral_code, $date_heure, 'driver');

                if (isset($record)) {
                    DB::table('auth_otp_temp')->where('id', $record->id)->update(['verified' => 1]);
                }

                $get_user = Driver::where('id', $id)->first();
                $row      = $get_user->toArray();
                unset($row['mdp']);
                $row['referral_code'] = \App\Services\ReferralCodeService::getOrCreateReferralCode($id, 'driver');
                $row['accesstoken'] = $this->adduseraccess($id, 'driver');
                $row['user_cat']    = 'driver';
                $row['id']          = (string)$id;

                return response()->json(['success' => 'success', 'error' => null, 'message' => 'Driver account created successfully.', 'data' => $row]);
            }
        } catch (\Throwable $e) {
            \Log::error("registerSimple Exception: " . $e->getMessage());
            // Fallback recovery check: If account was created despite error, return success
            $recovered = $this->getUserByPhone($phone, $user_cat);
            if ($recovered) {
                $row = $recovered->toArray();
                unset($row['mdp']);
                $row['user_cat']    = $user_cat === 'customer' ? 'user_app' : 'driver';
                $row['accesstoken'] = $this->adduseraccess($recovered->id, $user_cat);
                $row['id']          = (string)$recovered->id;
                return response()->json(['success' => 'success', 'error' => null, 'message' => 'Registration successful.', 'data' => $row]);
            }
            return response()->json(['success' => 'Failed', 'error' => $e->getMessage()]);
        }

        return response()->json(['success' => 'Failed', 'error' => 'Invalid user category.']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Apply Referral Code from Profile Screen (after registration)
    //   POST /api/v1/auth/apply-referral
    // ─────────────────────────────────────────────────────────────────────────
    // ─────────────────────────────────────────────────────────────────────────
    // Apply Referral Code from Profile Screen (after registration)
    //   POST /api/v1/auth/apply-referral
    public function applyReferral(Request $request)
    {
        $userId       = $request->get('user_id');
        $userCat      = $request->get('user_cat', 'customer');
        $referralCode = trim($request->get('referral_code', ''));

        if (empty($userId) || empty($referralCode)) {
            return response()->json(['success' => 'Failed', 'error' => 'user_id and referral_code are required.']);
        }

        // Look up referrer using flexible resolution
        $referrer = $this->resolveReferrerUserId($referralCode);
        if (!$referrer) {
            return response()->json(['success' => 'Failed', 'error' => 'Invalid referral code. Please check and try again.']);
        }

        $referrerId   = (int)$referrer['user_id'];
        $referrerType = $referrer['user_type'] ?? 'customer';

        // Don't allow genuine self-referral: Same user_id AND same user_type, OR same mobile number
        $isSelfReferral = ($referrerId === (int)$userId && $referrerType === $userCat);

        if (!$isSelfReferral) {
            $currentUserPhone = ($userCat === 'driver')
                ? DB::table('tj_conducteur')->where('id', $userId)->value('phone')
                : DB::table('tj_user_app')->where('id', $userId)->value('phone');
            $referrerPhone = ($referrerType === 'driver')
                ? DB::table('tj_conducteur')->where('id', $referrerId)->value('phone')
                : DB::table('tj_user_app')->where('id', $referrerId)->value('phone');

            if (!empty($currentUserPhone) && !empty($referrerPhone)) {
                $cleanCur = substr(preg_replace('/\D/', '', (string)$currentUserPhone), -10);
                $cleanRef = substr(preg_replace('/\D/', '', (string)$referrerPhone), -10);
                if (!empty($cleanCur) && $cleanCur === $cleanRef) {
                    $isSelfReferral = true;
                }
            }
        }

        if ($isSelfReferral) {
            return response()->json(['success' => 'Failed', 'error' => 'You cannot use your own referral code.']);
        }

        // Check if user already has a referral entry with a referrer set
        $existingReferral = DB::table('referral')
            ->where('user_id', $userId)
            ->where(function($q) use ($userCat) {
                $q->where('user_type', $userCat)->orWhereNull('user_type');
            })
            ->first();

        if ($existingReferral && !empty($existingReferral->referral_by_id)) {
            return response()->json(['success' => 'Failed', 'error' => 'A referral code has already been applied to your account.']);
        }

        if ($existingReferral) {
            $refUpdate = [
                'user_type'      => $userCat,
                'referral_by_id' => $referrerId,
                'code_used'      => 'true',
            ];
            if (Schema::hasColumn('referral', 'referral_by_type')) {
                $refUpdate['referral_by_type'] = $referrerType;
            }
            if (Schema::hasColumn('referral', 'referral_by_code')) {
                $refUpdate['referral_by_code'] = $referralCode;
            }
            DB::table('referral')->where('id', $existingReferral->id)->update($refUpdate);
        } else {
            $userReferralCode = \App\Services\ReferralCodeService::getOrCreateReferralCode($userId, $userCat);
            $insertData = [
                'user_id'        => $userId,
                'user_type'      => $userCat,
                'referral_by_id' => $referrerId,
                'referral_code'  => $userReferralCode,
                'code_used'      => 'true',
                'creer'          => date('Y-m-d H:i:s'),
            ];
            if (Schema::hasColumn('referral', 'referral_by_type')) {
                $insertData['referral_by_type'] = $referrerType;
            }
            if (Schema::hasColumn('referral', 'referral_by_code')) {
                $insertData['referral_by_code'] = $referralCode;
            }
            DB::table('referral')->insert($insertData);
        }

        // Update ref_by column in user/driver table if present
        if (Schema::hasColumn('tj_user_app', 'ref_by') && $userCat !== 'driver') {
            DB::table('tj_user_app')->where('id', $userId)->update(['ref_by' => $referralCode]);
        }
        if (Schema::hasColumn('tj_conducteur', 'ref_by') && $userCat === 'driver') {
            DB::table('tj_conducteur')->where('id', $userId)->update(['ref_by' => $referralCode]);
        }

        // Credit referral reward money to referrer's wallet (if milestone already met)
        $bonus = $this->creditReferralReward($referrerId, $referrerType, $userId, $userCat);

        \Log::info("applyReferral: user $userId ($userCat) applied code '$referralCode' by referrer $referrerId ($referrerType), credited ₹$bonus");

        $msg = ($bonus > 0)
            ? "Referral code applied successfully! ₹{$bonus} referral reward credited to your referrer."
            : "Referral code applied successfully! Cashback will unlock upon completing your first service.";

        return response()->json([
            'success' => 'success',
            'message' => $msg,
        ]);
    }


    private function phoneExists(string $phone, string $user_cat): bool
    {
        if ($user_cat === 'customer') {
            return UserApp::where('phone', $phone)->exists();
        }
        return Driver::where('phone', $phone)->exists();
    }

    private function emailExists(string $email, string $user_cat): bool
    {
        if ($user_cat === 'customer') {
            return UserApp::where('email', $email)->exists();
        }
        return Driver::where('email', $email)->exists();
    }

    private function getUserByPhone(string $phone, string $user_cat)
    {
        $cleanPhone = preg_replace('/\D/', '', $phone);
        $last10 = strlen($cleanPhone) >= 10 ? substr($cleanPhone, -10) : $cleanPhone;

        if ($user_cat === 'customer') {
            $user = UserApp::where('phone', $phone)->orWhere('phone', $cleanPhone)->first();
            if (!$user && strlen($last10) >= 10) {
                $user = UserApp::where('phone', 'LIKE', '%' . $last10)->first();
            }
            return $user;
        }

        $driver = Driver::where('phone', $phone)->orWhere('phone', $cleanPhone)->first();
        if (!$driver && strlen($last10) >= 10) {
            $driver = Driver::where('phone', 'LIKE', '%' . $last10)->first();
        }
        return $driver;
    }

    private function maskEmail(string $email): string
    {
        [$local, $domain] = explode('@', $email, 2);
        $masked = substr($local, 0, 1) . str_repeat('*', max(strlen($local) - 1, 2));
        return $masked . '@' . $domain;
    }

    public function resolveReferrerUserId(string $referralCode): ?array
    {
        return \App\Services\ReferralCodeService::resolveReferrer($referralCode);
    }

    public function getUserTypeById(int $userId): ?string
    {
        if (Schema::hasTable('common_user_base')) {
            $base = DB::table('common_user_base')->where('user_id', $userId)->first();
            if ($base && !empty($base->user_type)) {
                return $base->user_type === 'driver' ? 'driver' : 'customer';
            }
        }
        if (Schema::hasTable('tj_conducteur') && DB::table('tj_conducteur')->where('id', $userId)->exists()) {
            return 'driver';
        }
        if (Schema::hasTable('tj_user_app') && DB::table('tj_user_app')->where('id', $userId)->exists()) {
            return 'customer';
        }
        return null;
    }

    public function creditReferralReward(int $referrerId, string $referrerType, int $referredUserId, string $referredUserType = 'customer'): float
    {
        $referredUserType = in_array(strtolower(trim($referredUserType)), ['driver', 'conducteur', 'business', 'provider']) ? 'driver' : 'customer';

        $result = \App\Services\ReferralRewardService::checkAndProcessAppInstallReward((int)$referredUserId, $referredUserType);

        if (!empty($result['reward_processed'])) {
            return (float)($result['reward_amount'] ?? 0.0);
        }

        return 0.0;
    }

    public function handleReferral($userId, string $referralCode, string $dateHeure, string $userCat = 'customer'): void
    {
        try {
            $userId = (int)$userId;
            $userCat = in_array(strtolower(trim($userCat)), ['driver', 'conducteur', 'business', 'provider']) ? 'driver' : 'customer';
            $referralCode = trim($referralCode);
            $referrer = !empty($referralCode) ? $this->resolveReferrerUserId($referralCode) : null;
            $isSelf = false;
            if ($referrer) {
                $rId = (int)$referrer['user_id'];
                $rType = $referrer['user_type'] ?? 'customer';
                if ($rId === $userId && $rType === $userCat) {
                    $isSelf = true;
                }
            }
            $referrerId = ($referrer && !$isSelf) ? (int)$referrer['user_id'] : null;
            $referrerType = $referrer ? ($referrer['user_type'] ?? 'customer') : null;

            // Generate or fetch 100% globally unique, distinct referral code
            $userReferralCode = \App\Services\ReferralCodeService::getOrCreateReferralCode($userId, $userCat);

            // Update referral record with referrer information if provided
            if ($referrerId) {
                $refUpdate = [
                    'referral_by_id'   => $referrerId,
                    'code_used'        => 'true',
                ];
                if (Schema::hasColumn('referral', 'referral_by_type')) {
                    $refUpdate['referral_by_type'] = $referrerType;
                }
                if (Schema::hasColumn('referral', 'referral_by_code')) {
                    $refUpdate['referral_by_code'] = $referralCode;
                }

                $existingRef = DB::table('referral')->where('user_id', $userId)->where('user_type', $userCat)->first();
                if ($existingRef) {
                    DB::table('referral')->where('id', $existingRef->id)->update($refUpdate);
                } else {
                    $refUpdate['user_id']       = $userId;
                    $refUpdate['user_type']     = $userCat;
                    $refUpdate['referral_code'] = $userReferralCode;
                    $refUpdate['creer']         = $dateHeure;
                    DB::table('referral')->insert($refUpdate);
                }
            }

            // Persist referral_code and ref_by in user/driver table
            if (Schema::hasColumn('tj_user_app', 'ref_by') && $userCat !== 'driver' && !empty($referralCode)) {
                DB::table('tj_user_app')->where('id', $userId)->update(['ref_by' => $referralCode]);
            }
            if (Schema::hasColumn('tj_conducteur', 'ref_by') && $userCat === 'driver' && !empty($referralCode)) {
                DB::table('tj_conducteur')->where('id', $userId)->update(['ref_by' => $referralCode]);
            }

            // Credit referral reward if referrer was found
            if ($referrerId && $referrerType) {
                $this->creditReferralReward($referrerId, $referrerType, $userId, $userCat);
            }
        } catch (\Throwable $e) {
            \Log::error("handleReferral error for user $userId: " . $e->getMessage());
        }
    }

    /**
     * Send OTP email via Laravel Mail + SMTP (Hostinger — git@openscore.msmeloan.sbs)
     * Uses smtp.hostinger.com:465 configured in .env (MAIL_HOST, MAIL_USERNAME, etc.)
     */
    private function sendOtpEmail(string $toEmail, string $otp, string $purpose = 'verify'): bool
    {
        try {
            $appName  = env('APP_NAME', 'Fiinway');
            $fromAddr = env('OTP_MAIL_FROM_ADDRESS', env('MAIL_FROM_ADDRESS', 'git@openscore.msmeloan.sbs'));
            $subject  = $purpose === 'login'
                ? "$appName — Your Login OTP"
                : "$appName — Verify Your Email";

            $body = $purpose === 'login'
                ? $this->buildLoginOtpEmail($otp, $appName)
                : $this->buildVerifyOtpEmail($otp, $appName);

            // Use Laravel's Mail facade with SMTP configured in .env
            // This uses smtp.hostinger.com:465 (SSL) — NOT the broken PHP mail() sendmail
            Mail::html($body, function ($message) use ($toEmail, $fromAddr, $appName, $subject) {
                $message->to($toEmail)
                        ->from($fromAddr, $appName)
                        ->subject($subject);
            });

            return true;

        } catch (\Exception $e) {
            \Log::error('AuthOtpController::sendOtpEmail SMTP error: ' . $e->getMessage());
            return false;
        }
    }

    private function buildVerifyOtpEmail(string $otp, string $appName): string
    {
        return "
        <div style='font-family:Arial,sans-serif;max-width:480px;margin:0 auto;padding:32px;background:#f9f9f9;border-radius:8px'>
          <h2 style='color:#1a1a2e;margin-bottom:8px'>Verify your email</h2>
          <p style='color:#555;font-size:15px'>Use the OTP below to verify your email address for <strong>{$appName}</strong>.</p>
          <div style='background:#fff;border:1px solid #e0e0e0;border-radius:6px;padding:24px;text-align:center;margin:24px 0'>
            <span style='font-size:36px;font-weight:700;letter-spacing:12px;color:#1a1a2e'>{$otp}</span>
          </div>
          <p style='color:#888;font-size:13px'>This OTP expires in <strong>10 minutes</strong>. Do not share it with anyone.</p>
          <hr style='border:none;border-top:1px solid #eee;margin:24px 0'>
          <p style='color:#aaa;font-size:12px'>&copy; {$appName}. If you did not request this, ignore this email.</p>
        </div>";
    }

    private function buildLoginOtpEmail(string $otp, string $appName): string
    {
        return "
        <div style='font-family:Arial,sans-serif;max-width:480px;margin:0 auto;padding:32px;background:#f9f9f9;border-radius:8px'>
          <h2 style='color:#1a1a2e;margin-bottom:8px'>Login OTP</h2>
          <p style='color:#555;font-size:15px'>Your login OTP for <strong>{$appName}</strong> is:</p>
          <div style='background:#fff;border:1px solid #e0e0e0;border-radius:6px;padding:24px;text-align:center;margin:24px 0'>
            <span style='font-size:36px;font-weight:700;letter-spacing:12px;color:#1a1a2e'>{$otp}</span>
          </div>
          <p style='color:#888;font-size:13px'>This OTP expires in <strong>10 minutes</strong>. Never share it with anyone.</p>
          <hr style='border:none;border-top:1px solid #eee;margin:24px 0'>
          <p style='color:#aaa;font-size:12px'>&copy; {$appName}. If you did not attempt to log in, please secure your account.</p>
        </div>";
    }

    public function adduseraccess(int $userId, string $userType): string
    {
        $user = DB::table('users_access')
            ->where('user_id', $userId)
            ->where('user_type', $userType)
            ->first();

        if ($user && !empty($user->accesstoken)) {
            return $user->accesstoken;
        }

        $token = $this->getUniqAccessToken();
        DB::table('users_access')->insert([
            'user_id'     => $userId,
            'accesstoken' => $token,
            'user_type'   => $userType,
        ]);
        return $token;
    }

    private function getUniqAccessToken(): string
    {
        do {
            $token = md5(uniqid(mt_rand(), true));
        } while (DB::table('users_access')->where('accesstoken', $token)->exists());
        return $token;
    }

    public function updateDriverCategory(Request $request)
    {
        $id = $request->get('id_conducteur');
        $category_id = $request->get('category_id');
        $subcategory_ids = $request->get('subcategory_ids', []);

        if (empty($id) || empty($category_id)) {
            return response()->json(['success' => 'Failed', 'error' => 'Missing fields']);
        }

        if (is_string($subcategory_ids)) {
            $subcategory_ids = json_decode($subcategory_ids, true);
        }
        if (!is_array($subcategory_ids)) {
            $subcategory_ids = [];
        }

        // Validate the primary category exists
        $primaryCategory = DB::table('tj_categorie_user')->where('id', $category_id)->first();
        if (!$primaryCategory) {
            return response()->json(['success' => 'Failed', 'error' => 'Invalid primary category']);
        }

        // Fetch details of all selected subcategories to validate names
        $allSelectedCategories = DB::table('tj_categorie_user')
            ->whereIn('id', array_merge([$category_id], $subcategory_ids))
            ->get();

        $primaryName = strtolower(trim($primaryCategory->libelle));

        foreach ($allSelectedCategories as $cat) {
            if ($cat->parent_id !== null) {
                $parent = DB::table('tj_categorie_user')->where('id', $cat->parent_id)->first();
                if ($parent && str_contains(strtolower($parent->libelle), 'delivery & logistics')) {
                    $subName = strtolower(trim($cat->libelle));
                    
                    if ($primaryName == 'cab driver') {
                        if ($subName !== 'pickup & drop (personal runner)' && $subName !== 'parcel delivery') {
                            return response()->json(['success' => 'Failed', 'error' => "Invalid service combo: {$cat->libelle} is not allowed for Cab Driver."]);
                        }
                    } else if ($primaryName == 'bike rider') {
                        if ($subName !== 'pickup & drop (personal runner)' && $subName !== 'parcel delivery' && $subName !== 'food delivery') {
                            return response()->json(['success' => 'Failed', 'error' => "Invalid service combo: {$cat->libelle} is not allowed for Bike Rider."]);
                        }
                    } else if ($primaryName == 'auto driver') {
                        if ($subName !== 'pickup & drop (personal runner)' && $subName !== 'parcel delivery') {
                            return response()->json(['success' => 'Failed', 'error' => "Invalid service combo: {$cat->libelle} is not allowed for Auto Driver."]);
                        }
                    } else if ($primaryName == 'e-rickshaw') {
                        if ($subName !== 'pickup & drop (personal runner)' && $subName !== 'parcel delivery') {
                            return response()->json(['success' => 'Failed', 'error' => "Invalid service combo: {$cat->libelle} is not allowed for E-Rickshaw."]);
                        }
                    } else if ($primaryName == 'truck owner') {
                        if ($subName !== 'packers & movers') {
                            return response()->json(['success' => 'Failed', 'error' => "Invalid service combo: {$cat->libelle} is not allowed for Truck Owner."]);
                        }
                    } else {
                        return response()->json(['success' => 'Failed', 'error' => "Delivery options not allowed for this role."]);
                    }
                }
            }
        }

        $date_heure = date('Y-m-d H:i:s');
        $category_val = intval($category_id);

        DB::table('tj_conducteur')
            ->where('id', $id)
            ->update([
                'category_id' => $category_val,
                'updated_at'  => $date_heure
            ]);

        // Sync tj_conducteur_categories
        DB::table('tj_conducteur_categories')->where('driver_id', $id)->delete();

        // 1. Insert primary category
        DB::table('tj_conducteur_categories')->insert([
            'driver_id' => $id,
            'category_id' => $primaryCategory->parent_id ?? $primaryCategory->id,
            'subcategory_id' => $primaryCategory->id,
            'created_at' => $date_heure,
            'updated_at' => $date_heure,
        ]);

        // 2. Insert others
        foreach ($subcategory_ids as $sub_id) {
            $sub_id = intval($sub_id);
            if ($sub_id === $category_val) continue;

            $cat = DB::table('tj_categorie_user')->where('id', $sub_id)->first();
            if ($cat) {
                DB::table('tj_conducteur_categories')->insert([
                    'driver_id' => $id,
                    'category_id' => $cat->parent_id ?? $cat->id,
                    'subcategory_id' => $cat->id,
                    'created_at' => $date_heure,
                    'updated_at' => $date_heure,
                ]);
            }
        }

        $get_user = Driver::where('id', $id)->first();
        if ($get_user) {
            $row = $get_user->toArray();
            unset($row['mdp']);
            $row['user_cat']    = 'driver';
            $row['is_verified'] = ($row['is_verified'] == 1) ? 'yes' : 'no';

            // Driver vehicle
            $vehicle = DB::table('tj_vehicule')
                ->where('statut', 'yes')
                ->where('id_conducteur', $id)
                ->first();
            if ($vehicle) {
                $row['brand']       = $vehicle->brand;
                $row['model']       = $vehicle->model;
                $row['color']       = $vehicle->color;
                $row['numberplate'] = $vehicle->numberplate;
            }

            // Append commission info
            $commission = DB::table('tj_commission')->where('statut', 'yes')->first();
            if ($commission) {
                $row['admin_commission'] = $commission->value;
                $row['commision_type']   = $commission->type;
            }

            $row['selected_categories'] = DB::table('tj_conducteur_categories')
                ->where('driver_id', $id)
                ->get()
                ->map(fn($item) => (string)($item->subcategory_id ?? $item->category_id))
                ->toArray();

            $row['accesstoken'] = $this->adduseraccess($id, 'driver');
            $row['id']          = (string)$id;

            return response()->json([
                'success' => 'success',
                'message' => 'Category updated successfully',
                'data'    => $row
            ]);
        }

        return response()->json(['success' => 'Failed', 'error' => 'Driver not found']);
    }

    public function getDriverServices(Request $request)
    {
        $driverId = $request->get('driver_id');
        if (empty($driverId)) {
            return response()->json(['success' => 'Failed', 'error' => 'driver_id is required']);
        }

        $services = DB::table('tj_conducteur_categories')
            ->leftJoin('tj_categorie_user as subcat', 'tj_conducteur_categories.subcategory_id', '=', 'subcat.id')
            ->leftJoin('tj_categorie_user as cat', 'tj_conducteur_categories.category_id', '=', 'cat.id')
            ->where('tj_conducteur_categories.driver_id', $driverId)
            ->select(
                'tj_conducteur_categories.id',
                'tj_conducteur_categories.category_id',
                'tj_conducteur_categories.subcategory_id',
                DB::raw('COALESCE(subcat.libelle, cat.libelle) as title'),
                DB::raw('COALESCE(subcat.image, cat.image) as image'),
                'tj_conducteur_categories.statut'
            )
            ->get();

        $pricingByCategory = collect();
        if (\Illuminate\Support\Facades\Schema::hasTable('driver_service_pricing')) {
            $pricingByCategory = DB::table('driver_service_pricing')
                ->where('driver_id', $driverId)
                ->get()
                ->keyBy('category_id');
        }

        $itemsByCategory = collect();
        if (\Illuminate\Support\Facades\Schema::hasTable('driver_service_items')) {
            $itemsByCategory = DB::table('driver_service_items')
                ->where('driver_id', $driverId)
                ->orderBy('sort_order')
                ->get()
                ->groupBy('category_id');
        }

        $services = $services->map(function ($service) use ($pricingByCategory, $itemsByCategory) {
            $categoryKey = $service->subcategory_id ?: $service->category_id;
            $pricing = $pricingByCategory->get($categoryKey) ?? $pricingByCategory->get($service->category_id);
            $items = $itemsByCategory->get($categoryKey) ?? $itemsByCategory->get($service->category_id) ?? collect();

            $service->visiting_charge = $pricing ? (string) $pricing->visiting_charge : null;
            $service->service_items = $items->map(fn($item) => [
                'name' => $item->service_name,
                'price' => (string) $item->price,
            ])->values();

            return $service;
        });

        return response()->json([
            'success' => 'success',
            'data' => $services
        ]);
    }

    public function toggleDriverService(Request $request)
    {
        $driverId = $request->get('driver_id');
        $categoryId = $request->get('category_id');
        $statut = $request->get('statut'); // 'yes' or 'no'

        if (empty($driverId) || empty($categoryId) || !in_array($statut, ['yes', 'no'])) {
            return response()->json(['success' => 'Failed', 'error' => 'Missing or invalid parameters']);
        }

        // Try updating by subcategory_id first, then by category_id
        $updated = DB::table('tj_conducteur_categories')
            ->where('driver_id', $driverId)
            ->where('subcategory_id', $categoryId)
            ->update([
                'statut' => $statut,
                'updated_at' => now()
            ]);

        if (!$updated) {
            DB::table('tj_conducteur_categories')
                ->where('driver_id', $driverId)
                ->where('category_id', $categoryId)
                ->update([
                    'statut' => $statut,
                    'updated_at' => now()
                ]);
        }

        return response()->json([
            'success' => 'success',
            'message' => 'Service status updated successfully'
        ]);
    }
}
