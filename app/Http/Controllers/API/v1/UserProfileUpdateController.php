<?php
namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\UserApp;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Image;

class UserProfileUpdateController extends Controller
{

    protected $date; // addon

    public function __construct()
    {
        $this->limit = 20;

        // addon
        $kolkataDateTime = Carbon::now('Asia/Kolkata');
        $this->date      = $kolkataDateTime->format('Y-m-d');
        $this->datetime  = $kolkataDateTime->format('Y-m-d h:i A');
    }

    // public function withdrawWallet(Request $request)
    // {
    //     $ac_no     = $request->ac_no;
    //     $amount    = $request->amount;
    //     $user_type = $request->user_type; // user / partner

    //     // ✅ Select correct sender table + transaction table + ID column
    //     if ($user_type == 'user') {

    //         $sender = DB::table('tj_user_app')
    //             ->where('ac_no', $ac_no)
    //             ->where('statut', 'yes')
    //             ->first();

    //         $transactionTable = 'tj_transaction';
    //         $userIdColumn     = 'id_user_app';

    //     } else {

    //         $sender = DB::table('tj_conducteur')
    //             ->where('ac_no', $ac_no)
    //             ->where('statut', 'yes')
    //             ->first();

    //         $transactionTable = 'tj_conducteur_transaction';
    //         $userIdColumn     = 'id_conducteur';
    //     }

    //     // ✅ Sender not found
    //     if (! $sender) {
    //         return response()->json([
    //             'res' => 'error',
    //             'msg' => 'Sender not found',
    //         ]);
    //     }

    //     // ✅ Wallet balance check
    //     if ($sender->amount < $amount) {
    //         return response()->json([
    //             'res' => 'error',
    //             'msg' => 'Insufficient balance ' . $sender->nom . ' ' . $sender->prenom . ' in your wallet',
    //         ]);
    //     }

    //     // ✅ Build dynamic insert data
    //     $insertData = [
    //         'user_type'       => $user_type,
    //         $userIdColumn     => $sender->id, // ✅ dynamic column here
    //         'ac_no'           => $ac_no,
    //         'txn_id'          => time(),
    //         'withdraw_status' => 'pending',
    //         'description'     => 'Withdraw Request ' . $amount,
    //         'amount'          => $amount,
    //         'type'            => 'debit',
    //         'date'            => date('Y-m-d'),
    //     ];

    //     // ✅ Insert into correct transaction table
    //     $insdata = DB::table($transactionTable)->insert($insertData);

    //     if ($insdata) {
    //         // ✅ Fetch last transaction from the correct table
    //         $lastTransaction = DB::table($transactionTable)
    //             ->orderBy('id', 'desc')
    //             ->first();
    //         return response()->json([
    //             'res'  => 'success',
    //             'msg'  => 'Withdraw request submitted successfully',
    //             'data' => $lastTransaction,
    //         ]);
    //     } else {
    //         return response()->json([
    //             'res' => 'error',
    //             'msg' => 'Failed to insert data',
    //         ]);
    //     }
    // }

    // 2025
//     public function withdrawWallet(Request $request)
//     {
//         $ac_no       = $request->ac_no;
//         $amount      = $request->amount;
//         $sender_type = $request->sender_type;   // customer / driver
//         $sender_id   = $request->sender_id;

//         // Basic validation
//         if (!$ac_no || !$amount || !$sender_type || !$sender_id) {
//             return response()->json([
//                 'res' => 'error',
//                 'msg' => 'All parameters are required',
//             ]);
//         }

//         $txn_id = time(); // SAME TXN ID FOR BOTH

//         // STEP 1: GET RECEIVER ACCOUNT
//         $receiver = DB::table('common_user_base')
//             ->where('ac_no', $ac_no)
//             ->where('status', 1)
//             ->first();

//         if (!$receiver) {
//             return response()->json([
//                 'res' => 'error',
//                 'msg' => 'Receiver account not found',
//             ]);
//         }

//         $receiver_user_id   = $receiver->user_id;
//         $receiver_user_type = $receiver->user_type;

//         // STEP 2: GET SENDER DATA
//         if ($sender_type == 'customer') {

//             $sender = DB::table('tj_user_app')
//                 ->where('id', $sender_id)
//                 ->where('statut', 'yes')
//                 ->first();

//             $senderTable   = 'tj_transaction';
//             $senderColumn  = 'id_user_app';

//         } elseif ($sender_type == 'driver') {

//             $sender = DB::table('tj_conducteur')
//                 ->where('id', $sender_id)
//                 ->where('statut', 'yes')
//                 ->first();

//             $senderTable   = 'tj_conducteur_transaction';
//             $senderColumn  = 'id_conducteur';

//         } else {
//             return response()->json([
//                 'res' => 'error',
//                 'msg' => 'Invalid sender type',
//             ]);
//         }

//         if (!$sender) {
//             return response()->json([
//                 'res' => 'error',
//                 'msg' => 'Sender not found',
//             ]);
//         }

//         if ($sender->amount < $amount) {
//             return response()->json([
//                 'res' => 'error',
//                 'msg' => 'Insufficient balance',
//             ]);
//         }

//         // STEP 3: GET RECEIVER FULL NAME
//         if ($receiver_user_type == 'customer') {
//             $receiverData = DB::table('tj_user_app')->where('id', $receiver_user_id)->first();
//         } else {
//             $receiverData = DB::table('tj_conducteur')->where('id', $receiver_user_id)->first();
//         }

//         // FULL NAMES
//         $sender_fullname   = $sender->nom . ' ' . $sender->prenom;
//         $receiver_fullname = $receiverData->nom . ' ' . $receiverData->prenom;

//         // STEP 4: DYNAMIC DESCRIPTIONS
//         $senderDesc   = "Transferred $amount to $receiver_fullname";
//         $receiverDesc = "Received $amount from $sender_fullname";

//         // STEP 5: INSERT SENDER HISTORY (DEBIT)
//         $senderData = [
//             'sender_user_type'          => $sender_type,
//             $senderColumn        => $sender_id,
//             'receiver_user_id'   => $receiver_user_id,
//             'user_type' => $receiver_user_type,
//             'ac_no'              => $ac_no,
//             'txn_id'             => $txn_id,
//             'payment_status'     => 'pending',
//             'description'        => $senderDesc,
//             'amount'             => $amount,
//             'type'               => 'debit',
//             'deduction_type'     => 0,
//             'date'               => date('Y-m-d'),
//         ];

//         DB::table($senderTable)->insert($senderData);

//         // ------------------------------------------------------
//         // STEP 6: RECEIVER HISTORY TABLE CONFIG
//         // ------------------------------------------------------
//         if ($receiver_user_type == 'customer') {
//             $receiverTable  = 'tj_transaction';
//             $receiverColumn = 'id_user_app';
//         } else {
//             $receiverTable  = 'tj_conducteur_transaction';
//             $receiverColumn = 'id_conducteur';
//         }

//         // ------------------------------------------------------
//         // STEP 7: INSERT RECEIVER HISTORY (CREDIT)
//         // ------------------------------------------------------
//         $receiverDataInsert = [
//             'user_type'          => $receiver_user_type,
//             $receiverColumn      => $receiver_user_id,
//             'sender_user_id'     => $sender_id,
//             'sender_user_type'   => $sender_type,
//             'ac_no'              => $ac_no,
//             'txn_id'             => $txn_id,
//             'payment_status'     => 'pending',
//             'description'        => $receiverDesc,
//             'amount'             => $amount,
//             'type'               => 'credit',
//             'deduction_type'     => 1,
//             'date'               => date('Y-m-d'),
//         ];

//         DB::table($receiverTable)->insert($receiverDataInsert);

//         // ------------------------------------------------------
//         // STEP 8: RETURN FULL TRANSACTION DATA
//         // ------------------------------------------------------
//         return response()->json([
//             'res'  => 'success',
//             'msg'  => 'Transaction stored for sender and receiver successfully',
//             'data' => [
//                 'sender_transaction' => DB::table($senderTable)
//                     ->where('txn_id', $txn_id)
//                     ->where($senderColumn, $sender_id)
//                     ->first(),

//                 'receiver_transaction' => DB::table($receiverTable)
//                     ->where('txn_id', $txn_id)
//                     ->where($receiverColumn, $receiver_user_id)
//                     ->first(),
//             ]
//         ]);
// }

    // Amount transfer Api
// public function transfer_to_wallet(Request $request)
// {
//     // Step 1: Validate the incoming request data
//     $sender_ac_no    = $request->sender_ac_no;
//     $receiver_ac_no  = $request->receiver_ac_no;
//     $amount          = $request->amount;
//     $sender_type     = $request->sender_type;   // customer / driver
//     $current_date    = date('Y-m-d');
//     $earn_time       = Carbon::now('Asia/Kolkata')->format('h:i:s'); // Earn time for record

//     // Basic validation for required fields
//     if (!$sender_ac_no || !$receiver_ac_no || !$amount || !$sender_type) {
//         return response()->json([
//             'res' => 'error',
//             'msg' => 'All parameters are required',
//         ]);
//     }

//     // Step 2: Generate unique transaction ID
//     $txn_id = time(); // Generate a unique transaction ID

//     // Step 3: Fetch receiver data
//     $receiver = DB::table('common_user_base')
//         ->where('ac_no', $receiver_ac_no)
//         ->where('status', 1)
//         ->first();

//     if (!$receiver) {
//         return response()->json([
//             'res' => 'error',
//             'msg' => 'Receiver account not found',
//         ]);
//     }

//     $receiver_user_id   = $receiver->user_id;
//     $receiver_user_type = $receiver->user_type;

//     // Step 4: Fetch sender data based on sender type
//     if ($sender_type == 'customer') {
//         // Sender is a customer
//         $sender = DB::table('tj_user_app')
//             ->where('ac_no', $sender_ac_no)
//             ->where('statut', 'yes')
//             ->first();

//         $senderTable   = 'tj_transaction';
//         $senderColumn  = 'id_user_app';
//         $receiverTable = 'tj_transaction';
//         $receiverColumn = 'id_user_app';

//         // Fetch sender's start_date, end_date, and per_sender
//         $sender_data = DB::table('tj_user_app')
//             ->select('start_date', 'end_date', 'per_sender')
//             ->where('ac_no', $sender_ac_no)
//             ->first();

//     } elseif ($sender_type == 'driver') {
//         // Sender is a driver
//         $sender = DB::table('tj_conducteur')
//             ->where('ac_no', $sender_ac_no)
//             ->where('statut', 'yes')
//             ->first();

//         $senderTable   = 'tj_conducteur_transaction';
//         $senderColumn  = 'id_conducteur';
//         $receiverTable = 'tj_conducteur_transaction';
//         $receiverColumn = 'id_conducteur';

//         // Fetch sender's start_date, end_date, and per_sender
//         $sender_data = DB::table('tj_conducteur')
//             ->select('start_date', 'end_date', 'per_sender')
//             ->where('ac_no', $sender_ac_no)
//             ->first();

//     } else {
//         return response()->json([
//             'res' => 'error',
//             'msg' => 'Invalid sender type',
//         ]);
//     }

//     if (!$sender) {
//         return response()->json([
//             'res' => 'error',
//             'msg' => 'Sender not found',
//         ]);
//     }

//     // Step 5: Check if sender has sufficient balance
//     if ($sender->amount < $amount) {
//         return response()->json([
//             'res' => 'error',
//             'msg' => 'Insufficient balance in sender wallet',
//         ]);
//     }

//     // Step 6: Get receiver full name based on user_type (customer or driver)
//     if ($receiver_user_type == 'customer') {
//         $receiverData = DB::table('tj_user_app')->where('id', $receiver_user_id)->first();
//     } else {
//         $receiverData = DB::table('tj_conducteur')->where('id', $receiver_user_id)->first();
//     }

//     // Full names
//     $sender_fullname   = $sender->nom . ' ' . $sender->prenom;
//     $receiver_fullname = $receiverData->nom . ' ' . $receiverData->prenom;

//     // Step 7: Dynamic descriptions for sender and receiver
//     $senderDesc   = "Transferred $amount to $receiver_fullname";
//     $receiverDesc = "Received $amount from $sender_fullname";

//     // Step 8: Update sender's wallet (decrement amount)
//     if ($sender_type == 'customer') {
//         DB::table('tj_user_app')->where('ac_no', $sender_ac_no)->decrement('amount', $amount);
//     } elseif ($sender_type == 'driver') {
//         DB::table('tj_conducteur')->where('ac_no', $sender_ac_no)->decrement('amount', $amount);
//     }

//     // Step 9: Update receiver's wallet (increment amount)
//     if ($receiver_user_type == 'customer') {
//         DB::table('tj_user_app')->where('ac_no', $receiver_ac_no)->increment('amount', $amount);
//     } elseif ($receiver_user_type == 'driver') {
//         DB::table('tj_conducteur')->where('ac_no', $receiver_ac_no)->increment('amount', $amount);
//     }

//     // Step 10: Insert sender transaction history (debit)
//     $senderData = [
//         'sender_user_type' => $sender_type,
//         $senderColumn      => $sender->id,
//         'receiver_user_id' => $receiver_user_id,
//         'user_type'        => $receiver_user_type,
//         'ac_no'            => $receiver_ac_no,
//         'txn_id'           => $txn_id,
//         'payment_status'   => 'pending',
//         'description'      => $senderDesc,
//         'amount'           => $amount,
//         'type'             => 'debit', // Debit transaction
//         'deduction_type'   => 0,
//         'date'             => date('Y-m-d'),
//     ];

//     DB::table($senderTable)->insert($senderData);

//     // Step 11: Insert receiver transaction history (credit)
//     $receiverDataInsert = [
//         'user_type'        => $receiver_user_type,
//         $receiverColumn    => $receiver_user_id,
//         'sender_user_id'   => $sender->id,
//         'sender_user_type' => $sender_type,
//         'ac_no'            => $receiver_ac_no,
//         'txn_id'           => $txn_id,
//         'payment_status'   => 'pending',
//         'description'      => $receiverDesc,
//         'amount'           => $amount,
//         'type'             => 'credit', // Credit transaction
//         'deduction_type'   => 1,
//         'date'             => date('Y-m-d'),
//     ];

//     DB::table($receiverTable)->insert($receiverDataInsert);

//     // Step 12: Update sender's earn_wallet if within date range
//     if (
//         !empty($sender_data->start_date) && !empty($sender_data->end_date) &&
//         $current_date >= $sender_data->start_date && $current_date <= $sender_data->end_date
//     ) {
//         $sender_earn_amount = ($amount * $sender_data->per_sender) / 100;

//         // Update the correct table (customer or driver) for sender's earnings
//         if ($sender_type == 'customer') {
//             DB::table('tj_user_app')->where('ac_no', $sender_ac_no)->increment('earn_amount', $sender_earn_amount);
//         } elseif ($sender_type == 'driver') {
//             DB::table('tj_conducteur')->where('ac_no', $sender_ac_no)->increment('earn_amount', $sender_earn_amount);
//         }

//         $Type = $sender_earn_amount < 0 ? 'debit' : 'credit';

//         // Insert into tbl_earning for sender
//         DB::table('tbl_earning')->insert([
//             'ac_no'       => $sender_ac_no,
//             'description' => 'Earned ' . $sender_earn_amount . ' from transfer to ' . $receiverData->nom . ' ' . $receiverData->prenom,
//             'earn_wallet' => $sender_earn_amount,
//             'txn_id'      => time(),
//             'date'        => date('Y-m-d'),
//             'created_at'  => $Type,
//             'time'        => $earn_time,
//         ]);
//     }

//     // Step 13: Update receiver's earn_wallet if within date range
//     if (
//         !empty($receiver_data->start_date) && !empty($receiver_data->end_date) &&
//         $current_date >= $receiver_data->start_date && $current_date <= $receiver_data->end_date
//     ) {
//         $receiver_earn_amount = ($amount * $receiver_data->per_receiver) / 100;

//         // Update the correct table (customer or driver) for receiver's earnings
//         if ($receiver_user_type == 'customer') {
//             DB::table('tj_user_app')->where('ac_no', $receiver_ac_no)->increment('earn_amount', $receiver_earn_amount);
//         } elseif ($receiver_user_type == 'driver') {
//             DB::table('tj_conducteur')->where('ac_no', $receiver_ac_no)->increment('earn_amount', $receiver_earn_amount);
//         }

//         $Type2 = $receiver_earn_amount < 0 ? 'debit' : 'credit';

//         // Insert into tbl_earning for receiver
//         DB::table('tbl_earning')->insert([
//             'ac_no'       => $receiver_ac_no,
//             'description' => 'Earned ' . $receiver_earn_amount . ' from receiving transfer from ' . $sender->nom . ' ' . $sender->prenom,
//             'earn_wallet' => $receiver_earn_amount,
//             'txn_id'      => time(),
//             'date'        => date('Y-m-d'),
//             'created_at'  => $Type2,
//             'time'        => $earn_time,
//         ]);
//     }

//     return response()->json([
//         'res' => 'success',
//         'msg' => 'Amount transferred to receiver wallet successfully',
//     ]);
// }

    // Withdrawl Wallet Api Working
    public function withdrawWallet(Request $request)
    {
        $ac_no     = $request->ac_no;
        $amount    = $request->amount;
        $user_type = $request->user_type; // customer / driver

        // Basic validation
        if (! $ac_no || ! $amount || ! $user_type) {
            return response()->json([
                'res' => 'error',
                'msg' => 'All parameters are required',
            ]);
        }

        $txn_id = time(); // SAME TXN ID FOR BOTH

        // STEP 1: GET RECEIVER ACCOUNT (assuming receiver is the same as the ac_no provided)
        $receiver = DB::table('common_user_base')
            ->where('ac_no', $ac_no)
            ->where('status', 1)
            ->first();

        if (! $receiver) {
            return response()->json([
                'res' => 'error',
                'msg' => 'Receiver account not found',
            ]);
        }

        // STEP 2: GET SENDER DATA BASED ON USER TYPE
        if ($user_type == 'customer') {

            $sender = DB::table('tj_user_app')
                ->where('ac_no', $ac_no)
                ->where('statut', 'yes')
                ->first();

            $senderTable  = 'tj_transaction';
            $senderColumn = 'id_user_app';

        } elseif ($user_type == 'driver') {

            $sender = DB::table('tj_conducteur')
                ->where('ac_no', $ac_no)
                ->where('statut', 'yes')
                ->first();

            $senderTable  = 'tj_conducteur_transaction';
            $senderColumn = 'id_conducteur';

        } else {
            return response()->json([
                'res' => 'error',
                'msg' => 'Invalid user type',
            ]);
        }

        if (! $sender) {
            return response()->json([
                'res' => 'error',
                'msg' => 'Sender not found',
            ]);
        }

        // STEP 3: CHECK IF SENDER HAS ENOUGH BALANCE
        if ($sender->amount < $amount) {
            return response()->json([
                'res' => 'error',
                'msg' => 'Insufficient balance',
            ]);
        }

        // STEP 4: DYNAMIC DESCRIPTIONS FOR THE TRANSACTION
        $senderDesc = "Withdraw Request $amount";
        // STEP 5: INSERT SENDER HISTORY (DEBIT)
        $senderData = [
            'user_type'       => $user_type,
            $senderColumn     => $sender->id, // Adjusted to match sender's table column
            'ac_no'           => $ac_no,
            'txn_id'          => $txn_id,
            'withdraw_status' => 'pending',
            'payment_status'  => 'pending',
            'description'     => $senderDesc,
            'amount'          => $amount,
            'type'            => 'debit',
            'deduction_type'  => 0,
            'date'            => date('Y-m-d'),
        ];

        // Insert sender's transaction
        $inserted = DB::table($senderTable)->insert($senderData);

        if ($inserted) {
            $latestTransaction = DB::table($senderTable)
                ->where($senderColumn, $sender->id)
                ->orderBy('id', 'desc')
                ->limit(1)
                ->first();

            // Return the transaction data
            return response()->json([
                'res'  => 'success',
                'msg'  => $user_type . ' Request to Receiver Wallet Successfully',
                'data' => $latestTransaction, // Return the most recent transaction for the sender
            ]);

        } else {
            return response()->json([
                'res' => 'error',
                'msg' => 'Failed to insert data',
            ]);
        }
    }

    // Transfer To Wallet Api

    public function transfer_to_wallet(Request $request)
    {
        // Step 1: Validate the incoming request data
        $sender_ac_no   = $request->sender_ac_no;
        $receiver_ac_no = $request->receiver_ac_no;
        $amount         = $request->amount;
        $sender_type    = $request->sender_type; // customer / driver
        $current_date   = date('Y-m-d');
        $earn_time      = Carbon::now('Asia/Kolkata')->format('h:i:s'); // Earn time for record

        $mpin = trim($request->mpin ?? '');

        // Basic validation for required fields
        if (! $sender_ac_no || ! $receiver_ac_no || ! $amount || ! $sender_type || empty($mpin)) {
            return response()->json([
                'res' => 'error',
                'msg' => 'All parameters are required',
            ]);
        }

        // Step 2: Generate unique transaction ID
        $txn_id = time();

        // Step 3: Fetch receiver data
        $receiver = DB::table('common_user_base')
            ->where('ac_no', $receiver_ac_no)
            ->where('status', 1)
            ->first();

        if (! $receiver) {
            return response()->json([
                'res' => 'error',
                'msg' => 'Receiver account not found',
            ]);
        }

        $receiver_user_id   = $receiver->user_id;
        $receiver_user_type = $receiver->user_type;
        $date_heure         = date('Y-m-d H:i:s');

        if ($receiver_user_type === 'customer') {
            $receiverTable  = 'tj_transaction';
            $receiverColumn = 'id_user_app';
        } elseif ($receiver_user_type === 'driver') {
            $receiverTable  = 'tj_conducteur_transaction';
            $receiverColumn = 'id_conducteur';
        } else {
            return response()->json([
                'res' => 'error',
                'msg' => 'Unsupported receiver type',
            ]);
        }

        // Step 4: Fetch sender data based on sender type
        if ($sender_type == 'customer') {
            $sender = DB::table('tj_user_app')
                ->where('ac_no', $sender_ac_no)
                ->where('statut', 'yes')
                ->first();
            $senderTable  = 'tj_transaction';
            $senderColumn = 'id_user_app';

            // Fetch sender settings
            $sender_data = DB::table('tj_user_app')
                ->select('start_date', 'end_date', 'per_sender', 'per_receiver')
                ->where('ac_no', $sender_ac_no)
                ->first();

        } elseif ($sender_type == 'driver') {
            $sender = DB::table('tj_conducteur')
                ->where('ac_no', $sender_ac_no)
                ->where('statut', 'yes')
                ->first();
            $senderTable  = 'tj_conducteur_transaction';
            $senderColumn = 'id_conducteur';

            // Fetch sender settings
            $sender_data = DB::table('tj_conducteur')
                ->select('start_date', 'end_date', 'per_sender', 'per_receiver')
                ->where('ac_no', $sender_ac_no)
                ->first();
        } else {
            return response()->json([
                'res' => 'error',
                'msg' => 'Invalid sender type',
            ]);
        }

        if (! $sender) {
            return response()->json([
                'res' => 'error',
                'msg' => 'Sender not found',
            ]);
        }

        $hashedMpin = md5($mpin);
        $mpinValid  = ($sender->mdp === $hashedMpin)
            || (! empty($sender->m_pin) && $sender->m_pin === $mpin);

        if (! $mpinValid) {
            return response()->json([
                'res' => 'error',
                'msg' => 'Incorrect MPIN',
            ]);
        }

        // Step 5: Check if sender has sufficient balance
        if ($sender->amount < $amount) {
            return response()->json([
                'res' => 'error',
                'msg' => 'Insufficient balance in sender wallet',
            ]);
        }

        // Step 6: Get receiver full name
        if ($receiver_user_type == 'customer') {
            $receiver_data = DB::table('tj_user_app')->where('id', $receiver_user_id)->first();
        } else {
            $receiver_data = DB::table('tj_conducteur')->where('id', $receiver_user_id)->first();
        }

        $sender_fullname   = $sender->nom . ' ' . $sender->prenom;
        $receiver_fullname = $receiver_data->nom . ' ' . $receiver_data->prenom;

        // Step 7: Dynamic descriptions
        $senderDesc   = "Transferred $amount to $receiver_fullname";
        $receiverDesc = "Received $amount from $sender_fullname";

        // Step 8: Update sender's wallet (decrement)
        if ($sender_type == 'customer') {
            DB::table('tj_user_app')->where('ac_no', $sender_ac_no)->decrement('amount', $amount);
        } elseif ($sender_type == 'driver') {
            DB::table('tj_conducteur')->where('ac_no', $sender_ac_no)->decrement('amount', $amount);
        }

        // Step 9: Update receiver's wallet (increment)
        if ($receiver_user_type == 'customer') {
            DB::table('tj_user_app')->where('ac_no', $receiver_ac_no)->increment('amount', $amount);
        } elseif ($receiver_user_type == 'driver') {
            DB::table('tj_conducteur')->where('ac_no', $receiver_ac_no)->increment('amount', $amount);
        }

        // Step 10: Insert sender transaction history
        $senderData = [
            'sender_user_type' => $sender_type,
            $senderColumn      => $sender->id,
            'receiver_user_id' => $receiver_user_id,
            'user_type'        => $receiver_user_type,
            'ac_no'            => $receiver_ac_no,
            'txn_id'           => $txn_id,
            'payment_status'   => 'success',
            'payment_method'   => 'Wallet',
            'description'      => $senderDesc,
            'amount'           => $amount,
            'type'             => 'debit',
            'deduction_type'   => 0,
            'date'             => date('Y-m-d'),
            'creer'            => $date_heure,
            'modifier'         => $date_heure,
        ];
        DB::table($senderTable)->insert($senderData);

        // Step 11: Insert receiver transaction history
        $receiverDataInsert = [
            'user_type'        => $receiver_user_type,
            $receiverColumn    => $receiver_user_id,
            'sender_user_type' => $sender_type,
            'ac_no'            => $receiver_ac_no,
            'txn_id'           => $txn_id,
            'payment_status'   => 'success',
            'payment_method'   => 'Wallet',
            'description'      => $receiverDesc,
            'amount'           => $amount,
            'type'             => 'credit',
            'deduction_type'   => 1,
            'date'             => date('Y-m-d'),
            'creer'            => $date_heure,
            'modifier'         => $date_heure,
        ];

        if ($receiverTable === 'tj_transaction') {
            $receiverDataInsert['sender_user_id'] = $sender->id;
        }

        DB::table($receiverTable)->insert($receiverDataInsert);

        // Step 12: Sender Earnings logic
        if (
            ! empty($sender_data->start_date) && ! empty($sender_data->end_date) &&
            $current_date >= $sender_data->start_date && $current_date <= $sender_data->end_date &&
            is_numeric($sender_data->per_sender) && $sender_data->per_sender > 0
        ) {
            $sender_earn_amount = ($amount * $sender_data->per_sender) / 100;

            if ($sender_type == 'customer') {
                DB::table('tj_user_app')->where('ac_no', $sender_ac_no)->increment('earn_amount', $sender_earn_amount);
            } elseif ($sender_type == 'driver') {
                DB::table('tj_conducteur')->where('ac_no', $sender_ac_no)->increment('earn_amount', $sender_earn_amount);
            }

            $Type = $sender_earn_amount < 0 ? 'debit' : 'credit';
            DB::table('tbl_earning')->insert([
                'ac_no'       => $sender_ac_no,
                'description' => 'Earned ' . $sender_earn_amount . ' from transfer to ' . $receiver_fullname,
                'earn_wallet' => $sender_earn_amount,
                'txn_id'      => time(),
                'date'        => date('Y-m-d'),
                'created_at'  => $Type,
                'time'        => $earn_time,
            ]);
        }

        // Step 13: Receiver Earnings logic
        if (
            ! empty($receiver_data->start_date) && ! empty($receiver_data->end_date) &&
            $current_date >= $receiver_data->start_date && $current_date <= $receiver_data->end_date &&
            is_numeric($receiver_data->per_receiver) && $receiver_data->per_receiver > 0
        ) {
            $receiver_earn_amount = ($amount * $receiver_data->per_receiver) / 100;

            if ($receiver_user_type == 'customer') {
                DB::table('tj_user_app')->where('ac_no', $receiver_ac_no)->increment('earn_amount', $receiver_earn_amount);
            } elseif ($receiver_user_type == 'driver') {
                DB::table('tj_conducteur')->where('ac_no', $receiver_ac_no)->increment('earn_amount', $receiver_earn_amount);
            }

            $Type2 = $receiver_earn_amount < 0 ? 'debit' : 'credit';
            DB::table('tbl_earning')->insert([
                'ac_no'       => $receiver_ac_no,
                'description' => 'Earned ' . $receiver_earn_amount . ' from receiving transfer from ' . $sender_fullname,
                'earn_wallet' => $receiver_earn_amount,
                'txn_id'      => time(),
                'date'        => date('Y-m-d'),
                'created_at'  => $Type2,
                'time'        => $earn_time,
            ]);
        }

        // --- NEW CODE: FETCH UPDATED SENDER BALANCE FOR RESPONSE ---
        if ($sender_type == 'customer') {
            $updatedSender = DB::table('tj_user_app')->select('amount')->where('ac_no', $sender_ac_no)->first();
        } else {
            $updatedSender = DB::table('tj_conducteur')->select('amount')->where('ac_no', $sender_ac_no)->first();
        }

        $responseData = [
            'txn_id'          => $txn_id,
            'amount'          => $amount,
            'sender_ac_no'    => $sender_ac_no,
            'updated_balance' => $updatedSender ? $updatedSender->amount : 0,
            'receiver_name'   => $receiver_fullname,
            'date'            => $current_date,
            'time'            => $earn_time,
        ];
        // -----------------------------------------------------------

        return response()->json([
            'res'  => 'success',
            'msg'  => 'Amount transferred to receiver wallet successfully',
            'data' => $responseData, // Sending the data object
        ]);
    }

    // Add User
    public function addUser(Request $request)
    {
        // ✅ Validate inputs
        $validator = Validator::make($request->all(), [
            'name'      => 'required|string|max:255',
            'mobile'    => 'required|digits:10|unique:adduser,mobile',
            'ac_no'     => 'required',                    // ac_no zaroori
            'user_type' => 'required|in:customer,driver', // sirf ye 2 types allow
        ]);

        if ($validator->fails()) {
            return response()->json([
                'res'    => 'error',
                'msg'    => $validator->errors()->first(), // first error
                'errors' => $validator->errors(),
            ], 422);
        }
        $ac_no     = $request->ac_no;
        $user_type = $request->user_type;

        $receiver = DB::table('common_user_base')
            ->where('ac_no', $ac_no)
            ->where('user_type', $user_type)
            ->where('status', '1')
            ->first();

        // Agar match hi nahi mila
        if (! $receiver) {
            return response()->json([
                'res' => 'error',
                'msg' => 'Invalid account number or user type, or user is inactive.',
            ], 404);
        }

        $data = [
            'ac_no'      => $ac_no,
            'user_type'  => $user_type,
            'name'       => $request->name,
            'mobile'     => $request->mobile,
            'status'     => '1',
            'created_at' => $this->datetime,
        ];

        try {
            // insert + last inserted id ek hi step me
            $lastInsertedId = DB::table('adduser')->insertGetId($data);

            // Naya user record fetch karo
            $result = DB::table('adduser')->where('id', $lastInsertedId)->first();

            return response()->json([
                'res'  => 'success',
                'msg'  => 'User data inserted successfully',
                // 'data' => $result,
                'data' => [
                    $result,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'res' => 'error',
                'msg' => 'Failed to insert data',
            ], 500);
        }
    }

    // Show Add User
    public function showadduser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ac_no' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'res'    => 'error',
                'msg'    => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $ac_no = $request->ac_no;

        $receiver = DB::table('common_user_base')
            ->where('ac_no', $ac_no)
            ->where('status', '1')
            ->first();

        if (! $receiver) {
            return response()->json([
                'res' => 'error',
                'msg' => 'Invalid account number',
            ], 404);
        }

        $users = DB::table('adduser')
            ->where('ac_no', $ac_no)
            ->where('user_type', $receiver->user_type)
            ->get();

        if ($users->isEmpty()) {
            return response()->json([
                'res' => 'error',
                'msg' => 'Users Not Found',
            ]);
        }

        // 🛠 mobile ko rename karke mobile_no bana rahe hain
        $modifiedUsers = $users->map(function ($user) {
            $user->mobile_no = $user->mobile;
            unset($user->mobile);
            return $user;
        });

        return response()->json([
            'res'  => 'success',
            'msg'  => 'Users Found Here',
            'data' => $modifiedUsers,
        ]);
    }

    // Update MPIN like (Change Password)
    public function UpdateMpin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ac_no' => 'required',
            'opass' => 'required',
            'npass' => 'required',
            'cpass' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'res'    => 'error',
                'msg'    => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $ac_no = $request->ac_no;
        $opass = $request->opass;
        $npass = $request->npass;
        $cpass = $request->cpass;

        // 🔍 Step 1: Find user in common_user_base
        $receiver = DB::table('common_user_base')
            ->where('ac_no', $ac_no)
            ->where('status', '1')
            ->first();

        if (! $receiver) {
            return response()->json([
                'res' => 'error',
                'msg' => 'Invalid account number or user is inactive.',
            ], 404);
        }

                                          // user_type & user_id from common_user_base
        $userType = $receiver->user_type; // expected: 'customer' / 'driver'
        $userId   = $receiver->user_id;   // maps to tj_user_app.id / tj_conducteur.id

        // 🔁 Step 2: Decide table based on user_type
        if ($userType === 'customer') {
            $userTable = 'tj_user_app';
        } elseif ($userType === 'driver') {
            $userTable = 'tj_conducteur';
        } else {
            return response()->json([
                'res' => 'error',
                'msg' => 'Unsupported user type for MPIN update.',
            ], 400);
        }

        // 🔍 Step 3: Fetch user record from respective table
        $user = DB::table($userTable)->where('id', $userId)->first();

        if (! $user) {
            return response()->json([
                'res' => 'error',
                'msg' => 'User account record not found for this account.',
            ], 404);
        }

        // ⚙️ Step 4: Verify Old MPIN
        if ($user->m_pin !== $opass) {
            return response()->json([
                'res' => 'error',
                'msg' => 'Incorrect old MPIN',
            ]);
        }

        // ✅ Step 5: Check New MPIN & Confirm MPIN match
        if ($npass !== $cpass) {
            return response()->json([
                'res' => 'error',
                'msg' => 'New MPIN and confirm MPIN do not match',
            ]);
        }

        // ✅ Step 6: Check if new MPIN already used by another user
        $mpinAlreadyUsed = DB::table($userTable)
            ->where('m_pin', $npass)
            ->where('id', '!=', $userId) // Exclude current user
            ->exists();

        if ($mpinAlreadyUsed) {
            return response()->json([
                'res' => 'error',
                'msg' => 'This MPIN is already in use by another user. Please choose a different MPIN.',
            ]);
        }

        // 📝 Step 7: Update MPIN
        try {
            $updated = DB::table($userTable)
                ->where('id', $userId)
                ->update([
                    'm_pin' => $npass,
                    'mdp'   => md5($npass),
                ]);

            // Even if update returns 0 (if new pass is same as old), we treat it as success or check logic
            // But usually, we proceed if no exception.

            if ($updated) {

                // --- NEW CODE: PREPARE RESPONSE DATA ---
                $responseData = [
                    'user_id'   => $userId,
                    'user_type' => $userType,
                    'ac_no'     => $ac_no,
                    'name'      => $user->nom . ' ' . $user->prenom, // Combining First and Last name
                    'm_pin'     => $npass,                           // Returning the updated MPIN
                ];
                // ---------------------------------------

                return response()->json([
                    'res'  => 'success',
                    'msg'  => 'MPIN changed successfully',
                    'data' => $responseData, // Sending the data object
                ]);
            } else {
                // Edge case: If the new password was exactly the same as the database value,
                // update() might return 0. If you want to handle that as success, remove the else block.
                return response()->json([
                    'res' => 'error',
                    'msg' => 'Failed to update MPIN or MPIN is same as before.',
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'res' => 'error',
                'msg' => 'Something went wrong while updating MPIN',
            ], 500);
        }
    }

    // GetProfileByAcNo
    public function GetProfileByAcNo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ac_no' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'res'    => 'error',
                'msg'    => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $ac_no = $request->ac_no;

        // Step 1: ac_no se common_user_base me user dhundo
        $receiver = DB::table('common_user_base')
            ->where('ac_no', $ac_no)
            ->where('status', '1')
            ->first();

        if (! $receiver) {
            $user = DB::table('tj_user_app')->where('ac_no', $ac_no)->first();
            if (! $user) {
                $user = DB::table('tj_conducteur')->where('ac_no', $ac_no)->first();
            }

            if ($user) {
                return response()->json([
                    'res'  => 'success',
                    'msg'  => 'User found successfully',
                    'data' => (array) $user,
                ]);
            }

            return response()->json([
                'res' => 'error',
                'msg' => 'Invalid account number or user is inactive.',
            ], 404);
        }

                                          // user_type & user_id from common_user_base
        $userType = $receiver->user_type; // 'customer' / 'driver'
        $userId   = $receiver->user_id;   // tj_user_app.id / tj_conducteur.id

        // 🔁 Step 2: user_type ke basis par table decide karo
        if ($userType === 'customer') {
            $userTable = 'tj_user_app';
        } elseif ($userType === 'driver') {
            $userTable = 'tj_conducteur';
        } else {
            return response()->json([
                'res' => 'error',
                'msg' => 'Unsupported user type for this request.',
            ], 400);
        }

        // 🔍 Step 3: respective table me user record lao
        $user = DB::table($userTable)->where('id', $userId)->first();

        if (is_null($user)) {
            return response()->json([
                'res' => 'error',
                'msg' => 'User not found in mapped table.',
            ], 404);
        }
        $userArray = (array) $user;
        return response()->json([
            'res'  => 'success',
            'msg'  => 'User found successfully',
            'data' => $userArray,
        ]);
    }

    public function update(Request $request)
    {

        $id_user = $request->get('id_user');
        $phone   = str_replace("'", "\'", $request->get('phone'));
        $email   = str_replace("'", "\'", $request->get('email'));

        $mdp        = $request->get('mdp');
        $image      = $request->file('image');
        $prenom     = str_replace("'", "\'", $request->get('prenom'));
        $nom        = str_replace("'", "\'", $request->get('nom'));
        $date_heure = date('Y-m-d H:i:s');
        $user       = UserApp::find($id_user);
        $photo_path = $user->photo_path;
        $password   = $user->mdp;
        $date_heure = date('Y-m-d H:i:s');
        $checkEmail = UserApp::where('email', $email)->where('id', '!=', $id_user)->first();
        if (empty($checkEmail)) {
            $checkPhone = UserApp::where('phone', $phone)->where('id', '!=', $id_user)->first();
            if (empty($checkPhone)) {
                if (! empty($image)) {
                    $destination = public_path('assets/images/users/' . $user->photo_path);

                    if (File::exists($destination)) {
                        File::delete($destination);
                    }

                    $image      = '';
                    $file       = $request->file('image');
                    $extenstion = $file->getClientOriginalExtension();
                    $time       = time() . '.' . $extenstion;
                    $filename   = 'User_photo' . $time;
                    $path       = public_path('assets/images/users/') . $filename;
                    Image::make($file->getRealPath())->resize(150, 150)->save($path);
                    $photo_path = $filename;
                }
                if (! empty($mdp)) {
                    $new_mdp  = str_replace("'", "\'", $mdp);
                    $password = md5($new_mdp);
                }
                $updatePayload = [
                    'nom'        => $nom,
                    'prenom'     => $prenom,
                    'email'      => $email,
                    'phone'      => $phone,
                    'mdp'        => $password,
                    'photo_path' => $photo_path,
                    'modifier'   => $date_heure,
                ];
                if ($request->has('alternate_phone')) {
                    $updatePayload['alternate_phone'] = $request->get('alternate_phone');
                }
                if ($request->has('marketplace_enabled')) {
                    $updatePayload['marketplace_enabled'] = $request->get('marketplace_enabled');
                }
                $updatedata = UserApp::where('id', $id_user)->update($updatePayload);

                if ($updatedata >= 0) {
                    $get_user  = UserApp::where('id', $id_user)->first();
                    $row       = $get_user->toArray();
                    $row['id'] = (string) $row['id'];
                    if ($row['photo_path'] != '') {
                        if (file_exists(public_path('assets/images/users' . '/' . $row['photo_path']))) {
                            $image_user = asset('assets/images/users') . '/' . $row['photo_path'];
                        } else {
                            $image_user = asset('assets/images/placeholder_image.jpg');

                        }
                        $row['photo_path'] = $image_user;
                    }
                    if ($row['photo_nic_path'] != '') {
                        if (file_exists(public_path('assets/images/users' . '/' . $row['photo_nic_path']))) {
                            $image = asset('assets/images/users') . '/' . $row['photo_nic_path'];
                        } else {
                            $image = asset('assets/images/placeholder_image.jpg');

                        }
                        $row['photo_nic_path'] = $image;
                    }
                    $row['photo']     = '';
                    $row['photo_nic'] = '';

                    $response['success'] = 'success';
                    $response['error']   = null;
                    $response['message'] = 'successfully updated';
                    $response['data']    = $row;

                } else {
                    $response['success'] = 'Failed';
                    $response['error']   = 'Failed to update';
                }
            } else {
                $response['success'] = 'Failed';
                $response['error']   = 'Phone Already exists';
            }
        } else {
            $response['success'] = 'Failed';
            $response['error']   = 'Email Already exists';
        }
        return response()->json($response);
    }

    // Show Reward/Earning History
    public function show_wallet_amount(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'ac_no' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'res'    => 'error',
                'msg'    => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $acNo = $request->ac_no;
        $user = DB::table('tj_user_app')->where('ac_no', $acNo)->first();

        if (! $user) {
            $user = DB::table('tj_conducteur')->where('ac_no', $acNo)->first();
        }

        if (! $user) {
            return response()->json([
                'res' => 'error',
                'msg' => 'Wallet not found for this account number.',
            ], 404);
        }

        return response()->json([
            'res'  => 'success',
            'msg'  => 'Wallet amount fetched successfully',
            'data' => [
                'amount'      => $user->amount ?? '0',
                'earn_amount' => $user->earn_amount ?? '0',
            ],
        ]);
    }

    public function show_transaction_history(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'ac_no' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'res'    => 'error',
                'msg'    => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $acNo = $request->ac_no;
        $user = DB::table('tj_user_app')->where('ac_no', $acNo)->first();

        if (! $user) {
            return response()->json([
                'res' => 'error',
                'msg' => 'User not found for this account number.',
            ], 404);
        }

        $transactions = DB::table('tj_transaction')
            ->where('id_user_app', $user->id)
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'res'  => 'success',
            'msg'  => 'Transaction history fetched successfully',
            'data' => $transactions,
        ]);
    }

    public function show_reward_history(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'ac_no' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'res'    => 'error',
                'msg'    => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $ac_no = $request->ac_no;

        $rewards = \DB::table('tbl_earning')
            ->where('ac_no', $ac_no)
            ->orderBy('id', 'desc')
            ->get();

        if ($rewards->isEmpty()) {
            return response()->json([
                'res' => 'error',
                'msg' => 'No reward history found',
            ]);
        }

        return response()->json([
            'res'  => 'success',
            'msg'  => 'Reward history found',
            'data' => $rewards,
        ]);
    }

}
