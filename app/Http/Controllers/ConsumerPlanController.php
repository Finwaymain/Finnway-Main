<?php

namespace App\Http\Controllers;

use App\Models\ConsumerPremiumPlan;
use Illuminate\Http\Request;

class ConsumerPlanController extends Controller
{
    // ─── List all consumer plans ───────────────────────────────────────────────
    public function index(Request $request)
    {
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

        ConsumerPremiumPlan::create($this->buildData($request));

        return redirect()->route('consumer-plans.index')
            ->with('success', 'Consumer plan created successfully.');
    }

    // ─── Show edit form ───────────────────────────────────────────────────────
    public function edit($id)
    {
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

        ConsumerPremiumPlan::findOrFail($id)->update($this->buildData($request));

        return redirect()->route('consumer-plans.index')
            ->with('success', 'Consumer plan updated successfully.');
    }

    // ─── Delete plan ──────────────────────────────────────────────────────────
    public function delete($id)
    {
        ConsumerPremiumPlan::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Plan deleted.');
    }

    // ─── Toggle active/inactive status ───────────────────────────────────────
    public function toggleStatus(Request $request)
    {
        $plan = ConsumerPremiumPlan::findOrFail($request->id);
        $plan->status = ($request->ischeck === 'true') ? 'active' : 'inactive';
        $plan->save();

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
            'discount_cab'           => $request->discount_cab ?? 0,
            'discount_bike'          => $request->discount_bike ?? 0,
            'discount_home_service'  => $request->discount_home_service ?? 0,
            'discount_food'          => $request->discount_food ?? 0,
            'discount_travel'        => $request->discount_travel ?? 0,
            'discount_hotel'         => $request->discount_hotel ?? 0,
            'discount_healthcare'    => $request->discount_healthcare ?? 0,
            'discount_marketplace'   => $request->discount_marketplace ?? 0,

            // Shipping
            'free_shipping'          => $request->has('free_shipping'),
            'shipping_min_order'     => $request->shipping_min_order ?? 0,

            // Loan Eligibility
            'loan_personal'          => $request->has('loan_personal'),
            'loan_business'          => $request->has('loan_business'),
            'loan_credit_card'       => $request->has('loan_credit_card'),
            'loan_interest_free'     => $request->has('loan_interest_free'),
            'loan_virtual'           => $request->has('loan_virtual'),
            'virtual_credit_limit'   => $request->virtual_credit_limit ?? 15000,
        ];
    }
}
