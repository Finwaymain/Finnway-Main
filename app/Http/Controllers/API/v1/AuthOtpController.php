<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\UserApp;
use App\Models\Referral;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
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
        // TODO: Replace with real SMS gateway (e.g. Fast2SMS, Msg91) before production.
        //       Endpoint: POST https://www.fast2sms.com/dev/bulkV2
        //       For now, dummy OTP 1234 is used for all phone verifications.
        $otp = '1234';
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
            'message' => 'OTP sent to your mobile number.',
            // TODO: Remove this line in production. Only for development.
            'dev_otp' => $otp,
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

        $record = DB::table('auth_otp_temp')
            ->where('phone', $phone)
            ->where('otp', $otp)
            ->where('type', 'phone')
            ->where('user_cat', $user_cat)
            ->where('verified', 0)
            ->where('expires_at', '>', date('Y-m-d H:i:s'))
            ->first();

        if (!$record) {
            return response()->json(['success' => 'Failed', 'error' => 'Invalid or expired OTP. Please try again.']);
        }

        // Mark as verified
        DB::table('auth_otp_temp')->where('id', $record->id)->update(['verified' => 1]);

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

            // Handle referral
            $this->handleReferral($id, $referral_code, $date_heure);

            // Mark OTP verified
            DB::table('auth_otp_temp')->where('id', $record->id)->update(['verified' => 1]);

            $get_user = UserApp::where('id', $id)->first();
            $row      = $get_user->toArray();
            unset($row['mdp']);
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
            $lastId     = DB::table('tj_conducteur')->orderByDesc('id')->value('id') ?? 0;
            $sequential = $lastId + 1;
            $ac_no      = '7060' . str_pad($sequential + 1000, 8, '0', STR_PAD_LEFT);

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

            DB::table('auth_otp_temp')->where('id', $record->id)->update(['verified' => 1]);

            $get_user = Driver::where('id', $id)->first();
            $row      = $get_user->toArray();
            unset($row['mdp']);
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
        if ($user->mdp !== $hashedMpin) {
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

        // Mark OTP as verified
        DB::table('auth_otp_temp')->where('id', $record->id)->update(['verified' => 1]);

        // Get user and update MPIN (mdp)
        $hashedMpin = md5($mpin);
        if ($user_cat === 'customer') {
            DB::table('tj_user_app')->where('phone', $phone)->update(['mdp' => $hashedMpin]);
        } else {
            DB::table('tj_conducteur')->where('phone', $phone)->update(['mdp' => $hashedMpin]);
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

        // Verify phone OTP
        $record = DB::table('auth_otp_temp')
            ->where('phone', $phone)
            ->where('otp', $otp)
            ->where('type', 'phone')
            ->where('user_cat', $user_cat)
            ->where('verified', 1)
            ->where('expires_at', '>', date('Y-m-d H:i:s'))
            ->first();

        if (!$record) {
            \Log::error("registerSimple: Invalid or expired OTP. Verified=1 not found for phone: $phone, otp: $otp");
            return response()->json(['success' => 'Failed', 'error' => 'Invalid or expired OTP. Please try again.']);
        }

        // Check if phone already registered (idempotency check)
        $existingUser = $this->getUserByPhone($phone, $user_cat);
        if ($existingUser) {
            $hashedMpin = md5($mpin);
            if ($user_cat === 'customer') {
                DB::table('tj_user_app')->where('id', $existingUser->id)->update(['mdp' => $hashedMpin]);
            } else {
                DB::table('tj_conducteur')->where('id', $existingUser->id)->update(['mdp' => $hashedMpin]);
            }
            $row = $existingUser->toArray();
            unset($row['mdp']);
            $row['user_cat']    = $user_cat === 'customer' ? 'user_app' : 'driver';
            $row['accesstoken'] = $this->adduseraccess($existingUser->id, $user_cat);
            $row['id']          = (string)$existingUser->id;
            return response()->json(['success' => 'success', 'error' => null, 'message' => 'Registration successful.', 'data' => $row]);
        }

        // Generate dummy email from phone
        $email = '';
        $hashedMpin = md5($mpin);

        $firstname = str_replace("'", "\'", $firstname);
        $lastname  = str_replace("'", "\'", $lastname);

        if ($user_cat === 'customer') {
            // Insert customer
            DB::insert("insert into tj_user_app(prenom,nom,phone,mdp,statut,login_type,tonotify,creer,statut_nic,email,age,gender)
                values('$firstname','$lastname','$phone','$hashedMpin','yes','phoneOtp','yes','$date_heure','no','$email','0','')");

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

            // Handle referral
            $this->handleReferral($id, $referral_code, $date_heure);

            // Mark OTP verified
            DB::table('auth_otp_temp')->where('id', $record->id)->update(['verified' => 1]);

            $get_user = UserApp::where('id', $id)->first();
            $row      = $get_user->toArray();
            unset($row['mdp']);
            $row['user_cat']    = 'user_app';
            $row['accesstoken'] = $this->adduseraccess($id, 'customer');
            $row['id']          = (string)$id;

            return response()->json(['success' => 'success', 'error' => null, 'message' => 'Account created successfully.', 'data' => $row]);

        } elseif ($user_cat === 'driver') {
            DB::insert("insert into tj_conducteur(online,prenom,nom,phone,mdp,statut,login_type,tonotify,creer,updated_at,status_car_image,statut_vehicule,email,address,amount,parcel_delivery,driver_on_ride,is_verified)
                values('no','$firstname','$lastname','$phone','$hashedMpin','no','phoneOtp','yes','$date_heure','$date_heure','no','no','$email','','0','yes','no',0)");

            $id = DB::getPdo()->lastInsertId();

            // Generate ac_no
            $lastId     = DB::table('tj_conducteur')->orderByDesc('id')->value('id') ?? 0;
            $sequential = $lastId + 1;
            $ac_no      = '7060' . str_pad($sequential + 1000, 8, '0', STR_PAD_LEFT);

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

            DB::table('auth_otp_temp')->where('id', $record->id)->update(['verified' => 1]);

            $get_user = Driver::where('id', $id)->first();
            $row      = $get_user->toArray();
            unset($row['mdp']);
            $row['accesstoken'] = $this->adduseraccess($id, 'driver');
            $row['user_cat']    = 'driver';
            $row['id']          = (string)$id;

            return response()->json(['success' => 'success', 'error' => null, 'message' => 'Driver account created successfully.', 'data' => $row]);
        }

        return response()->json(['success' => 'Failed', 'error' => 'Invalid user category.']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────────

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
        if ($user_cat === 'customer') {
            return UserApp::where('phone', $phone)->first();
        }
        return Driver::where('phone', $phone)->first();
    }

    private function maskEmail(string $email): string
    {
        [$local, $domain] = explode('@', $email, 2);
        $masked = substr($local, 0, 1) . str_repeat('*', max(strlen($local) - 1, 2));
        return $masked . '@' . $domain;
    }

    private function handleReferral(int $userId, string $referralCode, string $dateHeure): void
    {
        $referralBy = '';
        if (!empty($referralCode)) {
            $query = Referral::where('referral_code', $referralCode)->first();
            if ($query) {
                $referralBy = $query->user_id;
            }
        }

        $uniqid          = uniqid();
        $randStart       = rand(1, 5);
        $userReferralCode = substr($uniqid, $randStart, 5);

        Referral::insert([
            'user_id'        => $userId,
            'referral_by_id' => $referralBy ?: null,
            'referral_code'  => $userReferralCode,
            'code_used'      => 'false',
            'creer'          => $dateHeure,
        ]);
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
