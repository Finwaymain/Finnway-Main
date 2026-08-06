<?php

namespace App\Http\Controllers;

use App\Models\ConsumerPremiumPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ConsumerPlanController extends Controller
{
    // ─── List all consumer plans ───────────────────────────────────────────────
    public function index(Request $request)
    {
        if (!Schema::hasTable('consumer_premium_plans')) {
            $plans = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
            return view('consumer_plans.index', compact('plans'));
        }

        $query = ConsumerPremiumPlan::query();

        if ($request->filled('search')) {
            $query->where('name', 'LIKE', '%' . $request->search . '%');
        }

        $plans = $query->orderBy('display_order')->paginate(15);

        return view('consumer_plans.index', compact('plans'));
    }

    // ─── Show create form ─────────────────────────────────────────────────────
    public function create()
    {
        return view('consumer_plans.create');
    }

    // ─── Store new plan ───────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'price'         => 'required|numeric|min:0',
            'validity_days' => 'required|integer|min:1',
            'description'   => 'nullable|string',
        ]);

        if (Schema::hasTable('consumer_premium_plans')) {
            ConsumerPremiumPlan::create($this->buildData($request));
        }

        return redirect()->route('consumer-plans.index')
            ->with('success', 'Consumer plan created successfully.');
    }

    // ─── Show edit form ───────────────────────────────────────────────────────
    public function edit($id)
    {
        if (!Schema::hasTable('consumer_premium_plans')) {
            return redirect()->route('consumer-plans.index')->with('error', 'Database table missing.');
        }
        $plan = ConsumerPremiumPlan::findOrFail($id);
        return view('consumer_plans.edit', compact('plan'));
    }

    // ─── Update plan ──────────────────────────────────────────────────────────
    public function update(Request $request, $id)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'price'         => 'required|numeric|min:0',
            'validity_days' => 'required|integer|min:1',
            'description'   => 'nullable|string',
        ]);

        if (Schema::hasTable('consumer_premium_plans')) {
            ConsumerPremiumPlan::findOrFail($id)->update($this->buildData($request));
        }

        return redirect()->route('consumer-plans.index')
            ->with('success', 'Consumer plan updated successfully.');
    }

    // ─── Delete plan ──────────────────────────────────────────────────────────
    public function delete($id)
    {
        if (Schema::hasTable('consumer_premium_plans')) {
            ConsumerPremiumPlan::findOrFail($id)->delete();
        }
        return redirect()->back()->with('success', 'Plan deleted.');
    }

    // ─── Toggle active/inactive status ───────────────────────────────────────
    public function toggleStatus(Request $request)
    {
        if (Schema::hasTable('consumer_premium_plans')) {
            $plan = ConsumerPremiumPlan::findOrFail($request->id);
            $plan->status = ($request->ischeck === 'true') ? 'active' : 'inactive';
            $plan->save();
        }

        return response()->json(['success' => true]);
    }

    // ─── Build fillable data array from request ────────────────────────────────
    private function buildData(Request $request): array
    {
        return [
            'name'                   => $request->name,
            'price'                  => $request->price,
            'validity_days'          => $request->validity_days,
            'description'            => $request->description,
            'status'                 => $request->has('status') ? 'active' : 'inactive',
            'display_order'          => $request->display_order ?? 0,

            // Cashback
            'sender_cashback_type'   => $request->sender_cashback_type ?? 'percentage',
            'sender_cashback_value'  => $request->sender_cashback_value ?? 0,
            'receiver_cashback_type' => $request->receiver_cashback_type ?? 'percentage',
            'receiver_cashback_value'=> $request->receiver_cashback_value ?? 0,

            // Service Discounts
            'discount_cab'              => $request->discount_cab ?? 0,
            'discount_bike'             => $request->discount_bike ?? 0,
            'discount_home_service'     => $request->discount_home_service ?? 0,
            'discount_food'             => $request->discount_food ?? 0,
            'discount_travel'           => $request->discount_travel ?? 0,
            'discount_hotel'            => $request->discount_hotel ?? 0,
            'discount_healthcare'       => $request->discount_healthcare ?? 0,
            'discount_marketplace'      => $request->discount_marketplace ?? 0,
            'discount_delivery'         => $request->discount_delivery ?? 0,
            'discount_transaction'      => $request->discount_transaction ?? 0,

            // Quotas & Minimum Benefit Rules
            'free_shipping'             => $request->has('free_shipping'),
            'shipping_min_order'        => $request->shipping_min_order ?? 0,
            'free_shipping_count'       => $request->free_shipping_count ?? 0,
            'free_ride_limit'           => $request->free_ride_limit ?? 0,
            'quota_hotel_booking'       => $request->quota_hotel_booking ?? 0,
            'quota_home_service'        => $request->quota_home_service ?? 0,
            'quota_shopping'            => $request->quota_shopping ?? 0,
            'quota_food'                => $request->quota_food ?? 0,
            'quota_medical'             => $request->quota_medical ?? 0,
            'min_order_amount_benefit'  => $request->min_order_amount_benefit ?? 0,
            'wallet_monthly_bonus'      => $request->wallet_monthly_bonus ?? 0,
            'annual_voucher_value'      => $request->annual_voucher_value ?? 0,

            // Loan & Virtual Credit Eligibility
            'loan_enabled'              => $request->has('loan_enabled'),
            'loan_max_amount'           => $request->loan_max_amount ?? 0,
            'loan_personal'             => $request->has('loan_personal'),
            'loan_business'             => $request->has('loan_business'),
            'loan_credit_card'          => $request->has('loan_credit_card'),
            'loan_interest_free'        => $request->has('loan_interest_free'),
            'loan_virtual'              => $request->has('loan_virtual'),
            'virtual_credit_limit'      => $request->virtual_credit_limit ?? 15000,
        ];
    }
}
