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
                DB::raw("COALESCE(tj_conducteur.nom, tj_user_app.nom, '') as nom"),
                DB::raw("COALESCE(tj_conducteur.prenom, tj_user_app.prenom, '') as prenom"),
                DB::raw("COALESCE(tj_conducteur.phone, tj_user_app.phone, '') as phone"),
                DB::raw("COALESCE(tj_conducteur.email, tj_user_app.email, '') as email"),
                DB::raw("COALESCE(tj_conducteur.bank_name, tj_user_app.bank_name, '') as bank_name"),
                DB::raw("COALESCE(tj_conducteur.branch_name, tj_user_app.branch_name, '') as branch_name"),
                DB::raw("COALESCE(tj_conducteur.holder_name, tj_user_app.holder_name, '') as holder_name"),
                DB::raw("COALESCE(tj_conducteur.account_no, tj_user_app.account_no, '') as account_no"),
                DB::raw("COALESCE(tj_conducteur.other_info, '') as other_info"),
                DB::raw("COALESCE(tj_conducteur.ifsc_code, tj_user_app.ifsc_code, '') as ifsc_code"),
                DB::raw("CASE WHEN tj_conducteur.id IS NOT NULL THEN 'Driver' ELSE 'User' END as user_category")
            );

        if (!empty($id)) {
            $query->where('withdrawals.id_conducteur', '=', $id);
        }

        if ($tab === 'driver') {
            $query->whereNotNull('tj_conducteur.id');
        } elseif ($tab === 'user') {
            $query->whereNull('tj_conducteur.id')->whereNotNull('tj_user_app.id');
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
        if ($user_type === 'user') {
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

        // Check driver first
        $driver = DB::table('tj_conducteur')->where('id', '=', $userId)->first();
        if ($driver) {
            $newAmount = max(0, floatval($driver->amount ?? 0) - $withdraw_amount);
            $newWithdrawable = max(0, floatval($driver->withdrawable_balance ?? 0) - $withdraw_amount);
            DB::table('tj_conducteur')->where('id', '=', $userId)->update([
                'amount'               => $newAmount,
                'withdrawable_balance' => $newWithdrawable,
            ]);

            // Record transaction in history so it appears in history ONLY AFTER admin approval
            $txnId = str_pad((string)$withdrawal->id, 7, '0', STR_PAD_LEFT);
            DB::table('tj_conducteur_transaction')->insert([
                'id_conducteur'   => $userId,
                'ac_no'           => $driver->ac_no ?? $driver->phone,
                'txn_id'          => $txnId,
                'withdraw_status' => 'approved',
                'payment_status'  => 'success',
                'description'     => 'Payout Request ' . number_format($withdraw_amount, 2) . ' Approved',
                'amount'          => $withdraw_amount,
                'type'            => 'debit',
                'deduction_type'  => 0,
                'date'            => date('Y-m-d'),
            ]);
        } else {
            // Check consumer user
            $user = DB::table('tj_user_app')->where('id', '=', $userId)->first();
            if ($user) {
                $newAmount = max(0, floatval($user->amount ?? 0) - $withdraw_amount);
                $newWithdrawable = max(0, floatval($user->withdrawable_balance ?? 0) - $withdraw_amount);
                DB::table('tj_user_app')->where('id', '=', $userId)->update([
                    'amount'               => $newAmount,
                    'withdrawable_balance' => $newWithdrawable,
                ]);

                // Record transaction in history so it appears in history ONLY AFTER admin approval
                $txnId = str_pad((string)$withdrawal->id, 7, '0', STR_PAD_LEFT);
                DB::table('tj_transaction')->insert([
                    'id_user_app'     => $userId,
                    'ac_no'           => $user->ac_no ?? $user->phone,
                    'txn_id'          => $txnId,
                    'withdraw_status' => 'approved',
                    'payment_status'  => 'success',
                    'description'     => 'Payout Request ' . number_format($withdraw_amount, 2) . ' Approved',
                    'amount'          => $withdraw_amount,
                    'type'            => 'debit',
                    'deduction_type'  => 0,
                    'date'            => date('Y-m-d'),
                ]);
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

        $withdrawal->statut = 'rejected';
        $withdrawal->save();

        return response()->json(['success' => true, 'message' => 'Withdrawal request rejected successfully.']);
    }
}
