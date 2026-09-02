<?php
namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\UserApp;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
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
        $all = $request->all();
        if (empty($all) && $request->getContent()) {
            $all = json_decode($request->getContent(), true) ?? [];
        }

        $ac_no     = $all['ac_no'] ?? $request->input('ac_no') ?? $all['user_id'] ?? $all['id_user'] ?? $all['driver_id'] ?? $request->input('user_id') ?? $request->input('id_user') ?? $request->input('driver_id');
        $amount    = floatval($all['amount'] ?? $request->input('amount'));
        $rawType   = strtolower($all['user_type'] ?? $all['sender_type'] ?? $request->input('user_type') ?? $request->input('sender_type') ?? '');
        $user_type = ($rawType === 'driver') ? 'driver' : 'customer';

        // Basic validation
        if (! $ac_no || $amount <= 0) {
            return response()->json([
                'res' => 'error',
                'msg' => 'Valid account and amount are required',
            ]);
        }

        $txn_id = time(); // SAME TXN ID FOR BOTH

        // STEP 1: GET SENDER DATA BASED ON USER TYPE
        $sender = null;
        $senderTable = 'tj_transaction';
        $senderColumn = 'id_user_app';

        if ($user_type === 'driver') {
            $sender = DB::table('tj_conducteur')
                ->where(function($q) use ($ac_no) {
                    $q->where('ac_no', $ac_no)
                      ->orWhere('id', $ac_no)
                      ->orWhere('phone', $ac_no);
                })
                ->first();
            $senderTable  = 'tj_conducteur_transaction';
            $senderColumn = 'id_conducteur';

            if (! $sender) {
                $sender = DB::table('tj_user_app')
                    ->where(function($q) use ($ac_no) {
                        $q->where('ac_no', $ac_no)
                          ->orWhere('id', $ac_no)
                          ->orWhere('phone', $ac_no);
                    })
                    ->first();
                if ($sender) {
                    $user_type    = 'customer';
                    $senderTable  = 'tj_transaction';
                    $senderColumn = 'id_user_app';
                }
            }
        } else {
            $sender = DB::table('tj_user_app')
                ->where(function($q) use ($ac_no) {
                    $q->where('ac_no', $ac_no)
                      ->orWhere('id', $ac_no)
                      ->orWhere('phone', $ac_no);
                })
                ->first();
            $senderTable  = 'tj_transaction';
            $senderColumn = 'id_user_app';

            if (! $sender) {
                $sender = DB::table('tj_conducteur')
                    ->where(function($q) use ($ac_no) {
                        $q->where('ac_no', $ac_no)
                          ->orWhere('id', $ac_no)
                          ->orWhere('phone', $ac_no);
                    })
                    ->first();
                if ($sender) {
                    $user_type    = 'driver';
                    $senderTable  = 'tj_conducteur_transaction';
                    $senderColumn = 'id_conducteur';
                }
            }
        }

        if (! $sender) {
            return response()->json([
                'res' => 'error',
                'msg' => 'Sender not found',
            ]);
        }

        // STEP 2: GET RECEIVER ACCOUNT (or fallback to sender)
        $receiver = DB::table('common_user_base')
            ->where('ac_no', $sender->ac_no ?? $ac_no)
            ->first();

        if (! $receiver) {
            $receiver = (object)[
                'user_id'   => $sender->id,
                'user_type' => $user_type,
                'ac_no'     => $sender->ac_no ?? $sender->id,
            ];
        }

        // STEP 3: CHECK IF SENDER HAS ENOUGH WITHDRAWABLE BALANCE (EARNINGS ONLY)
        $currentBalance = floatval($sender->amount ?? 0);
        $earnBalance = floatval($sender->earn_amount ?? 0);
        $withdrawableBalance = min($currentBalance, $earnBalance);

        if ($amount > $withdrawableBalance) {
            $topupBalance = max(0, $currentBalance - $withdrawableBalance);
            $errText = 'Withdrawal amount (₹' . number_format($amount, 2) . ') exceeds your withdrawable earnings balance of ₹' . number_format($withdrawableBalance, 2) . '.';
            if ($topupBalance > 0) {
                $errText .= ' Self top-up funds (₹' . number_format($topupBalance, 2) . ') cannot be withdrawn via payout and can only be used for platform services.';
            }
            return response()->json([
                'res'     => 'error',
                'success' => 'Failed',
                'msg'     => $errText,
                'error'   => $errText,
            ]);
        }

        // Insert into admin withdrawals table as pending request ONLY (No debit transaction created until admin approval)
        $insertedId = DB::table('withdrawals')->insertGetId([
            'id_conducteur' => $sender->id,
            'amount'        => $amount,
            'note'          => $all['note'] ?? $request->input('note') ?? 'Payout Request',
            'statut'        => 'pending',
            'creer'         => date('Y-m-d H:i:s'),
            'modifier'      => date('Y-m-d H:i:s'),
        ]);

        if ($insertedId) {
            $paddedTxnId = str_pad((string)$insertedId, 7, '0', STR_PAD_LEFT);
            return response()->json([
                'res'     => 'success',
                'success' => 'success',
                'msg'     => 'Payout request submitted successfully. It will be processed after admin approval.',
                'message' => 'Payout request submitted successfully. It will be processed after admin approval.',
                'data'    => [
                    'id'               => $paddedTxnId,
                    'txn_id'           => $paddedTxnId,
                    'widrawals_statut' => 'pending',
                    'widrawals_amount' => $amount,
                ]
            ]);
        } else {
            return response()->json([
                'res' => 'error',
                'msg' => 'Failed to insert payout request data',
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
        $rawSenderType = strtolower($request->sender_type ?? 'customer');
        $sender_type   = ($rawSenderType === 'driver') ? 'driver' : 'customer';
        $current_date   = date('Y-m-d');
        $earn_time      = Carbon::now('Asia/Kolkata')->format('h:i:s'); // Earn time for record

        $mpin = trim($request->mpin ?? '');

        // Basic validation for required fields
        if (! $sender_ac_no || ! $receiver_ac_no || ! $amount || empty($mpin)) {
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
            ->first();

        if (! $receiver) {
            $u = DB::table('tj_user_app')->where('ac_no', $receiver_ac_no)->orWhere('id', $receiver_ac_no)->orWhere('phone', $receiver_ac_no)->first();
            if ($u) {
                $receiver = (object)[
                    'user_id'   => $u->id,
                    'user_type' => 'customer',
                    'ac_no'     => $u->ac_no ?? $u->id,
                ];
            } else {
                $d = DB::table('tj_conducteur')->where('ac_no', $receiver_ac_no)->orWhere('id', $receiver_ac_no)->orWhere('phone', $receiver_ac_no)->first();
                if ($d) {
                    $receiver = (object)[
                        'user_id'   => $d->id,
                        'user_type' => 'driver',
                        'ac_no'     => $d->ac_no ?? $d->id,
                    ];
                }
            }
        }

        if (! $receiver) {
            return response()->json([
                'res' => 'error',
                'msg' => 'Receiver account not found',
            ]);
        }

        $receiver_user_id   = $receiver->user_id;
        $receiver_user_type = ($receiver->user_type === 'driver') ? 'driver' : 'customer';
        $date_heure         = date('Y-m-d H:i:s');

        if ($receiver_user_type === 'driver') {
            $receiverTable  = 'tj_conducteur_transaction';
            $receiverColumn = 'id_conducteur';
        } else {
            $receiverTable  = 'tj_transaction';
            $receiverColumn = 'id_user_app';
        }

        // Step 4: Fetch sender data based on sender type
        $sender = null;
        if ($sender_type === 'driver') {
            $sender = DB::table('tj_conducteur')
                ->where(function($q) use ($sender_ac_no) {
                    $q->where('ac_no', $sender_ac_no)
                      ->orWhere('id', $sender_ac_no)
                      ->orWhere('phone', $sender_ac_no);
                })
                ->first();
            $senderTable  = 'tj_conducteur_transaction';
            $senderColumn = 'id_conducteur';
            $sender_data  = $sender;

            if (! $sender) {
                $sender = DB::table('tj_user_app')
                    ->where(function($q) use ($sender_ac_no) {
                        $q->where('ac_no', $sender_ac_no)
                          ->orWhere('id', $sender_ac_no)
                          ->orWhere('phone', $sender_ac_no);
                    })
                    ->first();
                if ($sender) {
                    $sender_type  = 'customer';
                    $senderTable  = 'tj_transaction';
                    $senderColumn = 'id_user_app';
                    $sender_data  = $sender;
                }
            }
        } else {
            $sender = DB::table('tj_user_app')
                ->where(function($q) use ($sender_ac_no) {
                    $q->where('ac_no', $sender_ac_no)
                      ->orWhere('id', $sender_ac_no)
                      ->orWhere('phone', $sender_ac_no);
                })
                ->first();
            $senderTable  = 'tj_transaction';
            $senderColumn = 'id_user_app';
            $sender_data  = $sender;

            if (! $sender) {
                $sender = DB::table('tj_conducteur')
                    ->where(function($q) use ($sender_ac_no) {
                        $q->where('ac_no', $sender_ac_no)
                          ->orWhere('id', $sender_ac_no)
                          ->orWhere('phone', $sender_ac_no);
                    })
                    ->first();
                if ($sender) {
                    $sender_type  = 'driver';
                    $senderTable  = 'tj_conducteur_transaction';
                    $senderColumn = 'id_conducteur';
                    $sender_data  = $sender;
                }
            }
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
        $senderTxnRecordId = DB::table($senderTable)->insertGetId($senderData);
        $txn_id = str_pad((string)$senderTxnRecordId, 7, '0', STR_PAD_LEFT);
        DB::table($senderTable)->where('id', $senderTxnRecordId)->update(['txn_id' => $txn_id]);

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

        // Step 12: Sender Earnings logic (Cashback on QR / Scan & Pay / Transfer)
        $senderRate = null;
        if (! empty($sender_data->per_sender) && (string) $sender_data->per_sender !== '0') {
            $senderRate = trim((string) $sender_data->per_sender);
        } elseif (! empty($sender->percentage) && (string) $sender->percentage !== '0') {
            $senderRate = trim((string) $sender->percentage);
        }

        $senderDateValid = true;
        if (! empty($sender_data->start_date) && ! empty($sender_data->end_date)) {
            $senderDateValid = ($current_date >= $sender_data->start_date && $current_date <= $sender_data->end_date);
        }

        if ($senderDateValid && ! empty($senderRate)) {
            $cleanedRate = str_replace(['%', '₹', 'rs', 'Rs', ' '], '', $senderRate);
            $numRate = floatval($cleanedRate);
            if ($numRate > 0) {
                if (str_contains(strtolower($senderRate), 'flat') || $numRate > 10) {
                    $sender_earn_amount = round($numRate, 2);
                } else {
                    $sender_earn_amount = round(($amount * $numRate) / 100, 2);
                }

                if ($sender_earn_amount > 0) {
                    if ($sender_type == 'customer') {
                        DB::table('tj_user_app')->where('ac_no', $sender_ac_no)->increment('earn_amount', $sender_earn_amount);
                    } elseif ($sender_type == 'driver') {
                        DB::table('tj_conducteur')->where('ac_no', $sender_ac_no)->increment('earn_amount', $sender_earn_amount);
                    }

                    $Type = 'credit';
                    DB::table('tbl_earning')->insert([
                        'ac_no'       => $sender_ac_no,
                        'description' => 'Cashback ₹' . $sender_earn_amount . ' earned from payment to ' . $receiver_fullname,
                        'earn_wallet' => $sender_earn_amount,
                        'txn_id'      => time(),
                        'date'        => date('Y-m-d'),
                        'created_at'  => $Type,
                        'time'        => $earn_time,
                    ]);
                }
            }
        }

        // Step 13: Receiver Earnings logic (Receiving QR / Scan & Pay / Transfer)
        $receiverRate = null;
        if (! empty($receiver_data->per_receiver) && (string) $receiver_data->per_receiver !== '0') {
            $receiverRate = trim((string) $receiver_data->per_receiver);
        }

        $receiverDateValid = true;
        if (! empty($receiver_data->start_date) && ! empty($receiver_data->end_date)) {
            $receiverDateValid = ($current_date >= $receiver_data->start_date && $current_date <= $receiver_data->end_date);
        }

        if ($receiverDateValid && ! empty($receiverRate)) {
            $cleanedRecRate = str_replace(['%', '₹', 'rs', 'Rs', ' '], '', $receiverRate);
            $numRecRate = floatval($cleanedRecRate);
            if ($numRecRate > 0) {
                if (str_contains(strtolower($receiverRate), 'flat') || $numRecRate > 10) {
                    $receiver_earn_amount = round($numRecRate, 2);
                } else {
                    $receiver_earn_amount = round(($amount * $numRecRate) / 100, 2);
                }

                if ($receiver_earn_amount > 0) {
                    if ($receiver_user_type == 'customer') {
                        DB::table('tj_user_app')->where('ac_no', $receiver_ac_no)->increment('earn_amount', $receiver_earn_amount);
                    } elseif ($receiver_user_type == 'driver') {
                        DB::table('tj_conducteur')->where('ac_no', $receiver_ac_no)->increment('earn_amount', $receiver_earn_amount);
                    }

                    $Type2 = 'credit';
                    DB::table('tbl_earning')->insert([
                        'ac_no'       => $receiver_ac_no,
                        'description' => 'Earned ₹' . $receiver_earn_amount . ' from receiving transfer from ' . $sender_fullname,
                        'earn_wallet' => $receiver_earn_amount,
                        'txn_id'      => time(),
                        'date'        => date('Y-m-d'),
                        'created_at'  => $Type2,
                        'time'        => $earn_time,
                    ]);
                }
            }
        }

        // --- NEW CODE: FETCH UPDATED SENDER BALANCE FOR RESPONSE ---
        if ($sender_type == 'customer') {
            $updatedSender = DB::table('tj_user_app')->select('amount')->where('ac_no', $sender_ac_no)->first();
        } else {
            $updatedSender = DB::table('tj_conducteur')->select('amount')->where('ac_no', $sender_ac_no)->first();
        }

        $formattedTxnId = str_pad((string)$txn_id, 7, '0', STR_PAD_LEFT);
        $responseData = [
            'id'              => $formattedTxnId,
            'transaction_id'  => $formattedTxnId,
            'txn_id'          => $formattedTxnId,
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
        $ac_no    = trim((string)($request->ac_no ?? $request->account_no ?? ''));
        $userId   = trim((string)($request->user_id ?? $request->id_user ?? $request->driver_id ?? $request->id_driver ?? ''));
        $phone    = trim((string)($request->phone ?? $request->phone_number ?? ''));
        $userType = strtolower(trim((string)($request->user_type ?? $request->user_cat ?? '')));

        $opass = trim((string)($request->opass ?? $request->anc_mdp ?? $request->old_password ?? $request->old_mpin ?? ''));
        $npass = trim((string)($request->npass ?? $request->new_mdp ?? $request->new_password ?? $request->new_mpin ?? ''));
        $cpass = trim((string)($request->cpass ?? $request->confirm_password ?? $request->confirm_mpin ?? $npass));

        if (empty($npass)) {
            return response()->json([
                'res'     => 'error',
                'success' => 'Failed',
                'msg'     => 'New MPIN is required.',
                'error'   => 'New MPIN is required.',
            ], 422);
        }

        if (strlen($npass) < 4) {
            return response()->json([
                'res'     => 'error',
                'success' => 'Failed',
                'msg'     => 'New MPIN must be at least 4 digits.',
                'error'   => 'New MPIN must be at least 4 digits.',
            ], 422);
        }

        if ($npass !== $cpass) {
            return response()->json([
                'res'     => 'error',
                'success' => 'Failed',
                'msg'     => 'New MPIN and Confirm MPIN do not match.',
                'error'   => 'New MPIN and Confirm MPIN do not match.',
            ], 422);
        }

        // Locate user record
        $userTable = null;
        $resolvedUserId = null;
        $userRecord = null;

        // 1. Check common_user_base if ac_no is given
        if (!empty($ac_no)) {
            $common = DB::table('common_user_base')->where('ac_no', $ac_no)->first();
            if ($common) {
                $userType = ($common->user_type === 'driver') ? 'driver' : 'customer';
                $resolvedUserId = $common->user_id;
                $userTable = ($userType === 'driver') ? 'tj_conducteur' : 'tj_user_app';
                $userRecord = DB::table($userTable)->where('id', $resolvedUserId)->first();
            }
        }

        // 2. Lookup by userId if not found
        if (!$userRecord && !empty($userId)) {
            if ($userType === 'driver' || $request->is('*driver*')) {
                $userTable = 'tj_conducteur';
                $userRecord = DB::table('tj_conducteur')->where('id', $userId)->first();
                $userType = 'driver';
            } else {
                $userTable = 'tj_user_app';
                $userRecord = DB::table('tj_user_app')->where('id', $userId)->first();
                $userType = 'customer';
                if (!$userRecord) {
                    $userRecord = DB::table('tj_conducteur')->where('id', $userId)->first();
                    if ($userRecord) {
                        $userTable = 'tj_conducteur';
                        $userType = 'driver';
                    }
                }
            }
            if ($userRecord) {
                $resolvedUserId = $userRecord->id;
            }
        }

        // 3. Lookup by phone if still not found
        if (!$userRecord && !empty($phone)) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
            if ($userType === 'driver' || $request->is('*driver*')) {
                $userTable = 'tj_conducteur';
                $userRecord = DB::table('tj_conducteur')->where('phone', 'like', "%$cleanPhone%")->first();
                $userType = 'driver';
            } else {
                $userTable = 'tj_user_app';
                $userRecord = DB::table('tj_user_app')->where('phone', 'like', "%$cleanPhone%")->first();
                $userType = 'customer';
                if (!$userRecord) {
                    $userRecord = DB::table('tj_conducteur')->where('phone', 'like', "%$cleanPhone%")->first();
                    if ($userRecord) {
                        $userTable = 'tj_conducteur';
                        $userType = 'driver';
                    }
                }
            }
            if ($userRecord) {
                $resolvedUserId = $userRecord->id;
            }
        }

        if (!$userRecord || !$userTable) {
            return response()->json([
                'res'     => 'error',
                'success' => 'Failed',
                'msg'     => 'User account not found.',
                'error'   => 'User account not found.',
            ], 404);
        }

        // Verify Old MPIN (against stored m_pin or hashed mdp)
        $storedMpin = trim((string)($userRecord->m_pin ?? ''));
        $storedMdp  = trim((string)($userRecord->mdp ?? ''));
        $oldMatches = false;

        if ($storedMpin === '' && $storedMdp === '') {
            // First time setting MPIN
            $oldMatches = true;
        } elseif (empty($opass)) {
            return response()->json([
                'res'     => 'error',
                'success' => 'Failed',
                'msg'     => 'Current MPIN is required to change MPIN.',
                'error'   => 'Current MPIN is required to change MPIN.',
            ], 422);
        } elseif ($storedMpin !== '' && $storedMpin === $opass) {
            $oldMatches = true;
        } elseif ($storedMdp !== '' && ($storedMdp === md5($opass) || $storedMdp === $opass)) {
            $oldMatches = true;
        }

        if (!$oldMatches) {
            return response()->json([
                'res'     => 'error',
                'success' => 'Failed',
                'msg'     => 'Incorrect current MPIN.',
                'error'   => 'Incorrect current MPIN.',
            ], 400);
        }

        // Update MPIN & hashed mdp
        try {
            $updateData = [
                'm_pin' => $npass,
                'mdp'   => md5($npass),
            ];

            if (Schema::hasColumn($userTable, 'modifier')) {
                $updateData['modifier'] = now()->toDateTimeString();
            }

            DB::table($userTable)
                ->where('id', $resolvedUserId)
                ->update($updateData);

            $responseData = [
                'user_id'   => (string)$resolvedUserId,
                'user_type' => $userType,
                'ac_no'     => $userRecord->ac_no ?? $ac_no,
                'name'      => trim(($userRecord->prenom ?? '') . ' ' . ($userRecord->nom ?? '')),
                'm_pin'     => $npass,
            ];

            return response()->json([
                'res'     => 'success',
                'success' => 'success',
                'msg'     => 'MPIN changed successfully',
                'message' => 'MPIN changed successfully',
                'data'    => $responseData,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'res'     => 'error',
                'success' => 'Failed',
                'msg'     => 'Failed to update MPIN: ' . $e->getMessage(),
                'error'   => 'Failed to update MPIN',
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

        // Dynamic Earn Amount (Cashback from QR/Scan & Pay + Service Earnings)
        $earningWalletSum = 0;
        if (Schema::hasTable('tbl_earning') && ! empty($user->ac_no)) {
            $earningWalletSum = DB::table('tbl_earning')
                ->where('ac_no', $user->ac_no)
                ->where(function ($q) {
                    $q->where('created_at', 'credit')
                      ->orWhere('created_at', 'LIKE', '%credit%')
                      ->orWhere('earn_wallet', '>', 0);
                })
                ->sum('earn_wallet');
        }

        $serviceEarnings = 0;
        if ($userType === 'driver' || isset($user->statut_vehicule)) {
            $rideEarn   = DB::table('tj_requete')->where('id_conducteur', $user->id)->where('statut', 'completed')->sum('montant');
            $parcelEarn = DB::table('parcel_orders')->where('id_conducteur', $user->id)->where('status', 'completed')->sum('amount');
            $srvEarn    = 0;
            if (Schema::hasTable('service_requests')) {
                $srvEarn = DB::table('service_requests')->where('driver_id', $user->id)->whereIn('status', ['Completed', 'completed'])->sum('amount');
            }
            $serviceEarnings = floatval($rideEarn) + floatval($parcelEarn) + floatval($srvEarn);
        }

        $calcEarn   = round(floatval($earningWalletSum) + floatval($serviceEarnings), 2);
        $storedEarn = floatval($user->earn_amount ?? 0);
        $finalEarn  = max($storedEarn, $calcEarn);

        $userArray['earn_amount'] = (string) number_format($finalEarn, 2, '.', '');

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
        $all = $request->all();
        if (empty($all) && $request->getContent()) {
            $all = json_decode($request->getContent(), true) ?? [];
        }

        $acNo     = $all['ac_no'] ?? $request->input('ac_no');
        $userId   = $all['user_id'] ?? $all['id_user'] ?? $all['driver_id'] ?? $all['id'] ?? $request->input('user_id') ?? $request->input('id_user') ?? $request->input('driver_id') ?? $request->input('id');
        $userType = $all['user_type'] ?? $request->input('user_type');

        $isDriverReq = false;
        if ($userType === 'driver' || $request->is('*show_wallet_amount/driver*')) {
            $isDriverReq = true;
        } else if ($userType !== 'customer' && $userType !== 'user' && $userType !== 'user_app') {
            if (!empty($all['driver_id']) || !empty($all['id_driver']) || $request->has('driver_id') || $request->is('*driver*')) {
                $isDriverReq = true;
            }
        }

        $user = null;
        if ($isDriverReq) {
            if (!empty($userId)) {
                $user = DB::table('tj_conducteur')->where('id', $userId)->first();
            }
            if (!$user && !empty($acNo)) {
                $user = DB::table('tj_conducteur')->where('ac_no', $acNo)->orWhere('id', $acNo)->first();
            }
            if (!$user && !empty($userId)) {
                $user = DB::table('tj_user_app')->where('id', $userId)->first();
            }
        } else {
            if (!empty($userId)) {
                $user = DB::table('tj_user_app')->where('id', $userId)->first();
            }
            if (!$user && !empty($acNo)) {
                $user = DB::table('tj_user_app')->where('ac_no', $acNo)->orWhere('id', $acNo)->first();
            }
            if (!$user && !empty($userId)) {
                $user = DB::table('tj_conducteur')->where('id', $userId)->first();
            }
        }

        if (! $user) {
            return response()->json([
                'res' => 'error',
                'msg' => 'Wallet not found.',
            ], 404);
        }

        $totalEarnings = (string) ($user->earn_amount ?? '0');
        $userBalance = floatval($user->amount ?? 0);
        if ($isDriverReq || isset($user->statut_vehicule)) {
            $rideEarnings = DB::table('tj_requete')
                ->where('id_conducteur', $user->id)
                ->where('statut', 'completed')
                ->sum('montant');

            $parcelEarnings = DB::table('parcel_orders')
                ->where('id_conducteur', $user->id)
                ->where('status', 'completed')
                ->sum('amount');

            $serviceEarnings = 0;
            if (Schema::hasTable('service_requests')) {
                $serviceEarnings = DB::table('service_requests')
                    ->where('driver_id', $user->id)
                    ->whereIn('status', ['Completed', 'completed'])
                    ->sum('amount');
            }

            $calcEarn = round(floatval($rideEarnings) + floatval($parcelEarnings) + floatval($serviceEarnings), 2);
            if ($calcEarn > 0 || empty($user->earn_amount) || $user->earn_amount == '0') {
                $totalEarnings = strval($calcEarn);
            }

            $userBalance = round(floatval($user->amount ?? 0), 2);
        } else {
            $earningWalletSum = 0;
            if (!empty($user->ac_no) && Schema::hasTable('tbl_earning')) {
                $earningWalletSum = DB::table('tbl_earning')
                    ->where('ac_no', $user->ac_no)
                    ->where(function ($q) {
                        $q->where('created_at', 'credit')
                          ->orWhere('created_at', 'LIKE', '%credit%')
                          ->orWhere('earn_wallet', '>', 0);
                    })
                    ->sum('earn_wallet');
            }
            $finalEarn = max(floatval($user->earn_amount ?? 0), floatval($earningWalletSum));
            $totalEarnings = strval(number_format($finalEarn, 2, '.', ''));
            $userBalance = round(floatval($user->amount ?? 0), 2);
        }

        return response()->json([
            'res'  => 'success',
            'msg'  => 'Wallet amount fetched successfully',
            'data' => [
                'amount'         => (string) $userBalance,
                'wallet_amount'  => (string) $userBalance,
                'earn_amount'    => $totalEarnings,
                'total_earnings' => $totalEarnings,
            ],
        ]);
    }

    public function show_transaction_history(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'ac_no'     => 'nullable',
            'user_type' => 'nullable|in:customer,driver',
            'days'      => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'res'    => 'error',
                'msg'    => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $acNo     = $request->ac_no;
        $userType = $request->get('user_type');
        $days     = (int) $request->get('days', 0);
        $userId   = $request->get('id_user_app') ?? $request->get('user_id') ?? $request->get('driver_id') ?? $request->get('id_conducteur');
        $user     = null;

        // Auto-detect userType if not explicitly specified
        if (empty($userType)) {
            if (!empty($userId)) {
                if (DB::table('tj_conducteur')->where('id', $userId)->orWhere('ac_no', $userId)->exists()) {
                    $userType = 'driver';
                } else {
                    $userType = 'customer';
                }
            } elseif (!empty($acNo)) {
                if (DB::table('tj_conducteur')->where('ac_no', $acNo)->orWhere('id', $acNo)->exists()) {
                    $userType = 'driver';
                } else {
                    $userType = 'customer';
                }
            } else {
                $userType = 'customer';
            }
        }

        if (! empty($acNo)) {
            if ($userType === 'driver') {
                $user = DB::table('tj_conducteur')->where('ac_no', $acNo)->first();
                if (! $user) $user = DB::table('tj_conducteur')->where('id', $acNo)->first();
            } else {
                $user = DB::table('tj_user_app')->where('ac_no', $acNo)->first();
                if (! $user) $user = DB::table('tj_user_app')->where('id', $acNo)->first();
            }
        }
        if (! $user && ! empty($userId)) {
            if ($userType === 'driver') {
                $user = DB::table('tj_conducteur')->where('id', $userId)->first();
                if (! $user) $user = DB::table('tj_conducteur')->where('ac_no', $userId)->first();
            } else {
                $user = DB::table('tj_user_app')->where('id', $userId)->first();
                if (! $user) $user = DB::table('tj_user_app')->where('ac_no', $userId)->first();
            }
        }

        if (! $user) {
            return response()->json([
                'res' => 'error',
                'msg' => 'User not found for this account.',
            ], 404);
        }

        if ($userType === 'driver') {
            $query = DB::table('tj_conducteur_transaction')
                ->where(function ($q) use ($user) {
                    $q->where('id_conducteur', $user->id)
                      ->orWhere('id_conducteur', (string) $user->id);
                    if (!empty($user->ac_no)) {
                        $q->orWhere('id_conducteur', $user->ac_no);
                    }
                })
                ->orderBy('id', 'desc');
        } else {
            $query = DB::table('tj_transaction')
                ->where(function ($q) use ($user) {
                    $q->where('id_user_app', $user->id)
                      ->orWhere('id_user_app', (string) $user->id);
                    if (!empty($user->ac_no)) {
                        $q->orWhere('id_user_app', $user->ac_no)
                          ->orWhere('ac_no', $user->ac_no)
                          ->orWhere('ac_no', (string) $user->ac_no);
                    }
                })
                ->orderBy('id', 'desc');
        }

        if ($days > 0) {
            $query->where(function ($q) use ($days) {
                $fromDate = Carbon::now('Asia/Kolkata')->subDays($days)->startOfDay();
                $q->where('creer', '>=', $fromDate->format('Y-m-d H:i:s'))
                    ->orWhere(function ($inner) use ($fromDate) {
                        $inner->where(function ($invalidDate) {
                            $invalidDate->whereNull('creer')
                                ->orWhere('creer', '0000-00-00 00:00:00');
                        })->where('date', '>=', $fromDate->format('Y-m-d'));
                    });
            });
        }

        $transactions = $query->get();
        $output       = [];
        $processedRideCommissions = [];

        foreach ($transactions as $row) {
            $rideIdStr = !empty($row->id_ride) ? (string) $row->id_ride : null;
            $rawAmount = floatval($row->amount ?? 0);
            $isComm = (($row->payment_method ?? '') === 'Commission')
                || stripos((string) ($row->note ?? ''), 'commission') !== false
                || stripos((string) ($row->description ?? ''), 'commission') !== false
                || str_starts_with(trim((string) ($row->amount ?? '')), '-');

            if ($userType === 'driver' && $rideIdStr) {
                $commAmt = 0;
                $fullAmount = 0;
                $svc = Schema::hasTable('service_requests') ? DB::table('service_requests')->where('id', $rideIdStr)->first() : null;
                if ($svc) {
                    $fullAmount = (float) ($svc->amount ?? 0);
                    if ($fullAmount > 0) {
                        $commAmt = round($fullAmount * 0.10, 2);
                    }
                } else {
                    $ride = DB::table('tj_requete')->where('id', $rideIdStr)->first();
                    if ($ride) {
                        $fullAmount = (float) ($ride->montant ?? $ride->amount ?? $ride->total_amount ?? 0);
                        $commAmt = (float) ($ride->admin_commission ?? 0);
                        if ($commAmt <= 0 && $fullAmount > 0) {
                            $commAmt = round($fullAmount * 0.10, 2);
                        }
                    }
                }

                if ($isComm) {
                    // It is a commission deduction: preserve the 10% commission amount, never overwrite with full booking amount!
                    if ($rawAmount == 0 && $commAmt > 0) {
                        $row->amount = (string) $commAmt;
                    } else {
                        $row->amount = (string) abs($rawAmount);
                    }
                    $row->is_raw_negative = true;
                    $row->payment_method = 'Commission';
                    if (empty($row->description)) $row->description = 'Admin Commission';
                    if (empty($row->note)) $row->note = 'Admin Commission for Booking #' . $rideIdStr;
                } else {
                    // It is an earning credit: ensure full service/ride earning amount is present
                    if ($rawAmount <= 0 && $fullAmount > 0) {
                        $row->amount = (string) $fullAmount;
                    }
                }
            }

            $output[] = $this->enrichTransactionHistoryRow($row, (string) $user->id, $userType);
        }

        if ($userType === 'driver') {
            $completedRides = DB::table('tj_requete')
                ->where('id_conducteur', $user->id)
                ->where('statut', 'completed')
                ->orderBy('creer', 'desc')
                ->get();

            $existingRideEarningIds = [];
            foreach ($transactions as $t) {
                if (!empty($t->id_ride) && ($t->payment_method ?? '') !== 'Commission' && !str_starts_with(trim((string)($t->amount ?? '')), '-')) {
                    $existingRideEarningIds[] = (string) $t->id_ride;
                }
            }

            foreach ($completedRides as $cr) {
                $rideIdStr = (string) $cr->id;
                if (!in_array($rideIdStr, $existingRideEarningIds, true)) {
                    $synthetic = (object) [
                        'id' => 'ride_' . $cr->id,
                        'id_conducteur' => $user->id,
                        'id_ride' => $cr->id,
                        'id_parcel' => null,
                        'planId' => null,
                        'amount' => (string) ($cr->montant ?? '0'),
                        'payment_method' => !empty($cr->id_payment_method) ? 'App' : 'Cash',
                        'creer' => $cr->creer,
                        'date' => $cr->creer,
                        'is_raw_negative' => false,
                        'description' => 'Cab Ride Fare',
                        'note' => 'Ride #' . $cr->id,
                    ];
                    $output[] = $this->enrichTransactionHistoryRow($synthetic, (string) $user->id, $userType);
                }
            }

            $completedParcels = DB::table('parcel_orders')
                ->where('id_conducteur', $user->id)
                ->where('status', 'completed')
                ->orderBy('created_at', 'desc')
                ->get();

            $existingParcelEarningIds = [];
            foreach ($transactions as $t) {
                if (!empty($t->id_parcel) && ($t->payment_method ?? '') !== 'Commission' && !str_starts_with(trim((string)($t->amount ?? '')), '-')) {
                    $existingParcelEarningIds[] = (string) $t->id_parcel;
                }
            }

            foreach ($completedParcels as $cp) {
                $parcelIdStr = (string) $cp->id;
                if (!in_array($parcelIdStr, $existingParcelEarningIds, true)) {
                    $synthetic = (object) [
                        'id' => 'parcel_' . $cp->id,
                        'id_conducteur' => $user->id,
                        'id_ride' => null,
                        'id_parcel' => $cp->id,
                        'planId' => null,
                        'amount' => (string) ($cp->amount ?? '0'),
                        'payment_method' => 'Parcel',
                        'creer' => $cp->created_at,
                        'date' => $cp->created_at,
                        'is_raw_negative' => false,
                        'description' => 'Parcel Delivery',
                        'note' => 'Parcel #' . $cp->id,
                    ];
                    $output[] = $this->enrichTransactionHistoryRow($synthetic, (string) $user->id, $userType);
                }
            }

            if (Schema::hasTable('service_requests')) {
                $completedServices = DB::table('service_requests')
                    ->where('driver_id', $user->id)
                    ->whereIn('status', ['Completed', 'completed'])
                    ->orderBy('updated_at', 'desc')
                    ->get();

                $existingServiceEarningIds = [];
                foreach ($transactions as $t) {
                    if (!empty($t->id_ride) && ($t->payment_method ?? '') !== 'Commission' && !str_starts_with(trim((string)($t->amount ?? '')), '-')) {
                        $existingServiceEarningIds[] = (string) $t->id_ride;
                    }
                }

                foreach ($completedServices as $cs) {
                    $svcIdStr = (string) $cs->id;
                    if (!in_array($svcIdStr, $existingServiceEarningIds, true)) {
                        $synthetic = (object) [
                            'id' => 'service_' . $cs->id,
                            'id_conducteur' => $user->id,
                            'id_ride' => $cs->id,
                            'id_parcel' => null,
                            'planId' => null,
                            'amount' => (string) ($cs->amount ?? '0'),
                            'payment_method' => !empty($cs->payment_method) ? $cs->payment_method : ($cs->payment_status === 'paid_cash' ? 'Cash' : 'Online'),
                            'creer' => $cs->updated_at ?? $cs->created_at,
                            'date' => $cs->updated_at ?? $cs->created_at,
                            'is_raw_negative' => false,
                            'description' => $cs->service_name ?? 'Home Service',
                            'note' => 'Service #' . $cs->id . ' - ' . ($cs->service_name ?? 'Home Service'),
                        ];
                        $output[] = $this->enrichTransactionHistoryRow($synthetic, (string) $user->id, $userType);
                    }
                }
            }
        }

        // Sort all transactions chronologically (newest first)
        usort($output, function ($a, $b) {
            $dateA = strtotime($a['creer'] ?? $a['date'] ?? '0');
            $dateB = strtotime($b['creer'] ?? $b['date'] ?? '0');
            return $dateB - $dateA;
        });

        return response()->json([
            'res'  => 'success',
            'msg'  => 'Transaction history fetched successfully',
            'data' => $output,
        ]);
    }

    private function enrichTransactionHistoryRow($row, string $currentUserId, string $currentUserType): array
    {
        $desc            = trim((string) ($row->description ?? ''));
        $note            = trim((string) ($row->note ?? ''));
        $categoryTitle   = 'Wallet Transaction';
        $counterparty    = '';
        $paidFrom        = '';
        $paidTo          = '';
        $iconType        = 'wallet';
        $rideId        = $row->ride_id ?? ($row->id_ride ?? null);
        $parcelId      = $row->parcel_id ?? ($row->id_parcel ?? null);
        $planId        = $row->plan_id ?? ($row->planId ?? null);
        $deductionType = (string) ($row->deduction_type ?? '');
        $type          = strtolower((string) ($row->type ?? ''));
        $paymentMethod = trim((string) ($row->payment_method ?? ''));
        $paymentStatus = strtolower(trim((string) ($row->payment_status ?? 'completed')));
        $isRawNegative = !empty($row->is_raw_negative) || str_starts_with(trim((string) ($row->amount ?? '')), '-');

        $isServiceBooking = ($deductionType === 'Service Booking') ||
                            stripos($note, 'service') !== false ||
                            stripos($desc, 'service') !== false;

        $isCommission = ($paymentMethod === 'Commission') ||
                        stripos($desc, 'commission') !== false ||
                        stripos($note, 'commission') !== false ||
                        ($currentUserType === 'driver' && $isRawNegative && empty($row->id_ride) && empty($row->id_parcel));

        $isMarketplace = stripos($desc, 'marketplace') !== false ||
                         stripos($note, 'marketplace') !== false ||
                         stripos($paymentMethod, 'marketplace') !== false;

        $isReferral = ($paymentMethod === 'Referral Reward') ||
                      stripos($desc, 'referral') !== false ||
                      stripos($note, 'referral') !== false ||
                      stripos($paymentMethod, 'referral') !== false;

        $isCashback = stripos($desc, 'smart value') !== false ||
                      stripos($desc, 'cashback') !== false ||
                      stripos($note, 'cashback') !== false ||
                      $paymentMethod === 'Smart Value Cashback';

        if ($isCommission) {
            $categoryTitle = 'Admin Commission';
            $iconType      = 'commission';
            $counterparty  = 'Fiinway Platform';
            $paidFrom      = 'Partner Wallet';
            $paidTo        = 'Fiinway Platform';
            $deductionType = '0';
        } elseif ($isMarketplace) {
            if ($deductionType === '0' || $type === 'debit' || stripos($desc, 'commission') !== false || stripos($note, 'commission') !== false) {
                $categoryTitle = 'Admin Commission';
                $iconType      = 'commission';
                $counterparty  = 'Fiinway Platform';
                $paidFrom      = 'Your Wallet';
                $paidTo        = 'Fiinway Platform';
                $deductionType = '0';
            } elseif (stripos($desc, 'purchase') !== false || stripos($note, 'purchase') !== false) {
                $categoryTitle = 'Marketplace Purchase';
                $iconType      = 'marketplace';
                $counterparty  = 'Marketplace Store';
                $paidFrom      = 'Your Wallet';
                $paidTo        = 'Marketplace Store';
                $deductionType = '0';
            } else {
                $categoryTitle = 'Marketplace Sale';
                $iconType      = 'marketplace';
                $counterparty  = 'Marketplace Escrow';
                $paidFrom      = 'Marketplace Escrow';
                $paidTo        = 'Your Wallet';
                $deductionType = '1';
            }
        } elseif ($isReferral) {
            $categoryTitle = 'Referral Cashback';
            $iconType      = 'referral';
            $refereeId     = $row->receiver_user_id ?? ($row->sender_user_id ?? null);
            $refereeName   = !empty($refereeId) ? $this->resolvePersonName($refereeId, $row->sender_user_type ?? null) : '';
            $counterparty  = !empty($refereeName) ? ("Referred: " . $refereeName) : 'Fiinway Referral Program';
            $paidFrom      = 'Fiinway Referral Program';
            $paidTo        = 'Your Wallet';
            $deductionType = '1';
        } elseif ($isCashback) {
            $categoryTitle = 'Smart Value Cashback';
            $iconType      = 'reward';
            $counterparty  = 'Smart Value Rewards';
            $paidFrom      = 'Smart Value Rewards';
            $paidTo        = 'Your Wallet';
            $deductionType = '1';
        } elseif (!empty($rideId) && (string) $rideId !== '0') {
            $svc = DB::table('service_requests')->where('id', $rideId)->first();
            if ($svc && ($isServiceBooking || !DB::table('tj_requete')->where('id', $rideId)->exists())) {
                $serviceName = !empty($svc->service_name) ? trim($svc->service_name) : 'Home Service';
                $categoryTitle = $currentUserType === 'driver' ? ($serviceName . ' Earnings') : ($serviceName . ' Booking');
                $iconType      = 'home_service';
                $clientName    = $this->resolvePersonName($svc->user_id, 'customer');
                $providerName  = $this->resolvePersonName($svc->driver_id, 'driver');

                if ($currentUserType === 'driver') {
                    $counterparty = !empty($clientName) ? $clientName : 'Service Client';
                    $paidFrom     = !empty($clientName) ? $clientName : 'Customer';
                    $paidTo       = 'Your Wallet';
                    $deductionType= '1';
                } else {
                    $counterparty = !empty($providerName) ? $providerName : 'Service Expert';
                    $paidFrom     = 'Your Wallet';
                    $paidTo       = !empty($providerName) ? $providerName : 'Service Expert';
                    $deductionType= '0';
                }
            } else {
                $parcel = DB::table('parcel_orders')->where('id', $rideId)->first();
                if ($parcel) {
                    $categoryTitle = $currentUserType === 'driver' ? 'Parcel Delivery Earnings' : 'Parcel Delivery Payment';
                    $iconType      = 'parcel';
                    $clientName    = !empty($parcel->id_user_app) ? $this->resolvePersonName($parcel->id_user_app, 'customer') : '';
                    $receiverName  = trim((string) ($parcel->receiver_name ?? ''));
                    $counterparty  = !empty($receiverName) ? ("To: " . $receiverName) : (!empty($clientName) ? $clientName : 'Parcel Order');
                    $paidFrom      = $currentUserType === 'driver' ? ($clientName ?: 'Sender') : 'Your Wallet';
                    $paidTo        = $currentUserType === 'driver' ? 'Your Wallet' : ($receiverName ?: 'Courier');
                    $deductionType = $currentUserType === 'driver' ? '1' : '0';
                } else {
                    $ride = DB::table('tj_requete')->where('id', $rideId)->first();
                    if ($ride) {
                        $vehicle = !empty($ride->id_type_vehicule) ? DB::table('tj_type_vehicule')->where('id', $ride->id_type_vehicule)->first() : null;
                        $vehicleLabel = strtolower((string) ($vehicle->libelle ?? $ride->ride_type ?? 'Cab'));
                        $rideTypeTitle = str_contains($vehicleLabel, 'bike') ? 'Bike Ride' : (str_contains($vehicleLabel, 'auto') ? 'Auto Ride' : 'Cab Ride');
                        $categoryTitle = $currentUserType === 'driver' ? ($rideTypeTitle . ' Fare') : ($rideTypeTitle . ' Payment');
                        $iconType      = str_contains($vehicleLabel, 'bike') ? 'bike' : (str_contains($vehicleLabel, 'auto') ? 'auto' : 'cab');

                        $riderName  = !empty($ride->id_user_app) ? $this->resolvePersonName($ride->id_user_app, 'customer') : '';
                        $driverName = !empty($ride->id_conducteur) ? $this->resolvePersonName($ride->id_conducteur, 'driver') : '';

                        if ($currentUserType === 'driver') {
                            $counterparty = !empty($riderName) ? $riderName : 'Passenger';
                            $paidFrom     = !empty($riderName) ? $riderName : 'Passenger';
                            $paidTo       = 'Your Wallet';
                            $deductionType= '1';
                        } else {
                            $counterparty = !empty($driverName) ? $driverName : 'Driver Partner';
                            $paidFrom     = 'Your Wallet';
                            $paidTo       = !empty($driverName) ? $driverName : 'Driver Partner';
                            $deductionType= '0';
                        }
                    } else {
                        $categoryTitle = $currentUserType === 'driver' ? 'Ride Fare' : 'Ride Payment';
                        $iconType      = 'cab';
                        $counterparty  = $currentUserType === 'driver' ? 'Passenger' : 'Driver Partner';
                        $deductionType = $currentUserType === 'driver' ? '1' : '0';
                    }
                }
            }
        } elseif (!empty($parcelId) && (string) $parcelId !== '0') {
            $parcel = DB::table('parcel_orders')->where('id', $parcelId)->first();
            $categoryTitle = 'Parcel Delivery';
            $iconType      = 'parcel';
            $counterparty  = trim((string) ($parcel->receiver_name ?? ''));
            $paidFrom      = 'Your Wallet';
            $paidTo        = $counterparty ?: 'Parcel Service';
            $deductionType = '0';
        } elseif (!empty($planId)) {
            $categoryTitle = 'Subscription Plan';
            $iconType      = 'subscription';
            $counterparty  = 'Fiinway Membership Plan';
            $paidFrom      = 'Your Wallet';
            $paidTo        = 'Fiinway Membership';
            $deductionType = '0';
        } elseif (preg_match('/Transferred\s+.+?\s+to\s+(.+)$/i', $desc, $matches)) {
            $categoryTitle = 'Money Transfer';
            $counterparty  = trim($matches[1]);
            $paidFrom      = 'Your Wallet';
            $paidTo        = $counterparty;
            $iconType      = 'transfer';
            $deductionType = '0';
        } elseif (preg_match('/Received\s+.+?\s+from\s+(.+)$/i', $desc, $matches)) {
            $categoryTitle = 'Money Received';
            $counterparty  = trim($matches[1]);
            $paidFrom      = $counterparty;
            $paidTo        = 'Your Wallet';
            $iconType      = 'transfer';
            $deductionType = '1';
        } elseif (stripos($desc, 'withdraw') !== false || stripos($note, 'withdraw') !== false || stripos($desc, 'payout') !== false || stripos($note, 'payout') !== false) {
            $categoryTitle = 'Bank Withdrawal';
            $iconType      = 'withdraw';
            $counterparty  = 'Linked Bank Account';
            $paidFrom      = 'Your Wallet';
            $paidTo        = 'Bank Account';
            $deductionType = '0';
        } elseif (
            $deductionType === '1' || 
            $type === 'credit' ||
            stripos($desc, 'top-up') !== false ||
            stripos($desc, 'topup') !== false ||
            stripos($note, 'top-up') !== false ||
            stripos($note, 'topup') !== false ||
            stripos($desc, 'recharge') !== false ||
            stripos($note, 'recharge') !== false
        ) {
            $categoryTitle = 'Wallet Top-Up';
            $counterparty  = !empty($paymentMethod) ? ('Recharge via ' . $paymentMethod) : 'Wallet Top-Up';
            $paidFrom      = !empty($paymentMethod) ? $paymentMethod : 'Payment Gateway';
            $paidTo        = 'Your Wallet';
            $iconType      = 'topup';
            $deductionType = '1';
        } elseif ($deductionType === '0' || $type === 'debit') {
            $categoryTitle = 'Wallet Payment';
            $counterparty  = !empty($paymentMethod) ? $paymentMethod : 'Fiinway Services';
            $paidFrom      = 'Your Wallet';
            $paidTo        = 'Fiinway Services';
            $iconType      = 'wallet';
            $deductionType = '0';
        }

        if ($deductionType === '') {
            if ($isRawNegative || $paymentMethod === 'Commission') {
                $deductionType = '0';
            } else {
                $deductionType = '1';
            }
        }

        if ($counterparty === '') {
            $receiverId   = $row->receiver_user_id ?? null;
            $receiverType = $row->receiver_user_type ?? $row->user_type ?? null;
            $senderId     = $row->sender_user_id ?? null;
            $senderType   = $row->sender_user_type ?? null;

            if (! empty($receiverId) && (string) $receiverId !== $currentUserId) {
                $counterparty = $this->resolvePersonName($receiverId, $receiverType);
            } elseif (! empty($senderId) && (string) $senderId !== $currentUserId) {
                $counterparty = $this->resolvePersonName($senderId, $senderType);
            }
        }

        if ($counterparty === '' && ($desc !== '' || $note !== '')) {
            $counterparty = $this->guessCounterpartyFromDescription($desc !== '' ? $desc : $note);
        }

        if ($counterparty === '' && $categoryTitle === 'Wallet Payment') {
            $counterparty = 'Service Payment';
        }
        if ($counterparty === '') {
            $counterparty = $currentUserType === 'driver' ? 'Customer' : 'Fiinway User';
        }

        $categoryFromDesc = $this->detectCategoryFromKeywords($desc !== '' ? $desc : $note);
        if ($categoryFromDesc !== null && in_array($categoryTitle, ['Wallet Transaction', 'Wallet Payment'], true)) {
            $categoryTitle = $categoryFromDesc['title'];
            $iconType      = $categoryFromDesc['icon'];
        }

        $rawDate = $row->creer ?? null;
        if (empty($rawDate) || $rawDate === '0000-00-00 00:00:00') {
            $rawDate = ! empty($row->date) ? $row->date . ' 00:00:00' : null;
        }

        $formattedDate = '';
        if (! empty($rawDate) && $rawDate !== '0000-00-00 00:00:00') {
            try {
                $formattedDate = Carbon::parse($rawDate, 'Asia/Kolkata')->format('d M Y, h:i A');
            } catch (\Exception $e) {
                $formattedDate = (string) ($row->date ?? '');
            }
        }

        $statusLabel = 'Pending';
        if (in_array($paymentStatus, ['success', 'paid', 'completed'], true)) {
            $statusLabel = 'Paid';
        } elseif (in_array($paymentStatus, ['failed', 'cancelled', 'canceled', 'rejected'], true)) {
            $statusLabel = ucfirst($paymentStatus);
        } elseif ($paymentStatus !== '' && $paymentStatus !== 'pending') {
            $statusLabel = ucfirst($paymentStatus);
        }

        $paddedId = str_pad((string) $row->id, 7, '0', STR_PAD_LEFT);
        $finalTxnId = !empty($row->txn_id)
            ? (is_numeric($row->txn_id) ? str_pad((string) $row->txn_id, 7, '0', STR_PAD_LEFT) : (string) $row->txn_id)
            : $paddedId;

        return [
            'id'                => $paddedId,
            'transaction_id'    => $finalTxnId,
            'txn_id'            => $finalTxnId,
            'amount'            => (string) $row->amount,
            'id_user_app'       => (string) ($row->id_user_app ?? ''),
            'deduction_type'    => $deductionType,
            'ride_id'           => (string) ($rideId ?? ''),
            'payment_method'    => $paymentMethod,
            'payment_status'    => (string) ($row->payment_status ?? ''),
            'creer'             => (string) ($row->creer ?? ''),
            'modifier'          => (string) ($row->modifier ?? ''),
            'description'       => $desc !== '' ? $desc : $note,
            'note'              => $note,
            'type'              => (string) ($row->type ?? ''),
            'date'              => (string) ($row->date ?? ''),
            'category_title'    => $categoryTitle,
            'counterparty'      => $counterparty,
            'counterparty_name' => $counterparty,
            'paid_from'         => !empty($paidFrom) ? $paidFrom : ($deductionType === '0' ? 'Your Wallet' : $counterparty),
            'paid_to'           => !empty($paidTo) ? $paidTo : ($deductionType === '0' ? $counterparty : 'Your Wallet'),
            'user_name'         => $counterparty,
            'customer_name'     => $counterparty,
            'formatted_date'    => $formattedDate,
            'status_label'      => $statusLabel,
            'icon_type'         => $iconType,
        ];
    }

    private function resolvePersonName($userId, ?string $userType): string
    {
        if (empty($userId)) {
            return '';
        }

        $person = null;
        if ($userType === 'driver') {
            $person = DB::table('tj_conducteur')->where('id', $userId)->first();
            if (!$person) $person = DB::table('tj_conducteur')->where('ac_no', $userId)->first();
        } else {
            $person = DB::table('tj_user_app')->where('id', $userId)->first();
            if (!$person) $person = DB::table('tj_user_app')->where('ac_no', $userId)->first();
            if (!$person) $person = DB::table('tj_conducteur')->where('id', $userId)->first();
        }

        if (! $person) {
            return '';
        }

        $name = trim((string) ($person->name ?? ''));
        if (!empty($name) && strtolower($name) !== 'customer') {
            return $name;
        }

        $prenom = trim((string) ($person->prenom ?? ''));
        $nom = trim((string) ($person->nom ?? ''));
        $full = trim("$prenom $nom");

        return $full !== '' ? $full : ($name !== '' ? $name : '');
    }

    private function guessCounterpartyFromDescription(string $desc): string
    {
        if (preg_match('/\bto\s+(.+)$/i', $desc, $matches)) {
            return trim($matches[1]);
        }
        if (preg_match('/\bfrom\s+(.+)$/i', $desc, $matches)) {
            return trim($matches[1]);
        }

        return '';
    }

    private function detectCategoryFromKeywords(string $desc): ?array
    {
        $text = strtolower($desc);
        $map  = [
            ['Marketplace Purchase', 'marketplace', ['marketplace', 'product purchase', 'purchased', 'bought product']],
            ['Food Order', 'food', ['food', 'swiggy', 'zomato', 'restaurant']],
            ['Education Fee', 'education', ['education', 'school fee', 'college fee', 'tuition fee', 'exam fee', 'course fee']],
            ['Home Service', 'home_service', ['mechanic', 'plumber', 'electrician', 'home service', 'ac mechanic']],
            ['Merchant Transfer', 'merchant', ['merchant', 'kirana', 'store transfer', 'shop payment']],
            ['Parcel Delivery', 'parcel', ['parcel', 'courier', 'bluedart']],
            ['Cab Ride', 'cab', ['cab ride', 'taxi fare', 'ola', 'uber']],
            ['Bike Ride', 'bike', ['bike ride', 'rapido', 'motorcycle']],
        ];

        foreach ($map as [$title, $icon, $keywords]) {
            foreach ($keywords as $keyword) {
                if (str_contains($text, $keyword)) {
                    return ['title' => $title, 'icon' => $icon];
                }
            }
        }

        return null;
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
