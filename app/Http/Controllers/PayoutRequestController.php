<?php

namespace App\Http\Controllers;

use App\Models\Withdrawal;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class PayoutRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function payout(Request $request, $id = null)
    {
        $currency = Currency::where('statut', 'yes')->first();
        $tab = $request->get('tab', 'all');

        $query = DB::table('withdrawals')
            ->leftJoin('tj_conducteur', 'tj_conducteur.id', '=', 'withdrawals.id_conducteur')
            ->leftJoin('tj_user_app', 'tj_user_app.id', '=', 'withdrawals.id_conducteur')
            ->select(
                'withdrawals.*',
                DB::raw("CASE WHEN withdrawals.note LIKE '%[User]%' THEN tj_user_app.nom ELSE COALESCE(tj_conducteur.nom, tj_user_app.nom, '') END as nom"),
                DB::raw("CASE WHEN withdrawals.note LIKE '%[User]%' THEN tj_user_app.prenom ELSE COALESCE(tj_conducteur.prenom, tj_user_app.prenom, '') END as prenom"),
                DB::raw("CASE WHEN withdrawals.note LIKE '%[User]%' THEN tj_user_app.phone ELSE COALESCE(tj_conducteur.phone, tj_user_app.phone, '') END as phone"),
                DB::raw("CASE WHEN withdrawals.note LIKE '%[User]%' THEN tj_user_app.email ELSE COALESCE(tj_conducteur.email, tj_user_app.email, '') END as email"),
                DB::raw("CASE WHEN withdrawals.note LIKE '%[User]%' THEN tj_user_app.bank_name ELSE COALESCE(tj_conducteur.bank_name, tj_user_app.bank_name, '') END as bank_name"),
                DB::raw("CASE WHEN withdrawals.note LIKE '%[User]%' THEN tj_user_app.branch_name ELSE COALESCE(tj_conducteur.branch_name, tj_user_app.branch_name, '') END as branch_name"),
                DB::raw("CASE WHEN withdrawals.note LIKE '%[User]%' THEN tj_user_app.holder_name ELSE COALESCE(tj_conducteur.holder_name, tj_user_app.holder_name, '') END as holder_name"),
                DB::raw("CASE WHEN withdrawals.note LIKE '%[User]%' THEN tj_user_app.account_no ELSE COALESCE(tj_conducteur.account_no, tj_user_app.account_no, '') END as account_no"),
                DB::raw("CASE WHEN withdrawals.note LIKE '%[User]%' THEN '' ELSE COALESCE(tj_conducteur.other_info, '') END as other_info"),
                DB::raw("CASE WHEN withdrawals.note LIKE '%[User]%' THEN tj_user_app.ifsc_code ELSE COALESCE(tj_conducteur.ifsc_code, tj_user_app.ifsc_code, '') END as ifsc_code"),
                DB::raw("CASE WHEN withdrawals.note LIKE '%[User]%' THEN 'User' WHEN withdrawals.note LIKE '%[Driver]%' THEN 'Driver' WHEN tj_conducteur.id IS NOT NULL THEN 'Driver' ELSE 'User' END as user_category")
            );

        if (!empty($id)) {
            $query->where('withdrawals.id_conducteur', '=', $id);
        }

        if ($tab === 'driver') {
            $query->where(function($q) {
                $q->where('withdrawals.note', 'LIKE', '%[Driver]%')
                  ->orWhere(function($sub) {
                      $sub->where('withdrawals.note', 'NOT LIKE', '%[User]%')
                          ->whereNotNull('tj_conducteur.id');
                  });
            });
        } elseif ($tab === 'user') {
            $query->where(function($q) {
                $q->where('withdrawals.note', 'LIKE', '%[User]%')
                  ->orWhere(function($sub) {
                      $sub->whereNull('tj_conducteur.id')
                          ->whereNotNull('tj_user_app.id');
                  });
            });
        }

        $withdrawal = $query->orderBy('withdrawals.id', 'desc')->paginate(20);

        return view("payoutRequest.index")
            ->with("withdrawal", $withdrawal)
            ->with('currency', $currency)
            ->with('selectedTab', $tab);
    }

    public function getBankDetails(Request $request)
    {
        $id = $request->input('id');
        $user_type = strtolower($request->input('user_type') ?? '');

        $bankDetails = null;
        if ($user_type === 'user' || $user_type === 'customer') {
            $bankDetails = DB::table('tj_user_app')->select('*')->where('id', '=', $id)->first();
        } else {
            $bankDetails = DB::table('tj_conducteur')->select('*')->where('id', '=', $id)->first();
            if (!$bankDetails) {
                $bankDetails = DB::table('tj_user_app')->select('*')->where('id', '=', $id)->first();
            }
        }

        $bankName   = $bankDetails->bank_name ?? '';
        $branchName = $bankDetails->branch_name ?? '';
        $accNo      = $bankDetails->account_no ?? '';
        $other_info = $bankDetails->other_info ?? '';
        $ifsc_code  = $bankDetails->ifsc_code ?? '';
        $holderName = $bankDetails->holder_name ?? '';

        $data = [
            'bankName'   => $bankName,
            'branchName' => $branchName,
            'accNo'      => $accNo,
            'other_info' => $other_info,
            'ifsc_code'  => $ifsc_code,
            'holderName' => $holderName,
        ];
        echo json_encode($data);
    }

    public function acceptWithdrawal(Request $request)
    {
        $id = $request->input('id');
        $withdrawal = Withdrawal::find($id);
        if (!$withdrawal) {
            return response()->json(['success' => false, 'message' => 'Withdrawal request not found.']);
        }

        $withdraw_amount = floatval($withdrawal->amount);
        $userId = $withdrawal->id_conducteur;
        $note = (string) ($withdrawal->note ?? '');
        $isUser = (stripos($note, '[User]') !== false) || ($request->input('user_type') === 'user') || ($request->input('user_type') === 'customer');

        if ($isUser) {
            $user = DB::table('tj_user_app')->where('id', '=', $userId)->first();
            if ($user) {
                $padId = str_pad((string)$withdrawal->id, 7, '0', STR_PAD_LEFT);
                $existingTxn = DB::table('tj_transaction')->where('txn_id', $padId)->first();
                if ($existingTxn) {
                    DB::table('tj_transaction')->where('id', $existingTxn->id)->update([
                        'withdraw_status' => 'approved',
                        'payment_status'  => 'success',
                        'description'     => 'Payout Request ₹' . number_format($withdraw_amount, 2) . ' Approved',
                    ]);
                } else {
                    // Legacy record: deduct now
                    $newAmount = max(0, floatval($user->amount ?? 0) - $withdraw_amount);
                    $newEarn = max(0, floatval($user->earn_amount ?? 0) - $withdraw_amount);
                    DB::table('tj_user_app')->where('id', '=', $userId)->update([
                        'amount'      => $newAmount,
                        'earn_amount' => $newEarn,
                    ]);
                    DB::table('tj_transaction')->insert([
                        'id_user_app'     => $userId,
                        'ac_no'           => $user->ac_no ?? $user->phone,
                        'txn_id'          => $padId,
                        'withdraw_status' => 'approved',
                        'payment_status'  => 'success',
                        'payment_method'  => 'Bank Withdrawal',
                        'description'     => 'Payout Request ₹' . number_format($withdraw_amount, 2) . ' Approved',
                        'amount'          => $withdraw_amount,
                        'type'            => 'debit',
                        'deduction_type'  => 0,
                        'date'            => date('Y-m-d'),
                        'creer'           => date('Y-m-d H:i:s'),
                        'modifier'        => date('Y-m-d H:i:s'),
                    ]);
                }
            }
        } else {
            $driver = DB::table('tj_conducteur')->where('id', '=', $userId)->first();
            if ($driver) {
                $padId = str_pad((string)$withdrawal->id, 7, '0', STR_PAD_LEFT);
                $existingTxn = DB::table('tj_conducteur_transaction')->where('txn_id', $padId)->first();
                if ($existingTxn) {
                    DB::table('tj_conducteur_transaction')->where('id', $existingTxn->id)->update([
                        'withdraw_status' => 'approved',
                        'payment_status'  => 'success',
                        'description'     => 'Payout Request ₹' . number_format($withdraw_amount, 2) . ' Approved',
                    ]);
                } else {
                    $newAmount = max(0, floatval($driver->amount ?? 0) - $withdraw_amount);
                    $newEarn = max(0, floatval($driver->earn_amount ?? 0) - $withdraw_amount);
                    DB::table('tj_conducteur')->where('id', '=', $userId)->update([
                        'amount'      => $newAmount,
                        'earn_amount' => $newEarn,
                    ]);
                    DB::table('tj_conducteur_transaction')->insert([
                        'id_conducteur'   => $userId,
                        'ac_no'           => $driver->ac_no ?? $driver->phone,
                        'txn_id'          => $padId,
                        'withdraw_status' => 'approved',
                        'payment_status'  => 'success',
                        'payment_method'  => 'Bank Withdrawal',
                        'description'     => 'Payout Request ₹' . number_format($withdraw_amount, 2) . ' Approved',
                        'amount'          => $withdraw_amount,
                        'type'            => 'debit',
                        'deduction_type'  => 0,
                        'date'            => date('Y-m-d'),
                        'creer'           => date('Y-m-d H:i:s'),
                        'modifier'        => date('Y-m-d H:i:s'),
                    ]);
                }
            }
        }

        $withdrawal->statut = 'success';
        $withdrawal->save();

        return response()->json(['success' => true, 'message' => 'Withdrawal request approved successfully.']);
    }

    public function rejectWithdrawal(Request $request)
    {
        $id = $request->input('id');
        $withdrawal = Withdrawal::find($id);
        if (!$withdrawal) {
            return response()->json(['success' => false, 'message' => 'Withdrawal request not found.']);
        }

        $withdraw_amount = floatval($withdrawal->amount);
        $userId = $withdrawal->id_conducteur;
        $note = (string) ($withdrawal->note ?? '');
        $isUser = (stripos($note, '[User]') !== false) || ($request->input('user_type') === 'user') || ($request->input('user_type') === 'customer');

        $padId = str_pad((string)$withdrawal->id, 7, '0', STR_PAD_LEFT);
        if ($isUser) {
            $existingTxn = DB::table('tj_transaction')->where('txn_id', $padId)->first();
            if ($existingTxn) {
                // Refund wallet
                DB::table('tj_user_app')->where('id', '=', $userId)->increment('amount', $withdraw_amount);
                DB::table('tj_user_app')->where('id', '=', $userId)->increment('earn_amount', $withdraw_amount);
                DB::table('tj_transaction')->where('id', $existingTxn->id)->update([
                    'withdraw_status' => 'rejected',
                    'payment_status'  => 'failed',
                    'description'     => 'Payout Request ₹' . number_format($withdraw_amount, 2) . ' Rejected (Refunded)',
                ]);
            }
        } else {
            $existingTxn = DB::table('tj_conducteur_transaction')->where('txn_id', $padId)->first();
            if ($existingTxn) {
                DB::table('tj_conducteur')->where('id', '=', $userId)->increment('amount', $withdraw_amount);
                DB::table('tj_conducteur')->where('id', '=', $userId)->increment('earn_amount', $withdraw_amount);
                DB::table('tj_conducteur_transaction')->where('id', $existingTxn->id)->update([
                    'withdraw_status' => 'rejected',
                    'payment_status'  => 'failed',
                    'description'     => 'Payout Request ₹' . number_format($withdraw_amount, 2) . ' Rejected (Refunded)',
                ]);
            }
        }

        $withdrawal->statut = 'rejected';
        $withdrawal->save();

        return response()->json(['success' => true, 'message' => 'Withdrawal request rejected and refunded to wallet successfully.']);
    }
}
