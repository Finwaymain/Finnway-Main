<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\PromotionalService;

class PromotionalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        // 1. Get or create master config
        $config = DB::table('promotional_configs')->first();
        if (!$config) {
            DB::table('promotional_configs')->insert([
                'bonus_with_code'      => 300.00,
                'bonus_without_code'   => 150.00,
                'discount_per_service' => 50.00,
                'uses_with_code'       => 6,
                'uses_without_code'    => 3,
                'expiry_days'          => 30,
                'min_bill_amount'      => 50.00,
                'max_bill_amount'      => 100000.00,
                'status'               => 'active',
                'applicable_roles'     => 'all',
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);
            $config = DB::table('promotional_configs')->first();
        }

        // 2. Aggregate statistics
        $stats = [
            'total_promo_users'       => DB::table('user_promotions')->count(),
            'active_promo_users'      => DB::table('user_promotions')->where('status', 'active')->count(),
            'total_bonus_granted'     => (float) DB::table('user_promotions')->sum('initial_bonus'),
            'total_bonus_remaining'   => (float) DB::table('user_promotions')->where('status', 'active')->sum('remaining_bonus'),
            'total_discount_availed'  => (float) DB::table('user_promotion_logs')->sum('discount_applied'),
            'total_uses_redeemed'     => DB::table('user_promotion_logs')->count(),
        ];

        // 3. Recent Usage Logs with user details
        $logs = DB::table('user_promotion_logs')
            ->orderBy('id', 'desc')
            ->limit(25)
            ->get();

        // 4. Recent Promotional Users
        $users = DB::table('user_promotions')
            ->orderBy('id', 'desc')
            ->limit(20)
            ->get();

        return view('promotional.index', compact('config', 'stats', 'logs', 'users'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'bonus_with_code'      => 'required|numeric|min:0',
            'bonus_without_code'   => 'required|numeric|min:0',
            'discount_per_service' => 'required|numeric|min:1',
            'uses_with_code'       => 'required|integer|min:1',
            'uses_without_code'    => 'required|integer|min:1',
            'expiry_days'          => 'required|integer|min:1',
            'custom_expiry_date'   => 'nullable|date',
            'min_bill_amount'      => 'required|numeric|min:0',
            'max_bill_amount'      => 'required|numeric|min:0',
            'status'               => 'required|in:active,inactive',
            'applicable_roles'     => 'required|in:all,customer,driver',
        ]);

        $config = DB::table('promotional_configs')->first();
        if ($config) {
            DB::table('promotional_configs')->where('id', $config->id)->update([
                'bonus_with_code'      => $validated['bonus_with_code'],
                'bonus_without_code'   => $validated['bonus_without_code'],
                'discount_per_service' => $validated['discount_per_service'],
                'uses_with_code'       => $validated['uses_with_code'],
                'uses_without_code'    => $validated['uses_without_code'],
                'expiry_days'          => $validated['expiry_days'],
                'custom_expiry_date'   => $validated['custom_expiry_date'],
                'min_bill_amount'      => $validated['min_bill_amount'],
                'max_bill_amount'      => $validated['max_bill_amount'],
                'status'               => $validated['status'],
                'applicable_roles'     => $validated['applicable_roles'],
                'updated_at'           => now(),
            ]);
        }

        return redirect()->route('promotional.index')->with('success', 'Promotional settings successfully updated!');
    }
}
