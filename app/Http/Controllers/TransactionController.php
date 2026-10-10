<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request, $id = '')
    {
        if ($id) {
            $query = $this->buildCustomerTransactionsQuery($request, $id);
            $transaction = $query
                ->orderByDesc('tj_transaction.creer')
                ->paginate(20);
        } else {
            $customerQuery = $this->buildCustomerTransactionsQuery($request);
            $driverQuery   = $this->buildDriverTransactionsQuery($request);

            $unionQuery = $customerQuery->unionAll($driverQuery);

            $transaction = DB::query()
                ->fromSub($unionQuery, 'wallet_transactions')
                ->orderByDesc('creer')
                ->paginate(20);
        }

        $currency = Currency::where('statut', 'yes')->first();
        if (!$currency) {
            $currency = (object)['symbole' => '', 'symbol_at_right' => 'false', 'decimal_digit' => 2];
        } else if ($currency->symbole === null) {
            $currency->symbole = '';
        }

        return view('transactions.index')
            ->with('id', $id)
            ->with('transaction', $transaction)
            ->with('currency', $currency);
    }

    public function driverWallet(Request $request, $id = '')
    {
        $query = $this->buildDriverTransactionsQuery($request, $id);

        $transaction = $query
            ->orderByDesc('tj_conducteur_transaction.creer')
            ->paginate(20);

        $currency = Currency::where('statut', 'yes')->first();
        if (!$currency) {
            $currency = (object)['symbole' => '', 'symbol_at_right' => 'false', 'decimal_digit' => 2];
        } else if ($currency->symbole === null) {
            $currency->symbole = '';
        }

        return view('transactions.driver_wallet')
            ->with('transaction', $transaction)
            ->with('currency', $currency)
            ->with('id', $id);
    }

    private function buildCustomerTransactionsQuery(Request $request, $userId = null)
    {
        $query = DB::table('tj_transaction')
            ->leftJoin('tj_user_app', function($join) {
                $join->on('tj_transaction.id_user_app', '=', 'tj_user_app.id')
                     ->orWhere(function($orQ) {
                         $orQ->whereNotNull('tj_transaction.ac_no')
                             ->whereColumn('tj_transaction.ac_no', '=', 'tj_user_app.ac_no');
                     });
            })
            ->leftJoin('tj_payment_method', 'tj_payment_method.libelle', '=', 'tj_transaction.payment_method')
            ->select(
                'tj_transaction.id as id',
                DB::raw("LPAD(tj_transaction.id, 7, '0') as transaction_id"),
                DB::raw("'customer' as account_type"),
                'tj_user_app.id as userId',
                DB::raw("COALESCE(tj_user_app.prenom, 'User') as firstname"),
                DB::raw("COALESCE(tj_user_app.nom, '') as lastname"),
                'tj_transaction.amount',
                'tj_transaction.deduction_type',
                'tj_transaction.payment_method',
                'tj_payment_method.image',
                'tj_transaction.payment_status',
                DB::raw("COALESCE(tj_transaction.creer, tj_transaction.date) as creer"),
                DB::raw("COALESCE(NULLIF(tj_transaction.txn_id, ''), LPAD(tj_transaction.id, 7, '0')) as txn_id"),
                'tj_transaction.description',
                'tj_transaction.type',
                'tj_transaction.ac_no'
            );

        if ($userId) {
            $userObj = DB::table('tj_user_app')->where('id', $userId)->orWhere('ac_no', $userId)->first();
            $query->where(function ($q) use ($userId, $userObj) {
                $q->where('tj_transaction.id_user_app', '=', $userId);
                if ($userObj && !empty($userObj->ac_no)) {
                    $q->orWhere('tj_transaction.ac_no', '=', $userObj->ac_no);
                }
            });
        }

        $this->applyTransactionFilters($query, $request, 'tj_transaction');

        return $query;
    }

    private function buildDriverTransactionsQuery(Request $request, $driverId = null)
    {
        $query = DB::table('tj_conducteur_transaction')
            ->join('tj_conducteur', 'tj_conducteur_transaction.id_conducteur', '=', 'tj_conducteur.id')
            ->leftJoin('tj_payment_method', 'tj_payment_method.libelle', '=', 'tj_conducteur_transaction.payment_method')
            ->select(
                'tj_conducteur_transaction.id as id',
                DB::raw("LPAD(tj_conducteur_transaction.id, 7, '0') as transaction_id"),
                DB::raw("'driver' as account_type"),
                'tj_conducteur.id as userId',
                'tj_conducteur.prenom as firstname',
                'tj_conducteur.nom as lastname',
                'tj_conducteur_transaction.amount',
                'tj_conducteur_transaction.deduction_type',
                'tj_conducteur_transaction.payment_method',
                'tj_payment_method.image',
                'tj_conducteur_transaction.payment_status',
                'tj_conducteur_transaction.creer',
                DB::raw("COALESCE(NULLIF(tj_conducteur_transaction.txn_id, ''), LPAD(tj_conducteur_transaction.id, 7, '0')) as txn_id"),
                'tj_conducteur_transaction.description',
                'tj_conducteur_transaction.type',
                'tj_conducteur_transaction.ac_no'
            );

        if ($driverId) {
            $query->where('tj_conducteur_transaction.id_conducteur', '=', $driverId);
        }

        $this->applyTransactionFilters($query, $request, 'tj_conducteur_transaction');

        return $query;
    }

    public function upiPayments(Request $request)
    {
        $query = DB::table('upi_qr_transactions')
            ->select(
                'upi_qr_transactions.*',
                DB::raw("CASE 
                    WHEN upi_qr_transactions.user_type = 'driver' AND upi_qr_transactions.user_id IS NOT NULL THEN CONCAT(COALESCE(tj_conducteur.prenom, ''), ' ', COALESCE(tj_conducteur.nom, ''))
                    WHEN upi_qr_transactions.user_id IS NOT NULL THEN CONCAT(COALESCE(tj_user_app.prenom, ''), ' ', COALESCE(tj_user_app.nom, ''))
                    ELSE NULL
                END as user_full_name"),
                DB::raw("CASE 
                    WHEN upi_qr_transactions.user_type = 'driver' AND upi_qr_transactions.user_id IS NOT NULL THEN tj_conducteur.phone
                    WHEN upi_qr_transactions.user_id IS NOT NULL THEN tj_user_app.phone
                    ELSE NULL
                END as user_phone")
            )
            ->leftJoin('tj_user_app', function ($join) {
                $join->on('tj_user_app.id', '=', 'upi_qr_transactions.user_id')
                    ->where('upi_qr_transactions.user_type', '=', 'customer');
            })
            ->leftJoin('tj_conducteur', function ($join) {
                $join->on('tj_conducteur.id', '=', 'upi_qr_transactions.user_id')
                    ->where('upi_qr_transactions.user_type', '=', 'driver');
            });

        // Search & Filter
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $field = $request->input('selected_search', 'all');

            $query->where(function ($q) use ($search, $field) {
                if ($field === 'payment_id') {
                    $q->where('upi_qr_transactions.razorpay_payment_id', 'LIKE', "%{$search}%");
                } elseif ($field === 'ac_no') {
                    $q->where('upi_qr_transactions.ac_no', 'LIKE', "%{$search}%");
                } elseif ($field === 'payer_name') {
                    $q->where('upi_qr_transactions.payer_name', 'LIKE', "%{$search}%")
                      ->orWhere('upi_qr_transactions.payer_vpa', 'LIKE', "%{$search}%")
                      ->orWhere('upi_qr_transactions.payer_phone', 'LIKE', "%{$search}%");
                } elseif ($field === 'user') {
                    $q->where('tj_user_app.prenom', 'LIKE', "%{$search}%")
                      ->orWhere('tj_user_app.nom', 'LIKE', "%{$search}%")
                      ->orWhere('tj_conducteur.prenom', 'LIKE', "%{$search}%")
                      ->orWhere('tj_conducteur.nom', 'LIKE', "%{$search}%")
                      ->orWhere('tj_user_app.phone', 'LIKE', "%{$search}%")
                      ->orWhere('tj_conducteur.phone', 'LIKE', "%{$search}%");
                } else {
                    $q->where('upi_qr_transactions.razorpay_payment_id', 'LIKE', "%{$search}%")
                      ->orWhere('upi_qr_transactions.ac_no', 'LIKE', "%{$search}%")
                      ->orWhere('upi_qr_transactions.payer_name', 'LIKE', "%{$search}%")
                      ->orWhere('upi_qr_transactions.payer_vpa', 'LIKE', "%{$search}%")
                      ->orWhere('upi_qr_transactions.payer_phone', 'LIKE', "%{$search}%")
                      ->orWhere('tj_user_app.prenom', 'LIKE', "%{$search}%")
                      ->orWhere('tj_user_app.nom', 'LIKE', "%{$search}%")
                      ->orWhere('tj_conducteur.prenom', 'LIKE', "%{$search}%")
                      ->orWhere('tj_conducteur.nom', 'LIKE', "%{$search}%");
                }
            });
        }

        if ($request->filled('user_type') && in_array($request->input('user_type'), ['customer', 'driver'])) {
            $query->where('upi_qr_transactions.user_type', $request->input('user_type'));
        }

        if ($request->filled('status')) {
            $query->where('upi_qr_transactions.status', $request->input('status'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('upi_qr_transactions.created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('upi_qr_transactions.created_at', '<=', $request->input('date_to'));
        }

        // Summary stats (qualify with table name to prevent ambiguous column error)
        $totalVolume = (clone $query)->sum('upi_qr_transactions.amount');
        $totalTransactions = (clone $query)->count('upi_qr_transactions.id');

        $transactions = $query
            ->orderByDesc('upi_qr_transactions.created_at')
            ->paginate(20)
            ->appends($request->all());

        $currency = Currency::where('statut', 'yes')->first();
        if (!$currency) {
            $currency = (object)['symbole' => '₹', 'symbol_at_right' => 'false', 'decimal_digit' => 2];
        }

        return view('transactions.upi_payments', compact('transactions', 'currency', 'totalVolume', 'totalTransactions'));
    }

    /**
     * Manually assign an unassigned UPI transaction to a user and credit their wallet.
     */
    public function assignUpiPayment(Request $request)
    {
        $request->validate([
            'id'        => 'required|integer',
            'user_id'   => 'required|integer',
            'user_type' => 'required|in:customer,driver',
        ]);

        $txn = DB::table('upi_qr_transactions')->where('id', $request->input('id'))->first();
        if (!$txn) {
            return back()->with('error', 'Transaction not found.');
        }

        if ($txn->wallet_credited) {
            return back()->with('error', 'Transaction is already credited.');
        }

        $userId   = (int) $request->input('user_id');
        $userType = $request->input('user_type');
        $table    = ($userType === 'driver') ? 'tj_conducteur' : 'tj_user_app';
        $user     = DB::table($table)->where('id', $userId)->first();

        if (!$user) {
            return back()->with('error', 'Selected recipient user not found.');
        }

        $amountInRupees = floatval($txn->amount);
        $payerName      = $txn->payer_name ?: 'UPI Payer';
        $paymentId      = $txn->razorpay_payment_id;
        $nowDateTime    = Carbon::now('Asia/Kolkata')->format('Y-m-d H:i:s');
        $currentDate    = Carbon::now('Asia/Kolkata')->format('Y-m-d');
        $acNo           = $user->ac_no ?? $txn->ac_no;

        DB::beginTransaction();
        try {
            // 1. Increment wallet balance
            DB::table($table)->where('id', $userId)->increment('amount', $amountInRupees);

            // 2. Insert wallet transaction record
            $txnData = [
                'amount'         => (string) $amountInRupees,
                'type'           => 'credit',
                'deduction_type' => 1,
                'payment_method' => 'UPI',
                'counterparty'   => $payerName,
                'payment_status' => 'success',
                'txn_id'         => $paymentId,
                'description'    => 'UPI Payment from ' . $payerName . ($txn->payer_vpa ? ' (' . $txn->payer_vpa . ')' : '') . ' (Admin Assigned)',
                'date'           => $currentDate,
                'creer'          => $nowDateTime,
                'modifier'       => $nowDateTime,
            ];

            if ($userType === 'driver') {
                $txnData['id_conducteur'] = $userId;
                DB::table('tj_conducteur_transaction')->insert($txnData);
            } else {
                $txnData['id_user_app'] = $userId;
                $txnData['ac_no']       = $acNo;
                DB::table('tj_transaction')->insert($txnData);
            }

            // 3. Update upi_qr_transactions
            DB::table('upi_qr_transactions')->where('id', $txn->id)->update([
                'user_id'         => $userId,
                'user_type'       => $userType,
                'ac_no'           => $acNo,
                'wallet_credited' => true,
                'status'          => 'captured',
                'updated_at'      => $nowDateTime,
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Error crediting wallet: ' . $e->getMessage());
        }

        // Notify user via FCM push and persist in tj_notification
        try {
            $fcmId = $user->fcm_id ?? null;
            $formattedAmount = number_format($amountInRupees, 2);
            $msg = "Your Fiinway account has been credited with ₹{$formattedAmount} from {$payerName} via UPI.";

            if (!empty($fcmId)) {
                \App\Http\Controllers\API\v1\GcmController::sendNotification($fcmId, [
                    'title'     => 'Fiinway',
                    'body'      => $msg,
                    'sound'     => 'default',
                    'tag'       => 'wallet_topup',
                    'type'      => 'wallet',
                    'amount'    => (string) $amountInRupees,
                    'user_type' => $userType,
                ]);
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('tj_notification')) {
                DB::table('tj_notification')->insert([
                    'titre'    => 'Fiinway',
                    'message'  => $msg,
                    'statut'   => 'yes',
                    'creer'    => $nowDateTime,
                    'modifier' => $nowDateTime,
                    'to_id'    => $userId,
                    'from_id'  => 0,
                    'type'     => 'wallet_topup',
                ]);
            }
        } catch (\Throwable $e) {}

        return back()->with('success', "₹{$amountInRupees} successfully credited to {$user->prenom} {$user->nom} ({$userType})!");
    }

    /**
     * Manually credit a UPI payment directly using Razorpay Payment ID.
     */
    public function manualCreditUpi(Request $request)
    {
        $request->validate([
            'razorpay_payment_id' => 'required|string|max:100',
            'amount'              => 'required|numeric|min:0.01',
            'user_id'             => 'required|integer',
            'user_type'           => 'required|in:customer,driver',
            'payer_name'          => 'nullable|string|max:150',
            'payer_phone'         => 'nullable|string|max:50',
        ]);

        $paymentId      = trim($request->input('razorpay_payment_id'));
        $amountInRupees = round(floatval($request->input('amount')), 2);
        $userId         = (int) $request->input('user_id');
        $userType       = $request->input('user_type');
        $payerName      = trim($request->input('payer_name')) ?: 'UPI Payer';
        $payerPhone     = trim($request->input('payer_phone')) ?: null;

        // Check if already exists and credited
        $existing = DB::table('upi_qr_transactions')->where('razorpay_payment_id', $paymentId)->first();
        if ($existing && $existing->wallet_credited) {
            return back()->with('error', "Payment ID {$paymentId} has already been credited to user ID {$existing->user_id}.");
        }

        $table = ($userType === 'driver') ? 'tj_conducteur' : 'tj_user_app';
        $user  = DB::table($table)->where('id', $userId)->first();
        if (!$user) {
            return back()->with('error', 'Selected user not found.');
        }

        $nowDateTime = Carbon::now('Asia/Kolkata')->format('Y-m-d H:i:s');
        $currentDate = Carbon::now('Asia/Kolkata')->format('Y-m-d');
        $acNo        = $user->ac_no ?? null;

        DB::beginTransaction();
        try {
            DB::table($table)->where('id', $userId)->increment('amount', $amountInRupees);

            $txnData = [
                'amount'         => (string) $amountInRupees,
                'type'           => 'credit',
                'deduction_type' => 1,
                'payment_method' => 'UPI',
                'counterparty'   => $payerName,
                'payment_status' => 'success',
                'txn_id'         => $paymentId,
                'description'    => 'Manual UPI Credit by Admin: ' . $payerName,
                'date'           => $currentDate,
                'creer'          => $nowDateTime,
                'modifier'       => $nowDateTime,
            ];

            if ($userType === 'driver') {
                $txnData['id_conducteur'] = $userId;
                DB::table('tj_conducteur_transaction')->insert($txnData);
            } else {
                $txnData['id_user_app'] = $userId;
                $txnData['ac_no']       = $acNo;
                DB::table('tj_transaction')->insert($txnData);
            }

            DB::table('upi_qr_transactions')->updateOrInsert(
                ['razorpay_payment_id' => $paymentId],
                [
                    'user_id'         => $userId,
                    'user_type'       => $userType,
                    'ac_no'           => $acNo,
                    'amount'          => $amountInRupees,
                    'payer_name'      => $payerName,
                    'payer_phone'     => $payerPhone,
                    'payment_method'  => 'UPI',
                    'status'          => 'captured',
                    'wallet_credited' => true,
                    'raw_payload'     => json_encode(['source' => 'admin_manual_credit', 'timestamp' => $nowDateTime]),
                    'updated_at'      => $nowDateTime,
                    'created_at'      => $nowDateTime,
                ]
            );

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Database error: ' . $e->getMessage());
        }

        // Notify user via FCM push and persist in tj_notification
        try {
            $fcmId = $user->fcm_id ?? null;
            $formattedAmount = number_format($amountInRupees, 2);
            $msg = "Your Fiinway account has been credited with ₹{$formattedAmount} from {$payerName} via UPI.";

            if (!empty($fcmId)) {
                \App\Http\Controllers\API\v1\GcmController::sendNotification($fcmId, [
                    'title'     => 'Fiinway',
                    'body'      => $msg,
                    'sound'     => 'default',
                    'tag'       => 'wallet_topup',
                    'type'      => 'wallet',
                    'amount'    => (string) $amountInRupees,
                    'user_type' => $userType,
                ]);
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('tj_notification')) {
                DB::table('tj_notification')->insert([
                    'titre'    => 'Fiinway',
                    'message'  => $msg,
                    'statut'   => 'yes',
                    'creer'    => $nowDateTime,
                    'modifier' => $nowDateTime,
                    'to_id'    => $userId,
                    'from_id'  => 0,
                    'type'     => 'wallet_topup',
                ]);
            }
        } catch (\Throwable $e) {}

        return back()->with('success', "₹{$amountInRupees} credited to {$user->prenom} {$user->nom} for payment {$paymentId}!");
    }

    /**
     * Resend push notification for an existing credited transaction.
     */
    public function resendUpiNotification(Request $request)
    {
        $id = $request->input('id');
        $txn = DB::table('upi_qr_transactions')->where('id', $id)->first();
        if (!$txn || !$txn->user_id) {
            return back()->with('error', 'Transaction or recipient user not found.');
        }

        $table = ($txn->user_type === 'driver') ? 'tj_conducteur' : 'tj_user_app';
        $user = DB::table($table)->where('id', $txn->user_id)->first();
        if (!$user) {
            return back()->with('error', 'User not found in database.');
        }

        $fcmId = $user->fcm_id ?? null;
        $nowDateTime = Carbon::now('Asia/Kolkata')->format('Y-m-d H:i:s');
        $amount = number_format((float)$txn->amount, 2);
        $payer = $txn->payer_name ?: 'UPI Payer';
        $msg = "Your Fiinway account has been credited with ₹{$amount} from {$payer} via UPI.";

        if (!empty($fcmId)) {
            \App\Http\Controllers\API\v1\GcmController::sendNotification($fcmId, [
                'title'     => 'Fiinway',
                'body'      => $msg,
                'sound'     => 'default',
                'tag'       => 'wallet_topup',
                'type'      => 'wallet',
                'amount'    => (string) $txn->amount,
                'user_type' => $txn->user_type,
            ]);
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('tj_notification')) {
            DB::table('tj_notification')->insert([
                'titre'    => 'Fiinway',
                'message'  => $msg,
                'statut'   => 'yes',
                'creer'    => $nowDateTime,
                'modifier' => $nowDateTime,
                'to_id'    => $user->id,
                'from_id'  => 0,
                'type'     => 'wallet_topup',
            ]);
        }

        $tokenStatus = !empty($fcmId) ? 'Push notification dispatched to device!' : 'Saved in app notifications (Note: User has not registered an FCM device token yet).';
        return back()->with('success', "Notification sent to {$user->prenom} {$user->nom}! {$tokenStatus}");
    }

    /**
     * Search users and drivers for assignment modal dropdown.
     */
    public function searchUsersForUpi(Request $request)
    {
        $q = trim($request->input('q', ''));
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $results = [];

        // Search customers
        $customers = DB::table('tj_user_app')
            ->select('id', 'prenom', 'nom', 'phone', 'ac_no', 'amount')
            ->where(function ($query) use ($q) {
                $query->where('prenom', 'LIKE', "%{$q}%")
                    ->orWhere('nom', 'LIKE', "%{$q}%")
                    ->orWhere('phone', 'LIKE', "%{$q}%")
                    ->orWhere('ac_no', 'LIKE', "%{$q}%");
            })
            ->limit(15)
            ->get();

        foreach ($customers as $c) {
            $name = trim(($c->prenom ?? '') . ' ' . ($c->nom ?? '')) ?: 'Customer #' . $c->id;
            $results[] = [
                'id'        => $c->id,
                'user_type' => 'customer',
                'name'      => $name,
                'phone'     => $c->phone,
                'ac_no'     => $c->ac_no,
                'amount'    => number_format((float)$c->amount, 2),
                'label'     => "{$name} (Customer) | Ph: {$c->phone} | A/C: {$c->ac_no} | Bal: ₹{$c->amount}",
            ];
        }

        // Search drivers
        $drivers = DB::table('tj_conducteur')
            ->select('id', 'prenom', 'nom', 'phone', 'ac_no', 'amount')
            ->where(function ($query) use ($q) {
                $query->where('prenom', 'LIKE', "%{$q}%")
                    ->orWhere('nom', 'LIKE', "%{$q}%")
                    ->orWhere('phone', 'LIKE', "%{$q}%")
                    ->orWhere('ac_no', 'LIKE', "%{$q}%");
            })
            ->limit(15)
            ->get();

        foreach ($drivers as $d) {
            $name = trim(($d->prenom ?? '') . ' ' . ($d->nom ?? '')) ?: 'Driver #' . $d->id;
            $results[] = [
                'id'        => $d->id,
                'user_type' => 'driver',
                'name'      => $name,
                'phone'     => $d->phone,
                'ac_no'     => $d->ac_no,
                'amount'    => number_format((float)$d->amount, 2),
                'label'     => "{$name} (Driver) | Ph: {$d->phone} | A/C: {$d->ac_no} | Bal: ₹{$d->amount}",
            ];
        }

        return response()->json($results);
    }

    private function applyTransactionFilters($query, Request $request, string $table): void
    {
        if ($request->filled('search') && $request->get('selected_search') === 'transaction_id') {
            $search = $request->input('search');
            $query->where(function ($inner) use ($table, $search) {
                $inner->where("{$table}.id", 'LIKE', '%' . $search . '%')
                    ->orWhere("{$table}.txn_id", 'LIKE', '%' . $search . '%')
                    ->orWhere("{$table}.ac_no", 'LIKE', '%' . $search . '%');
            });
        } elseif ($request->filled('payment_status') && $request->get('selected_search') === 'payment_status') {
            $query->where("{$table}.payment_status", 'LIKE', '%' . $request->input('payment_status') . '%');
        } elseif ($request->filled('search') && $request->get('selected_search') === 'description') {
            $query->where("{$table}.description", 'LIKE', '%' . $request->input('search') . '%');
        }
    }
}
