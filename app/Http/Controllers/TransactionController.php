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

        return view('transactions.driver_wallet')
            ->with('transaction', $transaction)
            ->with('currency', $currency)
            ->with('id', $id);
    }

    private function buildCustomerTransactionsQuery(Request $request, $userId = null)
    {
        $query = DB::table('tj_transaction')
            ->join('tj_user_app', 'tj_transaction.id_user_app', '=', 'tj_user_app.id')
            ->leftJoin('tj_payment_method', 'tj_payment_method.libelle', '=', 'tj_transaction.payment_method')
            ->select(
                'tj_transaction.id as transaction_id',
                DB::raw("'customer' as account_type"),
                'tj_user_app.id as userId',
                'tj_user_app.prenom as firstname',
                'tj_user_app.nom as lastname',
                'tj_transaction.amount',
                'tj_transaction.deduction_type',
                'tj_transaction.payment_method',
                'tj_payment_method.image',
                'tj_transaction.payment_status',
                'tj_transaction.creer',
                'tj_transaction.txn_id',
                'tj_transaction.description',
                'tj_transaction.type',
                'tj_transaction.ac_no'
            );

        if ($userId) {
            $query->where('tj_transaction.id_user_app', '=', $userId);
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
                'tj_conducteur_transaction.id as transaction_id',
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
                'tj_conducteur_transaction.txn_id',
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
