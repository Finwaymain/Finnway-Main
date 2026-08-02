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
        $users = $sql->orderBy('tj_user_app.id', 'desc')->paginate(20);
        $users->map(function ($user) {
            if (! empty($user->email)) {
                $user->email = Helper::shortEmail($user->email);
            }
            if (! empty($user->phone)) {
                $user->phone = Helper::shortNumber($user->phone);
            }
            return $user;
        });

        return view("settings.users.users_shudule_index")->with("users", $users);
    }

    // update wallet/earn_wallet (amount/earn_amount)
    public function update_user_wallet(Request $request)
    {
        $existingUser = DB::table('tj_user_app')->where('ac_no', $request->ac_no)->first();
        $earn_time    = Carbon::now('Asia/Kolkata')->format('h:i:s');
        // Initialize variables with existing values
        $amount      = $existingUser->amount;
        $earn_amount = $existingUser->earn_amount;

        if ($request->has('amount') && ! is_null($request->amount)) {
            $amount = $existingUser->amount + $request->amount;
        }

        if ($request->has('earn_amount') && ! is_null($request->earn_amount)) {
            $earn_amount = $earn_amount + $request->earn_amount;
        }

        // Insert into transaction_history if amount is present and not null
        if ($request->has('amount') && ! is_null($request->amount)) {
            $Type1 = $request->amount < 0 ? 'debit' : 'credit';
            // DB::table('transaction_history')->insert([
            DB::table('tj_transaction')->insert([
                'user_type'       => 'customer',
                'withdraw_status' => '',
                'payment_status'  => '',
                'txn_id'          => time(),
                'ac_no'           => $request->ac_no,
                'description'     => $request->description,
                'amount'          => $request->amount,
                'type'            => $Type1,
                'deduction_type'  => 1,
                'date'            => date('Y-m-d'),
            ]);
        }

        // Insert into tbl_earning if earn_wallet is present and not null
        if ($request->has('earn_amount') && ! is_null($request->earn_amount)) {
            $Type = $request->wallet < 0 ? 'debit' : 'credit';
            DB::table('tbl_earning')->insert([
                'ac_no'       => $request->ac_no,
                'description' => $request->description,
                'earn_wallet' => $request->earn_amount,
                'txn_id'      => time(),
                'date'        => date('Y-m-d'),
                'created_at'  => $Type,
                'time'        => $earn_time,
            ]);
        }

        $data = [
            'amount'      => $amount,
            'earn_amount' => $earn_amount,
        ];
        // Update customer data in the database
        $update    = DB::table('tj_user_app')->where('ac_no', $request->ac_no)->update($data);
        $user_data = DB::table('tj_user_app')->orderBy('id', 'desc')->get();
        return response()->json([
            'data'    => $user_data,
            'success' => 'success',
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

        DB::table('tj_user_app')->whereIn('ac_no', $ac_nos)->update($data);

        return response()->json([
            'success' => 'success',
        ]);
    }

    ### Schedule 2 for users Sender Update Daily Increment
    public function update_wallet_all2(Request $request)
    {
        // Get the IDs from the request
        $ac_nos = explode(',', $request->ac_no);

        $data = [
            'start_date2'     => $request->start_date2 ?? null,
            'end_date2'       => $request->end_date2 ?? null,
            'percentage'      => $request->percentage ?? null,
            'description_2nd' => $request->description_2nd ?? null,
        ];

        // Update user data in the database for each ac_no
        DB::table('tj_user_app')->whereIn('ac_no', $ac_nos)->update($data);

        return response()->json([
            'success' => 'success',
        ]);
    }

    ### Schedule 3 for users Sender Update Deduction
	public function update_wallet_all3(Request $request)
	{
		// Get the IDs from the request
		$ac_nos = explode(',', $request->ac_no);

		$data = [
			'start_date3' => $request->start_date3 ?? null,
			'end_date3' => $request->end_date3 ?? null,
			'per_3rd' => $request->per_3rd ?? null,
			'amount_3rd' => $request->amount_3rd ?? null,
			'description_3rd' => $request->description_3rd ?? null,
		];

		// Update user data in the database for each ac_no
		DB::table('tj_user_app')->whereIn('ac_no', $ac_nos)->update($data);

		return response()->json([
			'success' => 'success'
		]);
	}


	// schedule 4 refer and earn 
	public function update_refer_earn(Request $request)
	{
		// Get the IDs from the request
		$ac_nos = explode(',', $request->ac_no);

		$data = [
			'start_date4' => $request->start_date4 ?? null,
			'end_date4' => $request->end_date4 ?? null,
			'amount_4th' => $request->amount_4th ?? null,
			'description_4th' => $request->description_4th ?? null,
		];

		// Update user data in the database for each ac_no
		DB::table('tj_user_app')->whereIn('ac_no', $ac_nos)->update($data);

		return response()->json([
			'success' => 'success'
		]);
	}


    // all user wallet and earn wallet update here
    public function update_transfer_wallet_all(Request $request)
    {
        $ac_nos    = explode(',', $request->ac_no);
        $ac_nos    = array_reverse($ac_nos);
        $earn_time = Carbon::now('Asia/Kolkata')->format('h:i:s');

        foreach ($ac_nos as $ac_no) {
            $existingUser = DB::table('tj_user_app')->where('ac_no', $ac_no)->first();

            $amount      = $existingUser->amount;
            $earn_amount = $existingUser->earn_amount;

            if ($request->has('amount') && ! is_null($request->amount)) {
                $amount = $existingUser->amount + $request->amount;
            }

            if ($request->has('earn_amount') && ! is_null($request->earn_amount)) {
                $earn_amount = $earn_amount + $request->earn_amount;
            }

            if ($request->has('amount') && ! is_null($request->amount)) {
                $Type1 = $request->amount < 0 ? 'debit' : 'credit';
                DB::table('tj_transaction')->insert([
                    'user_type'       => 'customer',
                    'withdraw_status' => '',
                    'payment_status'  => '',
                    'txn_id'          => time(),
                    'ac_no'           => $ac_no,
                    'description'     => $request->description,
                    'amount'          => $request->amount,
                    'type'            => $Type1,
                    'deduction_type'  => 1,
                    'date'            => date('Y-m-d'),
                ]);
            }

            if ($request->has('earn_amount') && ! is_null($request->earn_amount)) {
                $Type = $request->earn_amount < 0 ? 'debit' : 'credit';
                DB::table('tbl_earning')->insert([
                    'ac_no'       => $ac_no,
                    'description' => $request->description,
                    'earn_wallet' => $request->earn_amount,
                    'txn_id'      => time(),
                    'date'        => date('Y-m-d'),
                    'created_at'  => $Type,
                    'time'        => $earn_time,
                ]);
            }

            $data = [
                'amount'      => $amount,
                'earn_amount' => $earn_amount,
            ];

            DB::table('tj_user_app')->where('ac_no', $ac_no)->update($data);
        }

        $user_data = DB::table('tj_user_app')->orderBy('id', 'desc')->get();

        return response()->json([
            'data'    => $user_data,
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
        $users = $sql->orderBy('tj_user_app.id', 'desc')->paginate(20);
        $users->map(function ($user) {
            if (! empty($user->email)) {
                $user->email = Helper::shortEmail($user->email);
            }
            if (! empty($user->phone)) {
                $user->phone = Helper::shortNumber($user->phone);
            }
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

        $query = DB::table('tj_conducteur')
            ->where(function($q) {
                $q->whereNull('kyc_status')->orWhere('kyc_status', '!=', '1');
            })
            ->whereNull('deleted_at');

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
            ->select('id', 'nom', 'prenom', 'phone', 'email', DB::raw("'consumer' as user_type"), DB::raw("NULL as business_name"), 'amount', 'kyc_status', 'statut', 'creer')
            ->whereNull('deleted_at');

        // Base Query for Drivers
        $driversQuery = DB::table('tj_conducteur')
            ->select('id', 'nom', 'prenom', 'phone', 'email', DB::raw("'driver' as user_type"), 'business_name', 'amount', 'kyc_status', 'statut', 'creer')
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

        foreach ($users as $user) {
            if ($user->user_type == 'driver') {
                $mapped = DB::table('tj_conducteur_categories')
                    ->where('driver_id', $user->id)
                    ->pluck('category_id');
                if ($mapped->isEmpty()) {
                    $user->profession = 'Business Provider';
                    $user->role = 'Business Provider';
                } else {
                    $names = DB::table('tj_categorie_user')
                        ->whereIn('id', $mapped)
                        ->pluck('libelle')
                        ->toArray();
                    $user->profession = implode(', ', $names);
                    $user->role = implode(', ', $names);
                }
            } else {
                $user->profession = 'N/A';
                $user->role = 'Customer';
            }
        }

        return view('settings.users.all_users', compact('users'));
    }

}
