<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WalletTopupCashbackRule;
use App\Models\WalletTopupCashbackLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletTopupCashbackController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display listing of cashback rules and overall statistics.
     */
    public function index(Request $request)
    {
        // Auto-reconcile recent top-ups from last 48 hours that may have missed cashback
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('upi_qr_transactions')) {
                $recentCaptured = DB::table('upi_qr_transactions')
                    ->where('wallet_credited', true)
                    ->where('amount', '>', 0)
                    ->whereNotNull('user_id')
                    ->where('updated_at', '>=', \Carbon\Carbon::now()->subDays(2))
                    ->get();

                foreach ($recentCaptured as $rc) {
                    $uid = (int) $rc->user_id;
                    $uType = ($rc->user_type === 'driver') ? 'driver' : 'customer';
                    $amt = (float) $rc->amount;
                    $txnRef = (string) ($rc->razorpay_payment_id ?? $rc->id);

                    $alreadyLogged = WalletTopupCashbackLog::where('user_id', $uid)
                        ->where('user_type', $uType)
                        ->where(function($q) use ($txnRef) {
                            $q->where('txn_id', $txnRef)
                              ->orWhere('notes', 'LIKE', "%{$txnRef}%");
                        })
                        ->exists();

                    if (!$alreadyLogged && $amt >= 10) {
                        \App\Services\WalletTopupCashbackService::processTopupCashback($uid, $uType, $amt, $txnRef);
                    }
                }
            }
        } catch (\Throwable $reconcileErr) {
            \Illuminate\Support\Facades\Log::warning('Cashback auto-reconcile error: ' . $reconcileErr->getMessage());
        }

        $rules = WalletTopupCashbackRule::orderBy('priority', 'desc')->orderBy('id', 'desc')->get();

        $stats = [
            'total_cashback_given' => (float) WalletTopupCashbackLog::sum('cashback_amount'),
            'total_transactions'   => WalletTopupCashbackLog::count(),
            'unique_users_count'   => WalletTopupCashbackLog::distinct('user_id')->count('user_id'),
            'active_rules_count'   => WalletTopupCashbackRule::where('is_active', true)->count(),
        ];

        $logs = WalletTopupCashbackLog::with('rule')
            ->orderBy('id', 'desc')
            ->limit(50)
            ->get();

        $customerIds = $logs->where('user_type', 'customer')->pluck('user_id')->unique()->filter()->values()->all();
        $driverIds   = $logs->where('user_type', 'driver')->pluck('user_id')->unique()->filter()->values()->all();

        $customers = !empty($customerIds) ? DB::table('tj_user_app')->whereIn('id', $customerIds)->select('id', 'prenom', 'nom', 'phone', 'ac_no')->get()->keyBy('id') : collect();
        $drivers   = !empty($driverIds) ? DB::table('tj_conducteur')->whereIn('id', $driverIds)->select('id', 'prenom', 'nom', 'phone', 'ac_no')->get()->keyBy('id') : collect();

        foreach ($logs as $log) {
            if ($log->user_type === 'driver') {
                $d = $drivers->get($log->user_id);
                $log->user_name  = $d ? trim(($d->prenom ?? '') . ' ' . ($d->nom ?? '')) : 'Driver #' . $log->user_id;
                $log->user_phone = $d->phone ?? null;
                $log->user_ac_no = $d->ac_no ?? null;
                $log->user_url   = route('driver.show', ['id' => $log->user_id]);
            } else {
                $c = $customers->get($log->user_id);
                $log->user_name  = $c ? trim(($c->prenom ?? '') . ' ' . ($c->nom ?? '')) : 'User #' . $log->user_id;
                $log->user_phone = $c->phone ?? null;
                $log->user_ac_no = $c->ac_no ?? null;
                $log->user_url   = route('users.show', ['id' => $log->user_id]);
            }
        }

        return view('admin.wallet.topup_cashback', compact('rules', 'stats', 'logs'));
    }

    /**
     * Show dedicated standalone page for creating a new cashback rule.
     */
    public function create()
    {
        return view('admin.wallet.topup_cashback_create');
    }

    /**
     * Store a newly created cashback rule.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'                => 'required|string|max:255',
            'cashback_type'       => 'required|in:percentage,flat',
            'cashback_value'      => 'required|numeric|min:0.01',
            'min_topup_amount'    => 'required|numeric|min:0',
            'max_topup_amount'    => 'nullable|numeric|gte:min_topup_amount',
            'max_cashback_amount' => 'nullable|numeric|min:0',
            'target_user_type'    => 'required|in:all,customer,driver',
            'usage_limit_per_user'=> 'nullable|integer|min:1',
            'priority'            => 'nullable|integer',
            'start_date'          => 'nullable|date',
            'end_date'            => 'nullable|date|after_or_equal:start_date',
        ]);

        WalletTopupCashbackRule::create([
            'name'                 => $request->name,
            'cashback_type'        => $request->cashback_type,
            'cashback_value'       => $request->cashback_value,
            'min_topup_amount'     => $request->min_topup_amount ?? 0,
            'max_topup_amount'     => $request->filled('max_topup_amount') ? $request->max_topup_amount : null,
            'max_cashback_amount'  => $request->filled('max_cashback_amount') ? $request->max_cashback_amount : null,
            'target_user_type'     => $request->target_user_type ?? 'all',
            'usage_limit_per_user' => $request->filled('usage_limit_per_user') ? $request->usage_limit_per_user : null,
            'priority'             => $request->priority ?? 0,
            'is_active'            => $request->has('is_active') ? true : false,
            'start_date'           => $request->start_date ? $request->start_date : null,
            'end_date'             => $request->end_date ? $request->end_date : null,
        ]);

        return redirect()->route('admin.wallet.topup-cashback.index')->with('success', 'Wallet Top-Up Cashback rule created successfully.');
    }

    /**
     * Show dedicated standalone page for editing an existing cashback rule.
     */
    public function edit($id)
    {
        $rule = WalletTopupCashbackRule::findOrFail($id);
        return view('admin.wallet.topup_cashback_edit', compact('rule'));
    }

    /**
     * Update an existing cashback rule.
     */
    public function update(Request $request, $id)
    {
        $rule = WalletTopupCashbackRule::findOrFail($id);

        $request->validate([
            'name'                => 'required|string|max:255',
            'cashback_type'       => 'required|in:percentage,flat',
            'cashback_value'      => 'required|numeric|min:0.01',
            'min_topup_amount'    => 'required|numeric|min:0',
            'max_topup_amount'    => 'nullable|numeric|gte:min_topup_amount',
            'max_cashback_amount' => 'nullable|numeric|min:0',
            'target_user_type'    => 'required|in:all,customer,driver',
            'usage_limit_per_user'=> 'nullable|integer|min:1',
            'priority'            => 'nullable|integer',
            'start_date'          => 'nullable|date',
            'end_date'            => 'nullable|date|after_or_equal:start_date',
        ]);

        $rule->update([
            'name'                 => $request->name,
            'cashback_type'        => $request->cashback_type,
            'cashback_value'       => $request->cashback_value,
            'min_topup_amount'     => $request->min_topup_amount ?? 0,
            'max_topup_amount'     => $request->filled('max_topup_amount') ? $request->max_topup_amount : null,
            'max_cashback_amount'  => $request->filled('max_cashback_amount') ? $request->max_cashback_amount : null,
            'target_user_type'     => $request->target_user_type ?? 'all',
            'usage_limit_per_user' => $request->filled('usage_limit_per_user') ? $request->usage_limit_per_user : null,
            'priority'             => $request->priority ?? 0,
            'is_active'            => $request->has('is_active') ? true : false,
            'start_date'           => $request->start_date ? $request->start_date : null,
            'end_date'             => $request->end_date ? $request->end_date : null,
        ]);

        return redirect()->route('admin.wallet.topup-cashback.index')->with('success', "Cashback rule '{$rule->name}' updated successfully.");
    }

    /**
     * Toggle active/inactive status of a rule.
     */
    public function toggleStatus(Request $request, $id)
    {
        $rule = WalletTopupCashbackRule::findOrFail($id);
        $rule->is_active = !$rule->is_active;
        $rule->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'   => true,
                'is_active' => $rule->is_active,
                'message'   => "Rule status updated to " . ($rule->is_active ? 'Active' : 'Disabled'),
            ]);
        }

        return redirect()->back()->with('success', "Rule '{$rule->name}' status changed to " . ($rule->is_active ? 'Active' : 'Disabled'));
    }

    /**
     * Delete a rule.
     */
    public function destroy($id)
    {
        $rule = WalletTopupCashbackRule::findOrFail($id);
        $name = $rule->name;
        $rule->delete();

        return redirect()->back()->with('success', "Cashback rule '{$name}' deleted successfully.");
    }
}
