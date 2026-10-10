<?php

namespace App\Http\Controllers;

use App\Models\Currency;
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
                    WHEN upi_qr_transactions.user_type = 'driver' THEN CONCAT(COALESCE(tj_conducteur.prenom, ''), ' ', COALESCE(tj_conducteur.nom, ''))
                    ELSE CONCAT(COALESCE(tj_user_app.prenom, ''), ' ', COALESCE(tj_user_app.nom, ''))
                END as user_full_name"),
                DB::raw("CASE 
                    WHEN upi_qr_transactions.user_type = 'driver' THEN tj_conducteur.phone
                    ELSE tj_user_app.phone
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
                      ->orWhere('upi_qr_transactions.payer_vpa', 'LIKE', "%{$search}%");
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

        // Summary stats
        $totalVolume = (clone $query)->sum('amount');
        $totalTransactions = (clone $query)->count();

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
