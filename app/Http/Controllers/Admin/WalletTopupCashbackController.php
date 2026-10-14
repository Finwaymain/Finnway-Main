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

        return view('admin.wallet.topup_cashback', compact('rules', 'stats', 'logs'));
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

        return redirect()->back()->with('success', 'Wallet Top-Up Cashback rule created successfully.');
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

        return redirect()->back()->with('success', "Cashback rule '{$rule->name}' updated successfully.");
    }

    /**
     * Toggle active/inactive status of a rule.
     */
    public function toggleStatus($id)
    {
        $rule = WalletTopupCashbackRule::findOrFail($id);
        $rule->is_active = !$rule->is_active;
        $rule->save();

        return response()->json([
            'success'   => true,
            'is_active' => $rule->is_active,
            'message'   => "Rule status updated to " . ($rule->is_active ? 'Active' : 'Inactive'),
        ]);
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
