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
                'bonus_with_code'                   => 300.00,
                'uses_with_code'                    => 6,
                'discount_per_service_with_code'    => 50.00,
                'expiry_days_with_code'             => 30,
                'custom_expiry_date_with_code'      => null,
                'min_bill_with_code'                => 50.00,
                'max_bill_with_code'                => 100000.00,
                'applicable_roles_with_code'        => 'all',

                'bonus_without_code'                => 150.00,
                'uses_without_code'                 => 3,
                'discount_per_service_without_code' => 50.00,
                'expiry_days_without_code'          => 30,
                'custom_expiry_date_without_code'   => null,
                'min_bill_without_code'             => 50.00,
                'max_bill_without_code'             => 100000.00,
                'applicable_roles_without_code'     => 'all',

                // Legacy fallback columns
                'discount_per_service'              => 50.00,
                'expiry_days'                       => 30,
                'min_bill_amount'                   => 50.00,
                'max_bill_amount'                   => 100000.00,
                'status'                            => 'active',
                'applicable_roles'                  => 'all',
                'created_at'                        => now(),
                'updated_at'                        => now(),
            ]);
            $config = DB::table('promotional_configs')->first();
        }

        // Fill any null tier-specific fields with sensible defaults or legacy values
        if (!isset($config->discount_per_service_with_code)) {
            $config->discount_per_service_with_code = $config->discount_per_service ?? 50.00;
        }
        if (!isset($config->expiry_days_with_code)) {
            $config->expiry_days_with_code = $config->expiry_days ?? 30;
        }
        if (!isset($config->custom_expiry_date_with_code)) {
            $config->custom_expiry_date_with_code = $config->custom_expiry_date ?? null;
        }
        if (!isset($config->min_bill_with_code)) {
            $config->min_bill_with_code = $config->min_bill_amount ?? 50.00;
        }
        if (!isset($config->max_bill_with_code)) {
            $config->max_bill_with_code = $config->max_bill_amount ?? 100000.00;
        }
        if (!isset($config->applicable_roles_with_code)) {
            $config->applicable_roles_with_code = $config->applicable_roles ?? 'all';
        }

        if (!isset($config->discount_per_service_without_code)) {
            $config->discount_per_service_without_code = $config->discount_per_service ?? 50.00;
        }
        if (!isset($config->expiry_days_without_code)) {
            $config->expiry_days_without_code = $config->expiry_days ?? 30;
        }
        if (!isset($config->custom_expiry_date_without_code)) {
            $config->custom_expiry_date_without_code = $config->custom_expiry_date ?? null;
        }
        if (!isset($config->min_bill_without_code)) {
            $config->min_bill_without_code = $config->min_bill_amount ?? 50.00;
        }
        if (!isset($config->max_bill_without_code)) {
            $config->max_bill_without_code = $config->max_bill_amount ?? 100000.00;
        }
        if (!isset($config->applicable_roles_without_code)) {
            $config->applicable_roles_without_code = $config->applicable_roles ?? 'all';
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

        // 4. Promotional Users with Profile Info
        $users = DB::table('user_promotions')
            ->orderBy('id', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($promo) {
                if ($promo->user_type === 'driver') {
                    $u = DB::table('tj_conducteur')->where('id', $promo->user_id)->select('prenom', 'nom', 'phone', 'email', 'ac_no')->first();
                } else {
                    $u = DB::table('tj_user_app')->where('id', $promo->user_id)->select('prenom', 'nom', 'phone', 'email', 'ac_no')->first();
                }
                $promo->user_name = ($u && (!empty($u->prenom) || !empty($u->nom))) ? trim(($u->prenom ?? '') . ' ' . ($u->nom ?? '')) : 'User #' . $promo->user_id;
                $promo->user_phone = $u->phone ?? '—';
                $promo->user_email = $u->email ?? '—';
                $promo->ac_no = $u->ac_no ?? '—';
                return $promo;
            });

        return view('promotional.index', compact('config', 'stats', 'logs', 'users'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            // Tier 1: With Referral Code
            'bonus_with_code'                   => 'required|numeric|min:0',
            'uses_with_code'                    => 'required|integer|min:1',
            'discount_per_service_with_code'    => 'required|numeric|min:1',
            'expiry_days_with_code'             => 'required|integer|min:1',
            'custom_expiry_date_with_code'      => 'nullable|date',
            'min_bill_with_code'                => 'required|numeric|min:0',
            'max_bill_with_code'                => 'required|numeric|min:0',
            'applicable_roles_with_code'        => 'required|in:all,customer,driver',

            // Tier 2: Without Referral Code (Direct Join)
            'bonus_without_code'                => 'required|numeric|min:0',
            'uses_without_code'                 => 'required|integer|min:1',
            'discount_per_service_without_code' => 'required|numeric|min:1',
            'expiry_days_without_code'          => 'required|integer|min:1',
            'custom_expiry_date_without_code'   => 'nullable|date',
            'min_bill_without_code'             => 'required|numeric|min:0',
            'max_bill_without_code'             => 'required|numeric|min:0',
            'applicable_roles_without_code'     => 'required|in:all,customer,driver',

            // System Status
            'status'                            => 'required|in:active,inactive',
        ]);

        $config = DB::table('promotional_configs')->first();
        if ($config) {
            $updateData = [
                // Tier 1
                'bonus_with_code'                   => $validated['bonus_with_code'],
                'uses_with_code'                    => $validated['uses_with_code'],
                'discount_per_service_with_code'    => $validated['discount_per_service_with_code'],
                'expiry_days_with_code'             => $validated['expiry_days_with_code'],
                'custom_expiry_date_with_code'      => $validated['custom_expiry_date_with_code'],
                'min_bill_with_code'                => $validated['min_bill_with_code'],
                'max_bill_with_code'                => $validated['max_bill_with_code'],
                'applicable_roles_with_code'        => $validated['applicable_roles_with_code'],

                // Tier 2
                'bonus_without_code'                => $validated['bonus_without_code'],
                'uses_without_code'                 => $validated['uses_without_code'],
                'discount_per_service_without_code' => $validated['discount_per_service_without_code'],
                'expiry_days_without_code'          => $validated['expiry_days_without_code'],
                'custom_expiry_date_without_code'   => $validated['custom_expiry_date_without_code'],
                'min_bill_without_code'             => $validated['min_bill_without_code'],
                'max_bill_without_code'             => $validated['max_bill_without_code'],
                'applicable_roles_without_code'     => $validated['applicable_roles_without_code'],

                // Legacy synced values
                'discount_per_service'              => $validated['discount_per_service_with_code'],
                'expiry_days'                       => $validated['expiry_days_with_code'],
                'custom_expiry_date'                => $validated['custom_expiry_date_with_code'],
                'min_bill_amount'                   => $validated['min_bill_with_code'],
                'max_bill_amount'                   => $validated['max_bill_with_code'],
                'applicable_roles'                  => $validated['applicable_roles_with_code'],
                'status'                            => $validated['status'],
                'updated_at'                        => now(),
            ];

            // Filter update array to only columns that physically exist in the table
            $existingColumns = Schema::getColumnListing('promotional_configs');
            $filteredData = array_intersect_key($updateData, array_flip($existingColumns));

            DB::table('promotional_configs')->where('id', $config->id)->update($filteredData);
        }

        return redirect()->route('promotional.index')->with('success', 'Promotional settings successfully updated!');
    }
}
