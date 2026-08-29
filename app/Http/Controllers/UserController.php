<?php
namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\AccessToken;
use App\Models\Currency;
use App\Models\FavoriteRide;
use App\Models\ParcelOrder;
use App\Models\Referral;
use App\Models\Requests;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserApp;
use App\Models\VehicleLocation;
use Carbon\Carbon;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Image;
use Validator;

class UserController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    // this code is kyc switch status update
    public function switch_kyc_status_update(Request $request, $tableName)
    {

        $id                 = $request->id;
        $data               = [];
        $data['kyc_status'] = $request->kyc_status;
        if (! empty($id)) {

            $update = DB::table($tableName)->where('id', $id)->update($data);

            return response()->json([
                'data'    => $update,
                'success' => 'success',
            ]);
        }
    }

    ################# User Scheduler show here ############
    public function users_shudule_index(Request $request)
    {
        $sql = UserApp::whereNull('tj_user_app.deleted_at');

        if ($request->has('search') && trim($request->search) != '') {
            $search = trim($request->input('search'));
            $cleanDigits = preg_replace('/[^0-9]/', '', $search);

            if ($request->selected_search == 'phone') {
                $sql->where(function ($q) use ($search, $cleanDigits) {
                    $q->where('tj_user_app.phone', 'LIKE', '%' . $search . '%');
                    if (!empty($cleanDigits)) {
                        $q->orWhere('tj_user_app.phone', 'LIKE', '%' . $cleanDigits . '%');
                        if (strlen($cleanDigits) == 10) {
                            $q->orWhere('tj_user_app.phone', 'LIKE', '%91' . $cleanDigits . '%')
                              ->orWhere('tj_user_app.phone', 'LIKE', '%+91' . $cleanDigits . '%');
                        }
                    }
                });
            } else if ($request->selected_search == 'email') {
                $sql->where('tj_user_app.email', 'LIKE', '%' . $search . '%');
            } else if ($request->selected_search == 'prenom') {
                $sql->where(function ($q) use ($search) {
                    $q->where('tj_user_app.prenom', 'LIKE', '%' . $search . '%')
                      ->orWhere('tj_user_app.nom', 'LIKE', '%' . $search . '%')
                      ->orWhere(DB::raw('CONCAT(tj_user_app.prenom, " ", tj_user_app.nom)'), 'LIKE', '%' . $search . '%');
                });
            } else {
                $sql->where(function ($q) use ($search, $cleanDigits) {
                    $q->where('tj_user_app.prenom', 'LIKE', '%' . $search . '%')
                      ->orWhere('tj_user_app.nom', 'LIKE', '%' . $search . '%')
                      ->orWhere('tj_user_app.email', 'LIKE', '%' . $search . '%')
                      ->orWhere('tj_user_app.ac_no', 'LIKE', '%' . $search . '%')
                      ->orWhere('tj_user_app.phone', 'LIKE', '%' . $search . '%')
                      ->orWhere(DB::raw('CONCAT(tj_user_app.prenom, " ", tj_user_app.nom)'), 'LIKE', '%' . $search . '%');
                    if (!empty($cleanDigits)) {
                        $q->orWhere('tj_user_app.phone', 'LIKE', '%' . $cleanDigits . '%');
                    }
                });
            }
        }

        if ($request->filled('daterange')) {
            $dates = explode(' - ', $request->daterange);
            if (count($dates) == 2) {
                try {
                    $startDate = Carbon::createFromFormat('d-m-Y', trim($dates[0]))->startOfDay();
                    $endDate   = Carbon::createFromFormat('d-m-Y', trim($dates[1]))->endOfDay();
                    $sql->whereBetween('creer', [$startDate, $endDate]);
                } catch (\Exception $e) {}
            }
        }

        if ($request->has('status_selector') && $request->status_selector != '') {
            $status = $request->input('status_selector');
            $status == 'active' ? $sql->where('statut', 'yes') : $sql->where('statut', 'no');
        }

        $users = $sql->orderBy('tj_user_app.id', 'desc')->paginate(20);

        return view("settings.users.users_shudule_index")->with("users", $users);
    }

    // update wallet/earn_wallet (amount/earn_amount)
    public function update_user_wallet(Request $request)
    {
        $existingUser = null;
        if ($request->filled('user_id')) {
            $existingUser = DB::table('tj_user_app')->where('id', $request->user_id)->first();
        }
        if (!$existingUser && $request->filled('ac_no')) {
            $existingUser = DB::table('tj_user_app')->where('ac_no', $request->ac_no)->orWhere('id', $request->ac_no)->first();
        }

        if (!$existingUser) {
            return response()->json([
                'success' => 'error',
                'message' => 'User account not found.'
            ], 404);
        }

        $userAcNo = !empty($existingUser->ac_no) ? $existingUser->ac_no : (string) $existingUser->id;
        $now = Carbon::now('Asia/Kolkata');
        $earn_time = $now->format('H:i:s');
        $dateTimeStr = $now->format('Y-m-d H:i:s');
        $dateStr = $now->format('Y-m-d');

        // Initialize variables with existing values
        $amount      = (float) $existingUser->amount;
        $earn_amount = (float) $existingUser->earn_amount;

        if ($request->has('amount') && !is_null($request->amount) && trim($request->amount) !== '') {
            $diffAmount = (float) $request->amount;
            $amount += $diffAmount;

            $type = $diffAmount < 0 ? 'debit' : 'credit';
            $deductionType = $diffAmount < 0 ? 0 : 1;
            $desc = !empty($request->description) ? trim($request->description) : ($diffAmount < 0 ? 'Wallet Deduction by Admin' : 'Wallet Credit by Admin');

            DB::table('tj_transaction')->insert([
                'id_user_app'     => $existingUser->id,
                'user_type'       => 'customer',
                'withdraw_status' => 'approved',
                'payment_status'  => 'success',
                'payment_method'  => 'Admin Adjustment',
                'txn_id'          => 'ADM' . time() . rand(100, 999),
                'ac_no'           => $userAcNo,
                'description'     => $desc,
                'amount'          => (string) abs($diffAmount),
                'type'            => $type,
                'deduction_type'  => $deductionType,
                'creer'           => $dateTimeStr,
                'modifier'        => $dateTimeStr,
                'date'            => $dateStr,
            ]);
        }

        if ($request->has('earn_amount') && !is_null($request->earn_amount) && trim($request->earn_amount) !== '') {
            $diffEarn = (float) $request->earn_amount;
            $earn_amount += $diffEarn;

            $typeEarn = $diffEarn < 0 ? 'debit' : 'credit';
            $deductionTypeEarn = $diffEarn < 0 ? 0 : 1;
            $descEarn = !empty($request->description) ? trim($request->description) : ($diffEarn < 0 ? 'Earn Wallet Deduction by Admin' : 'Earn Wallet Credit by Admin');

            DB::table('tj_transaction')->insert([
                'id_user_app'     => $existingUser->id,
                'user_type'       => 'customer',
                'withdraw_status' => 'approved',
                'payment_status'  => 'success',
                'payment_method'  => 'Admin Adjustment',
                'txn_id'          => 'ADM_E' . time() . rand(100, 999),
                'ac_no'           => $userAcNo,
                'description'     => $descEarn,
                'amount'          => (string) abs($diffEarn),
                'type'            => $typeEarn,
                'deduction_type'  => $deductionTypeEarn,
                'creer'           => $dateTimeStr,
                'modifier'        => $dateTimeStr,
                'date'            => $dateStr,
            ]);

            DB::table('tbl_earning')->insert([
                'ac_no'       => $userAcNo,
                'description' => $descEarn,
                'earn_wallet' => (string) $diffEarn,
                'txn_id'      => time(),
                'date'        => $dateStr,
                'created_at'  => $typeEarn,
                'time'        => $earn_time,
            ]);
        }

        $data = [
            'amount'      => (string) $amount,
            'earn_amount' => (string) $earn_amount,
        ];
        DB::table('tj_user_app')->where('id', $existingUser->id)->update($data);

        return response()->json([
            'success' => 'success',
            'amount'  => $amount,
            'earn_amount' => $earn_amount,
        ]);
    }

    ### Schedule 1 for users Sender Receiver Button
    public function update_wallet_all(Request $request)
    {
        $ac_nos = explode(',', $request->ac_no);

        $data = [
            'start_date'    => $request->start_date ?? null,
            'end_date'      => $request->end_date ?? null,
            'per_sender'    => $request->per_sender ?? null,
            'per_receiver'  => $request->per_receiver ?? null,
            'sender_desc'   => $request->sender_desc ?? null,
            'receiver_desc' => $request->receiver_desc ?? null,
        ];

        foreach ($ac_nos as $ac_no) {
            $ac_no = trim($ac_no);
            if (empty($ac_no)) continue;
            DB::table('tj_user_app')->where('ac_no', $ac_no)->orWhere('id', $ac_no)->update($data);
        }

        return response()->json([
            'success' => 'success',
        ]);
    }

    ### Schedule 2 for users Sender Update Daily Increment
    public function update_wallet_all2(Request $request)
    {
        $ac_nos = explode(',', $request->ac_no);

        $data = [
            'start_date2'     => $request->start_date2 ?? null,
            'end_date2'       => $request->end_date2 ?? null,
            'percentage'      => $request->percentage ?? null,
            'description_2nd' => $request->description_2nd ?? null,
        ];

        foreach ($ac_nos as $ac_no) {
            $ac_no = trim($ac_no);
            if (empty($ac_no)) continue;
            DB::table('tj_user_app')->where('ac_no', $ac_no)->orWhere('id', $ac_no)->update($data);
        }

        return response()->json([
            'success' => 'success',
        ]);
    }

    ### Schedule 3 for users Sender Update Deduction
    public function update_wallet_all3(Request $request)
    {
        $ac_nos = explode(',', $request->ac_no);
        $deductAmount = !empty($request->amount_3rd) ? (float) $request->amount_3rd : 0;
        $now = Carbon::now('Asia/Kolkata');
        $dateTimeStr = $now->format('Y-m-d H:i:s');
        $dateStr = $now->format('Y-m-d');
        $desc = !empty($request->description_3rd) ? trim($request->description_3rd) : 'Scheduled Deduction by Admin';

        $data = [
            'start_date3'     => $request->start_date3 ?? null,
            'end_date3'       => $request->end_date3 ?? null,
            'per_3rd'         => $request->per_3rd ?? null,
            'amount_3rd'      => $request->amount_3rd ?? null,
            'description_3rd' => $request->description_3rd ?? null,
        ];

        foreach ($ac_nos as $ac_no) {
            $ac_no = trim($ac_no);
            if (empty($ac_no)) continue;

            $user = DB::table('tj_user_app')->where('ac_no', $ac_no)->orWhere('id', $ac_no)->first();
            if (!$user) continue;

            $userAcNo = !empty($user->ac_no) ? $user->ac_no : (string) $user->id;
            $userUpdate = $data;

            if ($deductAmount > 0) {
                $newWallet = max(0, (float)$user->amount - $deductAmount);
                $userUpdate['amount'] = (string) $newWallet;

                DB::table('tj_transaction')->insert([
                    'id_user_app'     => $user->id,
                    'user_type'       => 'customer',
                    'withdraw_status' => 'approved',
                    'payment_status'  => 'success',
                    'payment_method'  => 'Admin Deduction',
                    'txn_id'          => 'DED' . time() . rand(100, 999),
                    'ac_no'           => $userAcNo,
                    'description'     => $desc,
                    'amount'          => (string) $deductAmount,
                    'type'            => 'debit',
                    'deduction_type'  => 0,
                    'creer'           => $dateTimeStr,
                    'modifier'        => $dateTimeStr,
                    'date'            => $dateStr,
                ]);
            }

            DB::table('tj_user_app')->where('id', $user->id)->update($userUpdate);
        }

        return response()->json([
            'success' => 'success'
        ]);
    }

    // schedule 4 refer and earn 
    public function update_refer_earn(Request $request)
    {
        $ac_nos = explode(',', $request->ac_no);
        $amount = !empty($request->amount_4th) ? (float) $request->amount_4th : 0;
        $now = Carbon::now('Asia/Kolkata');
        $dateTimeStr = $now->format('Y-m-d H:i:s');
        $dateStr = $now->format('Y-m-d');
        $desc = !empty($request->description_4th) ? trim($request->description_4th) : 'Refer & Earn Bonus Reward';

        $data = [
            'start_date4'     => $request->start_date4 ?? null,
            'end_date4'       => $request->end_date4 ?? null,
            'amount_4th'      => $request->amount_4th ?? null,
            'description_4th' => $request->description_4th ?? null,
        ];

        foreach ($ac_nos as $ac_no) {
            $ac_no = trim($ac_no);
            if (empty($ac_no)) continue;

            $user = DB::table('tj_user_app')->where('ac_no', $ac_no)->orWhere('id', $ac_no)->first();
            if (!$user) continue;

            $userAcNo = !empty($user->ac_no) ? $user->ac_no : (string) $user->id;
            $userUpdate = $data;

            if ($amount > 0) {
                $newWallet = (float)$user->amount + $amount;
                $userUpdate['amount'] = (string) $newWallet;

                // Record transaction in tj_transaction
                DB::table('tj_transaction')->insert([
                    'id_user_app'     => $user->id,
                    'user_type'       => 'customer',
                    'withdraw_status' => 'approved',
                    'payment_status'  => 'success',
                    'payment_method'  => 'Refer & Earn Reward',
                    'txn_id'          => 'REF' . time() . rand(100, 999),
                    'ac_no'           => $userAcNo,
                    'description'     => $desc,
                    'amount'          => (string) $amount,
                    'type'            => 'credit',
                    'deduction_type'  => 1,
                    'creer'           => $dateTimeStr,
                    'modifier'        => $dateTimeStr,
                    'date'            => $dateStr,
                ]);
            }

            DB::table('tj_user_app')->where('id', $user->id)->update($userUpdate);
        }

        return response()->json([
            'success' => 'success'
        ]);
    }


    // all user wallet and earn wallet update here
    public function update_transfer_wallet_all(Request $request)
    {
        $ac_nos    = explode(',', $request->ac_no);
        $ac_nos    = array_reverse($ac_nos);
        $now       = Carbon::now('Asia/Kolkata');
        $earn_time = $now->format('H:i:s');
        $dateTimeStr = $now->format('Y-m-d H:i:s');
        $dateStr = $now->format('Y-m-d');

        foreach ($ac_nos as $ac_no) {
            $ac_no = trim($ac_no);
            if (empty($ac_no)) continue;

            $existingUser = DB::table('tj_user_app')->where('ac_no', $ac_no)->orWhere('id', $ac_no)->first();
            if (!$existingUser) continue;

            $amount      = (float) $existingUser->amount;
            $earn_amount = (float) $existingUser->earn_amount;

            if ($request->has('amount') && ! is_null($request->amount) && trim($request->amount) !== '') {
                $diffAmount = (float) $request->amount;
                $amount += $diffAmount;
                $type1 = $diffAmount < 0 ? 'debit' : 'credit';
                $deductionType1 = $diffAmount < 0 ? 0 : 1;
                $desc = !empty($request->description) ? trim($request->description) : ($diffAmount < 0 ? 'Bulk Wallet Deduction by Admin' : 'Bulk Wallet Credit by Admin');

                DB::table('tj_transaction')->insert([
                    'id_user_app'     => $existingUser->id,
                    'user_type'       => 'customer',
                    'withdraw_status' => 'approved',
                    'payment_status'  => 'success',
                    'payment_method'  => 'Admin Adjustment',
                    'txn_id'          => 'ADM_B' . time() . rand(100, 999),
                    'ac_no'           => $existingUser->ac_no ?? $ac_no,
                    'description'     => $desc,
                    'amount'          => (string) abs($diffAmount),
                    'type'            => $type1,
                    'deduction_type'  => $deductionType1,
                    'creer'           => $dateTimeStr,
                    'modifier'        => $dateTimeStr,
                    'date'            => $dateStr,
                ]);
            }

            if ($request->has('earn_amount') && ! is_null($request->earn_amount) && trim($request->earn_amount) !== '') {
                $diffEarn = (float) $request->earn_amount;
                $earn_amount += $diffEarn;
                $typeEarn = $diffEarn < 0 ? 'debit' : 'credit';
                $deductionTypeEarn = $diffEarn < 0 ? 0 : 1;
                $descEarn = !empty($request->description) ? trim($request->description) : ($diffEarn < 0 ? 'Bulk Earn Deduction by Admin' : 'Bulk Earn Credit by Admin');

                DB::table('tj_transaction')->insert([
                    'id_user_app'     => $existingUser->id,
                    'user_type'       => 'customer',
                    'withdraw_status' => 'approved',
                    'payment_status'  => 'success',
                    'payment_method'  => 'Admin Adjustment',
                    'txn_id'          => 'ADM_BE' . time() . rand(100, 999),
                    'ac_no'           => $existingUser->ac_no ?? $ac_no,
                    'description'     => $descEarn,
                    'amount'          => (string) abs($diffEarn),
                    'type'            => $typeEarn,
                    'deduction_type'  => $deductionTypeEarn,
                    'creer'           => $dateTimeStr,
                    'modifier'        => $dateTimeStr,
                    'date'            => $dateStr,
                ]);

                DB::table('tbl_earning')->insert([
                    'ac_no'       => $existingUser->ac_no ?? $ac_no,
                    'description' => $descEarn,
                    'earn_wallet' => (string) $diffEarn,
                    'txn_id'      => time(),
                    'date'        => $dateStr,
                    'created_at'  => $typeEarn,
                    'time'        => $earn_time,
                ]);
            }

            $data = [
                'amount'      => (string) $amount,
                'earn_amount' => (string) $earn_amount,
            ];
            DB::table('tj_user_app')->where('id', $existingUser->id)->update($data);
        }

        return response()->json([
            'success' => 'success',
        ]);
    }

    // public function update_user_wallet(Request $request)
    // {
    //     $existingUser = DB::table('tj_user_app')->where('ac_no', $request->ac_no)->first();
    //     $earn_time    = Carbon::now('Asia/Kolkata')->format('h:i:s');
    //     // Initialize variables with existing values
    //     $amount      = $existingUser->amount;
    //     $earn_amount = $existingUser->earn_amount;

    //     if ($request->has('amount') && ! is_null($request->amount)) {
    //         $amount = $existingUser->amount + $request->amount;
    //     }

    //     if ($request->has('earn_amount') && ! is_null($request->earn_amount)) {
    //         $earn_amount = $earn_amount + $request->earn_amount;
    //     }

    //     // Insert into transaction_history if amount is present and not null
    //     if ($request->has('amount') && ! is_null($request->amount)) {
    //         $Type1 = $request->amount < 0 ? 'debit' : 'credit';
    //         DB::table('transaction_history')->insert([
    //             'withdraw_status' => '',
    //             'txn_id'          => time(),
    //             'ac_no'           => $request->ac_no,
    //             'description'     => $request->description,
    //             'amount'          => $request->amount,
    //             'type'            => $Type1,
    //             'date'            => date('Y-m-d'),
    //         ]);
    //     }

    //     // Insert into tbl_earning if earn_wallet is present and not null
    //     if ($request->has('earn_amount') && ! is_null($request->earn_amount)) {
    //         $Type = $request->wallet < 0 ? 'debit' : 'credit';
    //         DB::table('tbl_earning')->insert([
    //             'ac_no'       => $request->ac_no,
    //             'description' => $request->description,
    //             'earn_wallet' => $request->earn_amount,
    //             'txn_id'      => time(),
    //             'date'        => date('Y-m-d'),
    //             'created_at'  => $Type,
    //             'time'        => $earn_time,
    //         ]);
    //     }

    //     $data = [
    //         'amount'      => $amount,
    //         'earn_amount' => $earn_amount,
    //     ];
    //     // Update customer data in the database
    //     $update    = DB::table('tj_user_app')->where('ac_no', $request->ac_no)->update($data);
    //     $user_data = DB::table('tj_user_app')->orderBy('id', 'desc')->get();
    //     return response()->json([
    //         'data'    => $user_data,
    //         'success' => 'success',
    //     ]);
    // }

    public function index(Request $request)
    {
        $sql = UserApp::where('tj_user_app.deleted_at', '=', null);

        if ($request->has('search') && $request->search != '' && $request->selected_search == 'prenom') {
            $search = $request->input('search');
            $sql->where('tj_user_app.prenom', 'LIKE', '%' . $search . '%')
                ->orWhere(DB::raw('CONCAT(tj_user_app.prenom, " ",tj_user_app.nom)'), 'LIKE', '%' . $search . '%');
        } else if ($request->has('search') && $request->search != '' && $request->selected_search == 'phone') {
            $search = $request->input('search');
            $sql->where('tj_user_app.phone', 'LIKE', '%' . $search . '%');
        } else if ($request->has('search') && $request->search != '' && $request->selected_search == 'email') {
            $search = $request->input('search');
            $sql->where('tj_user_app.email', 'LIKE', '%' . $search . '%');
        }
        if ($request->filled('daterange')) {
            $dates     = explode(' - ', $request->daterange);
            $startDate = Carbon::createFromFormat('d-m-Y', trim($dates[0]))->startOfDay();
            $endDate   = Carbon::createFromFormat('d-m-Y', trim($dates[1]))->endOfDay();

            $sql->whereBetween('creer', [$startDate, $endDate]);
        }
        if ($request->has('status_selector') && $request->status_selector != '') {
            $status = $request->input('status_selector');
            $status == 'active' ? $sql->where('statut', 'yes') : $sql->where('statut', 'no');
        }
        $users = $sql->orderBy('id', 'desc')->paginate(20);
        $users->map(function ($user) {
            if (! empty($user->email)) {
                $user->email = Helper::shortEmail($user->email);
            }
            if (! empty($user->phone)) {
                $user->phone = Helper::shortNumber($user->phone);
            }
            $planName = 'Standard';
            if (!empty($user->consumer_plan)) {
                $raw = trim((string)$user->consumer_plan);
                if (str_starts_with($raw, '{') || str_starts_with($raw, '[')) {
                    $decoded = json_decode($raw, true);
                    if (is_array($decoded)) {
                        $planName = $decoded['name'] ?? $decoded['title'] ?? $decoded['plan_name'] ?? $decoded['plan'] ?? 'Standard';
                    }
                } else {
                    $planName = $raw;
                }
            }
            $user->consumer_plan_display = $planName;
            return $user;
        });

        return view("settings.users.index")->with("users", $users);
    }

    public function create()
    {
        return view("settings.users.create");
    }

    public function storeuser(Request $request)
    {

        $validator = Validator::make($request->all(), $rules = [
            'nom'              => 'required',
            'prenom'           => 'required',
            'password'         => 'required',
            'confirm_password' => 'required|same:password',
            'phone'            => 'required|unique:tj_user_app',
            'email'            => 'required|unique:tj_user_app',
            'photo'            => 'required|mimes:jpg,jpeg,png|max:2048',
        ], $messages = [
            'nom.required'          => 'The First Name field is required!',
            'prenom.required'       => 'The Last Name field is required!',
            'email.required'        => 'The Email field is required!',
            'email.unique'          => 'The Email is already taken!',
            'password.required'     => 'The Password field is required!',
            'confirm_password.same' => 'Confirm Password should match the Password',
            'phone.required'        => 'The Phone is required!',
            'phone.unique'          => 'The Phone field is should be unique!',
        ]);

        if ($validator->fails()) {
            return redirect('users/create')
                ->withErrors($validator)->with(['message' => $messages])
                ->withInput();
        }
        $user         = new UserApp;
        $user->nom    = $request->input('nom');
        $user->prenom = $request->input('prenom');
        $user->email  = $request->input('email');

        $password         = $request->input('password');
        $confirm_password = $request->input('confirm_password');
        $user->mdp        = hash('md5', $password);

        $user->login_type = 'phone';
        $user->phone      = $request->input('phone');

        $user->statut = $request->has('statut') ? 'yes' : 'no';

        $user->photo     = '';
        $user->photo_nic = '';

        $user->creer      = date('Y-m-d H:i:s');
        $user->modifier   = date('Y-m-d H:i:s');
        $user->updated_at = date('Y-m-d H:i:s');

        if ($request->hasfile('photo')) {
            $file       = $request->file('photo');
            $extenstion = $file->getClientOriginalExtension();
            $time       = time() . '.' . $extenstion;
            $filename   = 'user_image' . $time;
            $path       = public_path('assets/images/users/') . $filename;
            if (! file_exists(public_path('assets/images/users/'))) {
                mkdir(public_path('assets/images/users/'), 0777, true);
            }
            Image::make($file->getRealPath())->resize(100, 100)->save($path);

            $image            = str_replace('data:image/png;base64,', '', $file);
            $image            = str_replace(' ', '+', $image);
            $user->photo_path = $filename;
        }
        $user->save();

        $referral = new Referral;

        $referral->user_id       = $user->id;
        $referral->referral_code = Str::random(5);
        $referral->code_used     = "false";
        $referral->creer         = date('Y-m-d H:i:s');

        $referral->save();

        return redirect('users');

    }

    public function appUsers()
    {
        return view("settings.users.index");
    }

    public function edit($id)
    {
        $user = UserApp::where('id', "=", $id)->first();

        $rides = DB::select("SELECT count(id) as rides FROM tj_requete WHERE statut='completed' AND id_user_app=$id");

        if (! empty($user['email'])) {
            $user['email'] = Helper::shortEmail($user['email']);
        }
        if (! empty($user['phone'])) {
            $user['phone'] = Helper::shortNumber($user['phone']);
        }

        return view("settings.users.edit")->with("user", $user)->with("rides", $rides);
    }

    public function show($id)
    {

        $user = UserApp::where('id', "=", $id)->first();

        if (! empty($user['email'])) {
            $user['email'] = Helper::shortEmail($user['email']);
        }
        if (! empty($user['phone'])) {
            $user['phone'] = Helper::shortNumber($user['phone']);
        }

        $currency = Currency::where('statut', 'yes')->first();
        if (!$currency) {
            $currency = (object)['symbole' => '', 'symbol_at_right' => 'false', 'decimal_digit' => 2];
        } else if ($currency->symbole === null) {
            $currency->symbole = '';
        }

        $transactions = Transaction::join('tj_payment_method', 'tj_transaction.payment_method', '=', 'tj_payment_method.libelle')
            ->select('tj_transaction.*', 'tj_payment_method.image')
            ->where('id_user_app', "=", $id)->orderBy('tj_transaction.id', 'desc')->paginate(10);

        $rides = Requests::
            join('tj_user_app', 'tj_requete.id_user_app', '=', 'tj_user_app.id')
            ->join('tj_conducteur', 'tj_requete.id_conducteur', '=', 'tj_conducteur.id')
            ->join('tj_payment_method', 'tj_requete.id_payment_method', '=', 'tj_payment_method.id')
            ->select('tj_requete.id', 'tj_requete.statut', 'tj_requete.statut_paiement', 'tj_requete.depart_name', 'tj_requete.destination_name', 'tj_requete.distance', 'tj_requete.montant', 'tj_requete.creer', 'tj_conducteur.id as driver_id', 'tj_conducteur.prenom as driverPrenom', 'tj_conducteur.nom as driverNom', 'tj_user_app.id as user_id', 'tj_user_app.prenom as userPrenom', 'tj_user_app.nom as userNom', 'tj_payment_method.libelle', 'tj_payment_method.image')
            ->where('tj_requete.id_user_app', $id)
            ->orderBy('tj_requete.id', 'DESC')
            ->paginate(10);

        $parcelOrders = ParcelOrder::
            join('tj_user_app', 'parcel_orders.id_user_app', '=', 'tj_user_app.id')
            ->join('tj_conducteur', 'parcel_orders.id_conducteur', '=', 'tj_conducteur.id')
            ->join('tj_payment_method', 'parcel_orders.id_payment_method', '=', 'tj_payment_method.id')
            ->select('parcel_orders.id', 'parcel_orders.status', 'parcel_orders.created_at', 'tj_conducteur.id as driver_id', 'tj_conducteur.prenom as driverPrenom', 'tj_conducteur.nom as driverNom')
            ->where('parcel_orders.id_user_app', $id)
            ->orderBy('parcel_orders.id', 'DESC')
            ->paginate(10);

        $user_rating = DB::table('tj_user_note')
            ->select(DB::raw("COUNT(id) as ratingCount"), DB::raw("SUM(niveau_driver) as ratingSum"))
            ->where('id_user_app', '=', $id)
            ->first();

        $userRating = "0.0";
        if (! empty($user_rating)) {
            if ($user_rating->ratingCount > 0) {
                $userRating = number_format(($user_rating->ratingSum / $user_rating->ratingCount));
            }
        }

        return view("settings.users.show")->with("user", $user)->with("rides", $rides)->with("transactions", $transactions)->with("currency", $currency)->with('userRating', $userRating)->with('parcelOrders', $parcelOrders);
    }

    public function userUpdate(Request $request, $id)
    {
        if ($request->id > 0) {
            $image_validation = "mimes:jpeg,jpg,png";
            $doc_validation   = "mimes:doc,pdf,docx,zip,txt";

        } else {
            $image_validation = "required|mimes:jpeg,jpg,png";
            $doc_validation   = "required|mimes:doc,pdf,docx,zip,txt";

        }
        $validator = Validator::make($request->all(), $rules = [
            'nom'    => 'required',
            'prenom' => 'required',
            'phone'  => 'required|unique:tj_user_app,phone,' . $id,
            'email'  => 'required|unique:tj_user_app,email,' . $id,
            'photo'  => 'required|mimes:jpg,jpeg,png|max:2048',

        ], $messages = [
            'nom.required'    => 'The First Name field is required!',
            'prenom.required' => 'The Last Name field is required!',
            'email.required'  => 'The Email field is required!',
            'email.unique'    => 'The Email is already taken!',
            'phone.required'  => 'The Phone is required!',
            'phone.unique'    => 'The Phone field is should be unique!',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)->with(['message' => $messages])
                ->withInput();
        }

        $nom       = $request->input('nom');
        $prenom    = $request->input('prenom');
        $phone     = $request->input('phone');
        $device_id = $request->input('device_id');

        // $gender = $request->input('gender');
        if ($request->input('statut')) {
            $status = "yes";
        } else {
            $status = "no";
        }
        $email = $request->input('email');

        $user = UserApp::find($id);
        if ($user) {
            $user->nom       = $nom;
            $user->prenom    = $prenom;
            $user->phone     = $phone;
            $user->device_id = $device_id;
            $user->statut    = $request->has('statut') ? 'yes' : 'no';
            $user->email     = $email;
            if ($request->hasfile('photo')) {

                $destination = public_path('assets/images/users/' . $user->photo_path);
                if (File::exists($destination)) {
                    File::delete($destination);
                }
                $file       = $request->file('photo');
                $extenstion = $file->getClientOriginalExtension();
                $time       = time() . '.' . $extenstion;
                $filename   = 'user_' . $id . '.' . $extenstion;
                $path       = public_path('assets/images/users/') . $filename;
                if (! file_exists(public_path('assets/images/users/'))) {
                    mkdir(public_path('assets/images/users/'), 0777, true);
                }
                Image::make($file->getRealPath())->resize(100, 100)->save($path);

                $user->photo_path = $filename;
            }
            $user->save();
        }

        return redirect('users');
    }

    public function deleteUser($id)
    {

        if ($id != "") {

            $id = json_decode($id);

            if (is_array($id)) {

                for ($i = 0; $i < count($id); $i++) {
                    $rides = Requests::where('id_user_app', $id[$i]);
                    if ($rides) {
                        $rides->delete();
                    }
                    $parcels = ParcelOrder::where('id_user_app', $id[$i]);
                    if ($parcels) {
                        $parcels->delete();
                    }

                    $favRides = FavoriteRide::where('id_user_app', $id[$i]);
                    if ($favRides) {
                        $favRides->delete();
                    }
                    $vehicle_location = VehicleLocation::where('id_user_app', $id[$i]);
                    if ($vehicle_location) {
                        $vehicle_location->delete();
                    }

                    $Transaction = Transaction::where('id_user_app', $id[$i]);
                    if ($Transaction) {
                        $Transaction->delete();
                    }

                    $Referral = Referral::where('user_id', $id[$i]);
                    if ($Referral) {
                        $Referral->delete();
                    }

                    $user        = UserApp::find($id[$i]);
                    $destination = public_path('assets/images/users/' . $user->photo_path);
                    if (File::exists($destination)) {
                        File::delete($destination);
                    }

                    $AccessToken = AccessToken::where('user_id', $id[$i]);
                    if ($AccessToken) {
                        $AccessToken->delete();
                    }

                    $user->delete();
                }

            } else {

                $rides = Requests::where('id_user_app', $id);
                if ($rides) {
                    $rides->delete();
                }
                $parcels = ParcelOrder::where('id_user_app', $id);
                if ($parcels) {
                    $parcels->delete();
                }

                $favRides = FavoriteRide::where('id_user_app', $id);
                if ($favRides) {
                    $favRides->delete();
                }
                $vehicle_location = VehicleLocation::where('id_user_app', $id);
                if ($vehicle_location) {
                    $vehicle_location->delete();
                }

                $Transaction = Transaction::where('id_user_app', $id);
                if ($Transaction) {
                    $Transaction->delete();
                }

                $Referral = Referral::where('user_id', $id);
                if ($Referral) {
                    $Referral->delete();
                }

                $user        = UserApp::find($id);
                $destination = public_path('assets/images/users/' . $user->photo_path);
                if (File::exists($destination)) {
                    File::delete($destination);
                }

                $AccessToken = AccessToken::where('user_id', $id);
                if ($AccessToken) {
                    $AccessToken->delete();
                }

                $user->delete();
            }

        }

        return redirect()->back();
    }

    public function addWallet(Request $request, $id)
    {
        $user   = UserApp::find($id);
        $amount = $request->amount;
        if ($amount == '' || $amount == null) {
            $amount = 0;
        }
        if ($user) {
            $userWallet   = floatval($user->amount) + floatval($amount);
            $user->amount = (string) $userWallet;
            $user->save();
        }
        $date = date('Y-m-d H:i:s');

        DB::table('tj_transaction')->insert([
            'amount'         => $amount,
            'payment_method' => 'Wallet',
            'id_user_app'    => $id,
            'deduction_type' => '1',
            'payment_status' => 'success',
            'creer'          => $date,
        ]);
        $user  = UserApp::find($id);
        $txnId = uniqid(0, 999);
        $email = $user->email;
        $date  = date('d F Y');

        if (! empty($email)) {

            $emailsubject  = '';
            $emailmessage  = '';
            $emailtemplate = DB::table('email_template')->select('*')->where('type', 'wallet_topup')->first();
            if (! empty($emailtemplate)) {
                $emailsubject  = $emailtemplate->subject;
                $emailmessage  = $emailtemplate->message;
                $send_to_admin = $emailtemplate->send_to_admin;
            }
            $currencyData = DB::table('tj_currency')->select('*')->where('statut', 'yes')->first();
            if ($currencyData->symbol_at_right == "true") {
                $amount     = number_format($amount, $currencyData->decimal_digit) . $currencyData->symbole;
                $newBalance = number_format($user['amount'], $currencyData->decimal_digit) . $currencyData->symbole;
            } else {
                $amount     = $currencyData->symbole . number_format($amount, $currencyData->decimal_digit);
                $newBalance = $currencyData->symbole . number_format($user['amount'], $currencyData->decimal_digit);

            }
            $contact_us_email = DB::table('tj_settings')->select('contact_us_email')->value('contact_us_email');
            $contact_us_email = $contact_us_email ? $contact_us_email : 'none@none.com';

            $app_name = env('APP_NAME', 'Cabme');
            if ($send_to_admin == "true") {
                $to = $email . "," . $contact_us_email;

            } else {
                $to = $email;
            }

            $emailmessage = str_replace("{AppName}", $app_name, $emailmessage);
            $emailmessage = str_replace("{UserName}", $user['nom'] . " " . $user['prenom'], $emailmessage);
            $emailmessage = str_replace("{Amount}", $amount, $emailmessage);
            $emailmessage = str_replace("{PaymentMethod}", 'Wallet', $emailmessage);
            $emailmessage = str_replace('{TransactionId}', $txnId, $emailmessage);
            $emailmessage = str_replace('{Balance}', $newBalance, $emailmessage);
            $emailmessage = str_replace('{Date}', $date, $emailmessage);

            // Always set content-type when sending HTML email
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= 'From: ' . $app_name . '<' . $contact_us_email . '>' . "\r\n";
            mail($to, $emailsubject, $emailmessage, $headers);
        }

        return redirect('users/show/' . $id);
    }

    public function profile()
    {
        $user = Auth::user();
        return view('settings.users.profile', compact(['user']));
    }

    public function changeStatus($id)
    {
        $user = UserApp::find($id);
        if ($user->statut == 'no') {
            $user->statut = 'yes';
        } else {
            $user->statut = 'no';
        }
        $user->save();
        return redirect()->back();

    }

    public function update(Request $request, $id)
    {
        $name         = $request->input('name');
        $password     = $request->input('password');
        $old_password = $request->input('old_password');
        $email        = $request->input('email');
        if ($password == '') {
            $validator = Validator::make($request->all(), [
                'name'  => 'required|max:255',
                'email' => 'required|email',
            ]);
        } else {
            $user = Auth::user();
            if (password_verify($old_password, $user->password)) {
                $validator = Validator::make($request->all(), [
                    'name'             => 'required|max:255',
                    'password'         => 'required|min:8',
                    'confirm_password' => 'required|same:password',
                    'email'            => 'required|email',
                ]);

            } else {
                return Redirect()->back()->with(['message' => "Please enter correct old password"]);
            }

        }

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return Redirect()->back()->with(['message' => $error]);
        }

        $user = User::find($id);
        if ($user) {
            $user->name  = $name;
            $user->email = $email;
            if ($password != '') {
                $user->password = Hash::make($password);
            }
            $user->save();
        }

        return redirect()->back();
    }

    public function toggalSwitch(Request $request)
    {
        $ischeck = $request->input('ischeck');
        $id      = $request->input('id');
        $user    = UserApp::find($id);

        if ($ischeck == "true") {
            $user->statut = 'yes';
        } else {
            $user->statut = 'no';
        }
        $user->save();

    }

    public function kycVerificationIndex(Request $request)
    {
        $search = $request->input('search');
        $roleFilter = $request->input('role_filter');
        $statusFilter = $request->input('kyc_status', 'pending'); // default to 'pending'
        $daterange = $request->input('daterange');

        $query = DB::table('tj_conducteur')->whereNull('deleted_at');

        if ($statusFilter === 'pending') {
            $query->where(function($q) {
                $q->whereNull('kyc_status')->orWhere('kyc_status', '0')->orWhere('kyc_status', '')->orWhere('kyc_status', '!=', '1');
            });
        } elseif ($statusFilter === 'approved') {
            $query->where('kyc_status', '1');
        } elseif ($statusFilter === 'rejected') {
            $query->where('kyc_status', '2');
        } // if 'all', don't filter by kyc_status

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('prenom', 'LIKE', '%' . $search . '%')
                  ->orWhere('nom', 'LIKE', '%' . $search . '%')
                  ->orWhere('phone', 'LIKE', '%' . $search . '%')
                  ->orWhere('email', 'LIKE', '%' . $search . '%')
                  ->orWhere('business_name', 'LIKE', '%' . $search . '%');
            });
        }

        if (!empty($roleFilter)) {
            $driverIdsWithCat = DB::table('tj_conducteur_categories')
                ->where('category_id', $roleFilter)
                ->pluck('driver_id');
            $query->whereIn('id', $driverIdsWithCat);
        }

        if (!empty($daterange)) {
            $dates = explode(' - ', $daterange);
            if (count($dates) == 2) {
                $startDate = Carbon::createFromFormat('d-m-Y', trim($dates[0]))->startOfDay();
                $endDate = Carbon::createFromFormat('d-m-Y', trim($dates[1]))->endOfDay();
                $query->whereBetween('creer', [$startDate, $endDate]);
            }
        }

        $pendingBusinessUsers = $query->orderBy('id', 'desc')->paginate(15)->appends($request->all());

        // Map roles and professions for business & service providers
        foreach ($pendingBusinessUsers as $user) {
            $mapped = DB::table('tj_conducteur_categories')
                ->where('driver_id', $user->id)
                ->pluck('category_id');
            
            if ($mapped->isEmpty()) {
                $user->role = 'Service Provider';
                $user->profession = 'Service Provider';
            } else {
                $names = DB::table('tj_categorie_user')
                    ->whereIn('id', $mapped)
                    ->pluck('libelle')
                    ->toArray();
                $user->role = implode(', ', $names);
                $user->profession = implode(', ', $names);
            }
        }

        $categoriesList = DB::table('tj_categorie_user')->orderBy('libelle', 'asc')->get();

        return view('settings.users.kyc_verification', compact('pendingBusinessUsers', 'categoriesList'));
    }

    public function updateKycStatus(Request $request)
    {
        $id = $request->input('id');
        $type = $request->input('type'); // 'customer' or 'driver'
        $status = $request->input('status'); // 'approved' or 'disapproved'
        
        $kycValue = ($status == 'approved') ? '1' : '0';
        
        if ($type == 'customer') {
            DB::table('tj_user_app')->where('id', $id)->update(['kyc_status' => $kycValue]);
        } elseif ($type == 'driver') {
            $updateData = ['kyc_status' => $kycValue];
            
            // If disapproved, force driver status to pending/deactivated
            if ($status == 'disapproved') {
                $updateData['statut'] = 'no';
                $updateData['is_verified'] = 0;
            }
            
            DB::table('tj_conducteur')->where('id', $id)->update($updateData);
        }
        
        return response()->json(['success' => true]);
    }

    public function allUsersIndex(Request $request)
    {
        $search = $request->input('search');
        $selectedSearch = $request->input('selected_search');
        $userTypeFilter = $request->input('user_type_filter');

        // Base Query for Consumers
        $consumersQuery = DB::table('tj_user_app')
            ->select('id', 'nom', 'prenom', 'phone', 'alternate_phone', 'email', DB::raw("'consumer' as user_type"), DB::raw("NULL as business_name"), 'amount', 'earn_amount', DB::raw("0 as referral_amount"), 'kyc_status', 'aadhar_number as aadhar_no', 'statut', 'consumer_plan as active_plan', 'm_pin as mpin', 'ac_no', 'creer')
            ->whereNull('deleted_at');

        // Base Query for Drivers
        $driversQuery = DB::table('tj_conducteur')
            ->select('id', 'nom', 'prenom', 'phone', 'alternate_phone', 'email', DB::raw("'driver' as user_type"), 'business_name', 'amount', 'earn_amount', DB::raw("0 as referral_amount"), 'kyc_status', 'aadhar_number as aadhar_no', 'statut', DB::raw("'Vehicle Docs' as active_plan"), 'm_pin as mpin', 'ac_no', 'creer')
            ->whereNull('deleted_at');

        if ($search != '') {
            if ($selectedSearch == 'prenom') {
                $consumersQuery->where(function($q) use ($search) {
                    $q->where('prenom', 'LIKE', '%' . $search . '%')
                      ->orWhere('nom', 'LIKE', '%' . $search . '%');
                });
                $driversQuery->where(function($q) use ($search) {
                    $q->where('prenom', 'LIKE', '%' . $search . '%')
                      ->orWhere('nom', 'LIKE', '%' . $search . '%');
                });
            } elseif ($selectedSearch == 'phone') {
                $consumersQuery->where('phone', 'LIKE', '%' . $search . '%');
                $driversQuery->where('phone', 'LIKE', '%' . $search . '%');
            } elseif ($selectedSearch == 'email') {
                $consumersQuery->where('email', 'LIKE', '%' . $search . '%');
                $driversQuery->where('email', 'LIKE', '%' . $search . '%');
            }
        }

        if ($userTypeFilter == 'consumer') {
            $unionQuery = $consumersQuery;
        } elseif ($userTypeFilter == 'driver') {
            $unionQuery = $driversQuery;
        } else {
            $unionQuery = $consumersQuery->unionAll($driversQuery);
        }

        $total = DB::table(DB::raw("({$unionQuery->toSql()}) as union_table"))
            ->mergeBindings($unionQuery)
            ->count();

        $page = $request->input('page', 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $results = DB::table(DB::raw("({$unionQuery->toSql()}) as union_table"))
            ->mergeBindings($unionQuery)
            ->orderBy('creer', 'desc')
            ->offset($offset)
            ->limit($perPage)
            ->get();

        $users = new \Illuminate\Pagination\LengthAwarePaginator(
            $results,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $driverIds = collect($users)->where('user_type', 'driver')->pluck('id')->filter()->toArray();
        $multiCats = [];
        $vehCats   = [];
        $singleCats = [];

        if (!empty($driverIds)) {
            $multiCats = DB::table('tj_conducteur_categories')
                ->join('tj_categorie_user', 'tj_categorie_user.id', '=', 'tj_conducteur_categories.category_id')
                ->whereIn('tj_conducteur_categories.driver_id', $driverIds)
                ->select('tj_conducteur_categories.driver_id', 'tj_categorie_user.libelle')
                ->get()
                ->groupBy('driver_id');

            $vehCats = DB::table('tj_vehicule')
                ->join('tj_type_vehicule', 'tj_type_vehicule.id', '=', 'tj_vehicule.id_type_vehicule')
                ->whereIn('tj_vehicule.id_conducteur', $driverIds)
                ->select('tj_vehicule.id_conducteur', 'tj_type_vehicule.libelle')
                ->get()
                ->keyBy('id_conducteur');

            $singleCats = DB::table('tj_conducteur')
                ->whereIn('id', $driverIds)
                ->whereNotNull('category_id')
                ->pluck('category_id', 'id');
        }

        foreach ($users as $user) {
            if ($user->user_type == 'consumer') {
                $planName = 'Standard';
                if (!empty($user->active_plan)) {
                    $raw = trim((string)$user->active_plan);
                    if (str_starts_with($raw, '{') || str_starts_with($raw, '[')) {
                        $decoded = json_decode($raw, true);
                        if (is_array($decoded)) {
                            $planName = $decoded['name'] ?? $decoded['title'] ?? $decoded['plan_name'] ?? $decoded['plan'] ?? 'Standard';
                        }
                    } else {
                        $planName = $raw;
                    }
                }
                $user->active_plan_display = $planName;
                $user->category_list = [];
                $user->profession    = 'N/A';
                $user->role          = 'Customer';
            } else {
                $cats = [];
                if (isset($multiCats[$user->id])) {
                    $cats = $multiCats[$user->id]->pluck('libelle')->filter()->toArray();
                }
                if (empty($cats) && isset($singleCats[$user->id])) {
                    $singleCat = DB::table('tj_categorie_user')->where('id', $singleCats[$user->id])->value('libelle');
                    if ($singleCat) $cats[] = $singleCat;
                }
                if (empty($cats) && isset($vehCats[$user->id])) {
                    $cats[] = $vehCats[$user->id]->libelle;
                }
                $user->category_list = array_values(array_unique($cats));
                $user->profession    = !empty($cats) ? implode(', ', $cats) : ($user->business_name ?: 'Business Provider');
                $user->role          = $user->profession;
                $user->active_plan_display = 'Docs';
            }
            $user->referral_code = \App\Services\ReferralCodeService::getOrCreateReferralCode((int)$user->id, $user->user_type);
        }

        return view('settings.users.all_users', compact('users'));
    }

    public function quickUpdateUser(Request $request)
    {
        $id = $request->input('id');
        $userType = $request->input('user_type', 'consumer');
        $field = $request->input('field');
        $value = $request->input('value');

        if (empty($id) || empty($field)) {
            return response()->json(['success' => false, 'message' => 'Invalid parameters'], 400);
        }

        $table = ($userType == 'driver' || $userType == 'business') ? 'tj_conducteur' : 'tj_user_app';

        $updateData = [];

        if ($field == 'name') {
            $updateData['prenom'] = $request->input('prenom', '');
            $updateData['nom'] = $request->input('nom', '');
        } elseif ($field == 'email') {
            $updateData['email'] = $value;
        } elseif ($field == 'phone') {
            $updateData['phone'] = $value;
        } elseif ($field == 'alternate_phone') {
            $updateData['alternate_phone'] = $value;
        } elseif ($field == 'aadhar_number') {
            $updateData['aadhar_number'] = $value;
        } elseif ($field == 'active_plan') {
            if ($table == 'tj_user_app') {
                $updateData['consumer_plan'] = $value;
            } else {
                $updateData['subscription_plan'] = $value;
            }
        } else {
            return response()->json(['success' => false, 'message' => 'Field not supported'], 400);
        }

        $updateData['updated_at'] = date('Y-m-d H:i:s');

        DB::table($table)->where('id', $id)->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $updateData
        ]);
    }

}
