<?php

namespace App\Http\Controllers\Admin\Food;

use App\Http\Controllers\Controller;
use App\Models\Food\FoodChargeRule;
use App\Models\Food\FoodCommissionRule;
use App\Models\Food\FoodDeliveryChargeRule;
use App\Models\Food\FoodDispute;
use App\Models\Food\FoodMarkupRule;
use App\Models\Food\FoodOrder;
use App\Models\Food\FoodOrderItem;
use App\Models\Food\FoodPremiumDeal;
use App\Models\Food\FoodProduct;
use App\Models\Food\FoodRestaurant;
use App\Models\Food\FoodRestaurantType;
use App\Models\Food\FoodSetting;
use App\Models\Food\FoodSettlement;
use App\Services\Food\FoodPricingEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FoodAdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.food.dashboard', [
            'liveOrders' => FoodOrder::whereNotIn('order_status', ['delivered', 'completed', 'cancelled', 'rejected'])->count(),
            'activeRestaurants' => FoodRestaurant::where('onboarding_status', 'active')->where('operational_status', 'open')->count(),
            'pendingApprovals' => FoodRestaurant::where('onboarding_status', 'pending_approval')->count(),
            'openDisputes' => FoodDispute::whereIn('status', ['open', 'under_review', 'in_progress'])->count(),
            'todayOrders' => FoodOrder::whereDate('created_at', now()->toDateString())->count(),
            'todaySales' => FoodOrder::whereDate('created_at', now()->toDateString())->sum('customer_payable'),
        ]);
    }

    public function types()
    {
        $types = FoodRestaurantType::orderBy('sort_order')->get();
        return view('admin.food.types', compact('types'));
    }

    public function saveType(Request $request, $id = null)
    {
        $type = $id ? FoodRestaurantType::findOrFail($id) : new FoodRestaurantType();
        $type->fill($request->only([
            'code', 'name', 'description', 'is_active', 'onboarding_fee', 'approval_mode', 'sort_order',
        ]));
        $type->is_active = $request->boolean('is_active');
        $type->save();
        return redirect()->route('admin.food.types')->with('success', 'Restaurant type saved.');
    }

    public function restaurants(Request $request)
    {
        $q = FoodRestaurant::with('owner', 'type')->orderByDesc('id');
        if ($request->filled('status')) {
            $q->where('onboarding_status', $request->status);
        }
        if ($request->filled('q')) {
            $term = $request->q;
            $q->where(function ($qq) use ($term) {
                $qq->where('name', 'like', "%$term%")
                    ->orWhere('owner_phone', 'like', "%$term%")
                    ->orWhere('city', 'like', "%$term%");
            });
        }
        $restaurants = $q->paginate(30);
        return view('admin.food.restaurants', compact('restaurants'));
    }

    public function restaurantShow($id)
    {
        $restaurant = FoodRestaurant::with('owner', 'type', 'products', 'categories')->findOrFail($id);
        return view('admin.food.restaurant_show', compact('restaurant'));
    }

    public function approve($id)
    {
        $restaurant = FoodRestaurant::findOrFail($id);
        $restaurant->onboarding_status = 'active';
        $restaurant->operational_status = 'closed';
        $restaurant->approved_at = now();
        $restaurant->approved_by = auth()->id();
        $restaurant->rejection_reason = null;
        $restaurant->save();
        return back()->with('success', 'Restaurant approved.');
    }

    public function reject(Request $request, $id)
    {
        $restaurant = FoodRestaurant::findOrFail($id);
        $restaurant->onboarding_status = 'rejected';
        $restaurant->rejection_reason = $request->get('reason', 'Rejected by admin');
        $restaurant->save();
        return back()->with('success', 'Restaurant rejected.');
    }

    public function suspend($id)
    {
        $restaurant = FoodRestaurant::findOrFail($id);
        $restaurant->onboarding_status = 'suspended';
        $restaurant->operational_status = 'closed';
        $restaurant->save();
        return back()->with('success', 'Restaurant suspended.');
    }

    public function commissions()
    {
        $rules = FoodCommissionRule::orderByDesc('id')->get();
        $markups = FoodMarkupRule::orderByDesc('id')->get();
        $deals = FoodPremiumDeal::with('restaurant')->orderByDesc('id')->get();
        return view('admin.food.commissions', compact('rules', 'markups', 'deals'));
    }

    public function saveCommission(Request $request, $id = null)
    {
        $rule = $id ? FoodCommissionRule::findOrFail($id) : new FoodCommissionRule();
        $rule->fill($request->only([
            'scope', 'restaurant_type_id', 'restaurant_id', 'category_id', 'product_id',
            'rule_type', 'rule_value', 'starts_at', 'ends_at',
        ]));
        $rule->is_active = $request->boolean('is_active', true);
        $rule->save();
        return back()->with('success', 'Commission rule saved.');
    }

    public function saveMarkup(Request $request, $id = null)
    {
        $rule = $id ? FoodMarkupRule::findOrFail($id) : new FoodMarkupRule();
        $rule->fill($request->only([
            'scope', 'restaurant_id', 'category_id', 'product_id', 'rule_type', 'rule_value', 'starts_at', 'ends_at',
        ]));
        $rule->is_active = $request->boolean('is_active', true);
        $rule->save();
        return back()->with('success', 'Markup rule saved.');
    }

    public function charges()
    {
        $charges = FoodChargeRule::orderByDesc('id')->get();
        $delivery = FoodDeliveryChargeRule::orderByDesc('id')->get();
        return view('admin.food.charges', compact('charges', 'delivery'));
    }

    public function saveCharge(Request $request, $id = null)
    {
        $rule = $id ? FoodChargeRule::findOrFail($id) : new FoodChargeRule();
        $rule->fill($request->only([
            'name', 'code', 'charge_type', 'charge_value', 'min_amount', 'max_amount',
            'order_min', 'order_max', 'time_from', 'time_to', 'starts_at', 'ends_at',
        ]));
        if ($request->filled('slab_json')) {
            $rule->slab_json = json_decode($request->slab_json, true);
        }
        $rule->is_active = $request->boolean('is_active', true);
        $rule->save();
        return back()->with('success', 'Charge saved.');
    }

    public function saveDeliveryCharge(Request $request, $id = null)
    {
        $rule = $id ? FoodDeliveryChargeRule::findOrFail($id) : new FoodDeliveryChargeRule();
        $rule->fill($request->only([
            'name', 'base_charge', 'free_radius_km', 'base_radius_km', 'per_km_charge',
            'max_distance_km', 'above_max_per_km',
        ]));
        $rule->allow_above_max = $request->boolean('allow_above_max');
        $rule->is_active = $request->boolean('is_active', true);
        if ($request->filled('distance_slabs')) {
            $rule->distance_slabs = json_decode($request->distance_slabs, true);
        }
        $rule->save();
        return back()->with('success', 'Delivery charge saved.');
    }

    public function settings()
    {
        $settings = FoodSetting::orderBy('key')->get()->pluck('value', 'key');
        return view('admin.food.settings', compact('settings'));
    }

    public function saveSettings(Request $request)
    {
        foreach ($request->except('_token') as $key => $value) {
            FoodSetting::setValue($key, $value);
        }
        return back()->with('success', 'Settings updated.');
    }

    public function orders(Request $request)
    {
        $q = FoodOrder::with('restaurant')->orderByDesc('id');
        if ($request->filled('status')) {
            $q->where('order_status', $request->status);
        }
        $orders = $q->paginate(40);
        return view('admin.food.orders', compact('orders'));
    }

    public function live()
    {
        $orders = FoodOrder::with('restaurant')
            ->whereNotIn('order_status', ['delivered', 'completed', 'cancelled', 'rejected'])
            ->orderByDesc('id')
            ->limit(100)
            ->get();
        $restaurants = FoodRestaurant::where('onboarding_status', 'active')
            ->where('operational_status', 'open')
            ->get(['id', 'name', 'latitude', 'longitude', 'city', 'operational_status']);
        return view('admin.food.live', compact('orders', 'restaurants'));
    }

    public function createTestOrder(Request $request)
    {
        $restaurant = FoodRestaurant::where('onboarding_status', 'active')->findOrFail($request->get('restaurant_id'));
        $product = FoodProduct::where('restaurant_id', $restaurant->id)->where('is_active', true)->first();
        if (!$product) {
            return back()->with('error', 'Restaurant has no active products. Add menu first.');
        }
        $engine = new FoodPricingEngine();
        $price = $engine->customerUnitPrice($product, $restaurant);
        $qty = (int) $request->get('quantity', 1);
        $foodAmount = $price['base_price'] * $qty;
        $markup = $price['markup'] * $qty;
        $subtotal = $price['customer_price'] * $qty;
        $commission = $engine->resolveCommission($restaurant, $foodAmount);
        $platform = $engine->calculatePlatformCharges($subtotal);
        $delivery = $engine->calculateDeliveryCharge(3);

        $order = FoodOrder::create([
            'order_number' => 'TEST-FOOD-' . time(),
            'restaurant_id' => $restaurant->id,
            'customer_id' => 0,
            'customer_name' => $request->get('customer_name', 'Test Customer'),
            'customer_phone' => $request->get('customer_phone', '9999999999'),
            'delivery_address' => $request->get('delivery_address', 'Test Address, Fiinway City'),
            'delivery_lat' => $restaurant->latitude,
            'delivery_lng' => $restaurant->longitude,
            'distance_km' => 3,
            'food_amount' => $foodAmount,
            'markup_amount' => $markup,
            'food_subtotal' => $subtotal,
            'platform_charges' => $platform['total'],
            'delivery_charge' => $delivery['amount'],
            'customer_payable' => $subtotal + $platform['total'] + $delivery['amount'],
            'commission_amount' => $commission['amount'],
            'restaurant_net_amount' => $foodAmount - $commission['amount'],
            'company_due_amount' => $commission['amount'] + $markup + $platform['total'],
            'payment_method' => 'cod',
            'payment_status' => 'cash_pending',
            'order_status' => 'pending',
            'settlement_status' => 'pending',
            'delivery_otp' => (string) random_int(1000, 9999),
            'charges_breakdown' => $platform['breakdown'],
            'is_test' => true,
        ]);
        FoodOrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => $qty,
            'restaurant_unit_price' => $price['base_price'],
            'markup_unit' => $price['markup'],
            'customer_unit_price' => $price['customer_price'],
            'addons_total' => 0,
            'line_total' => $subtotal,
        ]);
        return redirect()->route('admin.food.orders')->with('success', 'Test order #' . $order->order_number . ' created.');
    }

    public function settlements()
    {
        $settlements = FoodSettlement::orderByDesc('id')->paginate(30);
        return view('admin.food.settlements', compact('settlements'));
    }

    public function disputes()
    {
        $disputes = FoodDispute::orderByDesc('id')->paginate(30);
        return view('admin.food.disputes', compact('disputes'));
    }

    public function resolveDispute(Request $request, $id)
    {
        $dispute = FoodDispute::findOrFail($id);
        $dispute->status = $request->get('status', 'resolved');
        $dispute->resolution = $request->get('resolution');
        $dispute->refund_amount = $request->get('refund_amount');
        $dispute->penalty_amount = $request->get('penalty_amount');
        $dispute->save();
        return back()->with('success', 'Dispute updated.');
    }
}
