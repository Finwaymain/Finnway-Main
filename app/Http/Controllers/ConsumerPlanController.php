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
        $f = function($val) {
            return (is_numeric($val) && $val !== '') ? floatval($val) : 0.0;
        };

        return [
            'name'                   => $request->name,
            'price'                  => $f($request->price),
            'validity_days'          => intval($request->validity_days ?? 365),
            'description'            => $request->description,
            'status'                 => $request->has('status') ? 'active' : 'inactive',
            'display_order'          => intval($request->display_order ?? 0),

            // Cashback
            'sender_cashback_type'   => $request->sender_cashback_type ?? 'percentage',
            'sender_cashback_value'  => $f($request->sender_cashback_value),
            'receiver_cashback_type' => $request->receiver_cashback_type ?? 'percentage',
            'receiver_cashback_value'=> $f($request->receiver_cashback_value),

            // Service Discounts
            'discount_cab'              => $f($request->discount_cab),
            'discount_bike'             => $f($request->discount_bike),
            'discount_home_service'     => $f($request->discount_home_service),
            'discount_food'             => $f($request->discount_food),
            'discount_travel'           => $f($request->discount_travel),
            'discount_hotel'            => $f($request->discount_hotel),
            'discount_healthcare'       => $f($request->discount_healthcare),
            'discount_marketplace'      => $f($request->discount_marketplace),
            'discount_delivery'         => $f($request->discount_delivery),
            'discount_transaction'      => $f($request->discount_transaction),

            // Quotas & Minimum Benefit Rules
            'free_shipping'             => $request->has('free_shipping'),
            'shipping_min_order'        => $f($request->shipping_min_order),
            'free_shipping_count'       => intval($request->free_shipping_count ?? 0),
            'free_ride_limit'           => intval($request->free_ride_limit ?? 0),
            'quota_hotel_booking'       => intval($request->quota_hotel_booking ?? 0),
            'quota_home_service'        => intval($request->quota_home_service ?? 0),
            'quota_shopping'            => intval($request->quota_shopping ?? 0),
            'quota_food'                => intval($request->quota_food ?? 0),
            'quota_medical'             => intval($request->quota_medical ?? 0),
            'quota_travel'              => intval($request->quota_travel ?? 0),
            'min_order_amount_benefit'  => $f($request->min_order_amount_benefit),
            'wallet_monthly_bonus'      => $f($request->wallet_monthly_bonus),
            'annual_voucher_value'      => $f($request->annual_voucher_value),

            // Per-Service Minimum Booking Amount for Benefit
            'min_amount_hotel'          => $f($request->min_amount_hotel),
            'min_amount_home_service'   => $f($request->min_amount_home_service),
            'min_amount_shopping'       => $f($request->min_amount_shopping),
            'min_amount_food'           => $f($request->min_amount_food),
            'min_amount_travel'         => $f($request->min_amount_travel),
            'min_amount_medical'        => $f($request->min_amount_medical),
            'min_amount_cab'            => $f($request->min_amount_cab),

            // Per-Service Delivery Discounts
            'discount_delivery_food'         => $f($request->discount_delivery_food),
            'discount_delivery_shopping'     => $f($request->discount_delivery_shopping),
            'discount_delivery_home_service' => $f($request->discount_delivery_home_service),
            'discount_delivery_medical'      => $f($request->discount_delivery_medical),
            'discount_delivery_parcel'       => $f($request->discount_delivery_parcel),

            // Loan & Virtual Credit Eligibility
            'loan_enabled'              => $request->has('loan_enabled'),
            'loan_max_amount'           => $f($request->loan_max_amount),
            'loan_personal'             => $request->has('loan_personal'),
            'loan_business'             => $request->has('loan_business'),
            'loan_credit_card'          => $request->has('loan_credit_card'),
            'loan_interest_free'        => $request->has('loan_interest_free'),
            'loan_virtual'              => $request->has('loan_virtual'),
            'virtual_credit_limit'      => $f($request->virtual_credit_limit ?? 15000),
        ];
    }
}
