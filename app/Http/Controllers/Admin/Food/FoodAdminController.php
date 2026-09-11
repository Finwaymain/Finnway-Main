<?php

namespace App\Http\Controllers\Admin\Food;

use App\Http\Controllers\Controller;
use App\Models\Food\FoodCategory;
use App\Models\Food\FoodChargeRule;
use App\Models\Food\FoodCommissionRule;
use App\Models\Food\FoodDeliveryChargeRule;
use App\Models\Food\FoodDispute;
use App\Models\Food\FoodDuePayment;
use App\Models\Food\FoodMarkupRule;
use App\Models\Food\FoodOrder;
use App\Models\Food\FoodOrderItem;
use App\Models\Food\FoodPremiumDeal;
use App\Models\Food\FoodProduct;
use App\Models\Food\FoodRestaurant;
use App\Models\Food\FoodRestaurantType;
use App\Models\Food\FoodReview;
use App\Models\Food\FoodSetting;
use App\Models\Food\FoodSettlement;
use App\Services\Food\FoodPricingEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
        $restaurants = $q->paginate(25);
        $stats = [
            'total' => FoodRestaurant::count(),
            'active' => FoodRestaurant::where('onboarding_status', 'active')->count(),
            'pending' => FoodRestaurant::where('onboarding_status', 'pending_approval')->count(),
            'resubmit' => FoodRestaurant::where('onboarding_status', 'doc_resubmission_required')->count(),
            'open_now' => FoodRestaurant::where('onboarding_status', 'active')->where('operational_status', 'open')->count(),
        ];
        return view('admin.food.restaurants', compact('restaurants', 'stats'));
    }

    public function restaurantShow($id)
    {
        $restaurant = FoodRestaurant::with([
            'owner',
            'type',
            'categories.products',
            'products.category',
            'products.variants',
            'products.addons',
        ])->findOrFail($id);

        $categories = FoodCategory::where('restaurant_id', $id)->get();
        $products = FoodProduct::where('restaurant_id', $id)->with('category')->get();
        $orders = FoodOrder::where('restaurant_id', $id)->orderByDesc('id')->limit(30)->get();
        $disputes = FoodDispute::where('restaurant_id', $id)->orderByDesc('id')->limit(20)->get();
        $duePayments = FoodDuePayment::where('restaurant_id', $id)->orderByDesc('id')->get();
        $settlements = FoodSettlement::where('restaurant_id', $id)->orderByDesc('id')->limit(20)->get();
        $reviews = FoodReview::where('restaurant_id', $id)->orderByDesc('id')->limit(20)->get();
        
        $pendingDueTotal = FoodDuePayment::where('restaurant_id', $id)->where('status', 'pending')->sum('amount');
        $totalOrdersDelivered = FoodOrder::where('restaurant_id', $id)->where('order_status', 'delivered')->count();
        $totalGrossSales = FoodOrder::where('restaurant_id', $id)->where('order_status', 'delivered')->sum('food_subtotal');

        return view('admin.food.restaurant_show', compact(
            'restaurant', 'categories', 'products', 'orders', 'disputes',
            'duePayments', 'settlements', 'reviews', 'pendingDueTotal',
            'totalOrdersDelivered', 'totalGrossSales'
        ));
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
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Restaurant approved and activated.']);
        }
        return back()->with('success', 'Restaurant approved and activated.');
    }

    public function reject(Request $request, $id)
    {
        $restaurant = FoodRestaurant::findOrFail($id);
        $restaurant->onboarding_status = 'rejected';
        $restaurant->rejection_reason = $request->get('reason', 'Application rejected by administration.');
        $restaurant->save();
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Restaurant rejected.']);
        }
        return back()->with('success', 'Restaurant rejected.');
    }

    public function suspend($id)
    {
        $restaurant = FoodRestaurant::findOrFail($id);
        $restaurant->onboarding_status = 'suspended';
        $restaurant->operational_status = 'closed';
        $restaurant->save();
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Restaurant suspended.']);
        }
        return back()->with('success', 'Restaurant suspended.');
    }

    public function updateProfile(Request $request, $id)
    {
        $restaurant = FoodRestaurant::findOrFail($id);
        $fields = [
            'name', 'owner_name', 'owner_phone', 'owner_email',
            'address', 'landmark', 'city', 'state', 'pincode',
            'latitude', 'longitude', 'delivery_radius_km',
            'avg_prep_minutes', 'min_order_amount', 'max_order_amount',
            'opening_time', 'closing_time', 'fssai_number', 'gst_number', 'pan_number',
            'bank_account_name', 'bank_name', 'bank_account_number', 'bank_ifsc', 'upi_id',
        ];
        foreach ($fields as $field) {
            if ($request->has($field)) {
                $restaurant->{$field} = $request->get($field);
            }
        }
        $restaurant->pure_veg = $request->boolean('pure_veg');
        $restaurant->delivery_available = $request->boolean('delivery_available', true);
        $restaurant->takeaway_available = $request->boolean('takeaway_available');
        $restaurant->dine_in_available = $request->boolean('dine_in_available');
        if ($request->filled('custom_commission_rate')) {
            $restaurant->custom_commission_rate = (float) $request->custom_commission_rate;
        }

        $restaurant->save();
        return back()->with('success', 'Restaurant profile updated successfully.');
    }

    public function updateOperationalStatus(Request $request, $id)
    {
        $restaurant = FoodRestaurant::findOrFail($id);
        $status = $request->get('operational_status', 'closed');
        if (in_array($status, ['open', 'busy', 'closed', 'temporarily_closed'])) {
            $restaurant->operational_status = $status;
            $restaurant->save();
        }
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Operational status changed to ' . ucfirst($status) . '.']);
        }
        return back()->with('success', 'Operational status changed to ' . ucfirst($status) . '.');
    }

    public function verifyDoc(Request $request, $id)
    {
        $restaurant = FoodRestaurant::findOrFail($id);
        $docType = $request->get('doc_type', $request->get('document_type'));
        $status = $request->get('status');
        $notes = $request->get('notes', '');

        $currentDocs = $restaurant->doc_status ?: [];
        $currentDocs[$docType] = [
            'status' => $status,
            'verified_at' => now()->toDateTimeString(),
            'notes' => $notes,
        ];
        $restaurant->doc_status = $currentDocs;
        $restaurant->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => strtoupper($docType) . ' marked as ' . ucfirst($status) . '.']);
        }
        return back()->with('success', strtoupper($docType) . ' marked as ' . ucfirst($status) . '.');
    }

    public function requestDocReupload(Request $request, $id)
    {
        $restaurant = FoodRestaurant::findOrFail($id);
        $reason = $request->get('reason', 'Document resubmission required.');
        $docsNeeded = $request->get('docs_needed', $request->get('document_type', []));

        $restaurant->onboarding_status = 'doc_resubmission_required';
        $restaurant->rejection_reason = $reason;
        $restaurant->doc_notes = is_array($docsNeeded) ? implode(', ', $docsNeeded) : (string) $docsNeeded;

        $docType = $request->get('doc_type', $request->get('document_type'));
        if ($docType) {
            $currentDocs = $restaurant->doc_status ?: [];
            $currentDocs[$docType] = [
                'status' => 'reupload_requested',
                'requested_at' => now()->toDateTimeString(),
                'reason' => $reason,
            ];
            $restaurant->doc_status = $currentDocs;
        }
        $restaurant->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Document resubmission request sent to restaurant partner.']);
        }
        return back()->with('success', 'Document resubmission request sent to restaurant partner.');
    }

    public function saveProduct(Request $request, $id, $productId = null)
    {
        $restaurant = FoodRestaurant::findOrFail($id);
        $product = $productId ? FoodProduct::where('restaurant_id', $id)->findOrFail($productId) : new FoodProduct();

        $product->restaurant_id = $restaurant->id;
        $product->category_id = $request->get('category_id');
        $product->name = $request->get('name');
        $product->description = $request->get('description');
        $product->food_type = $request->get('food_type', 'veg');
        $product->restaurant_price = (float) $request->get('price', $request->get('restaurant_price', 0));
        $product->discount_price = $request->filled('discount_price') ? (float) $request->get('discount_price') : null;
        $product->prep_minutes = (int) $request->get('prep_time_minutes', $request->get('prep_minutes', 20));
        $product->availability = $request->boolean('is_available', $request->boolean('is_in_stock', true)) ? 'in_stock' : 'out_of_stock';
        $product->is_active = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            $product->image = $request->file('image')->store('food/products/' . $restaurant->id, 'public');
        }

        $product->save();
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Product ' . ($productId ? 'updated' : 'added') . ' successfully.', 'data' => $product]);
        }
        return back()->with('success', 'Product ' . ($productId ? 'updated' : 'added') . ' successfully.');
    }

    public function toggleProductStock(Request $request, $id, $productId)
    {
        $product = FoodProduct::where('restaurant_id', $id)->findOrFail($productId);
        $product->availability = $product->availability === 'in_stock' ? 'out_of_stock' : 'in_stock';
        $product->save();
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Product stock toggled to ' . ($product->availability === 'in_stock' ? 'In Stock' : 'Out of Stock') . '.']);
        }
        return back()->with('success', 'Product stock toggled to ' . ($product->availability === 'in_stock' ? 'In Stock' : 'Out of Stock') . '.');
    }

    public function deleteProduct($id, $productId)
    {
        $product = FoodProduct::where('restaurant_id', $id)->findOrFail($productId);
        $product->delete();
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Product deleted successfully.']);
        }
        return back()->with('success', 'Product deleted successfully.');
    }

    public function saveCustomCommission(Request $request, $id)
    {
        $restaurant = FoodRestaurant::findOrFail($id);
        $rate = (float) $request->get('custom_commission_rate', $request->get('commission_rate', 0));
        $restaurant->custom_commission_rate = $rate;
        $restaurant->save();

        $rule = FoodCommissionRule::firstOrNew(['restaurant_id' => $id, 'scope' => 'restaurant']);
        $rule->rule_type = $request->get('rule_type', 'percentage');
        $rule->rule_value = $rate;
        $rule->is_active = true;
        $rule->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Custom commission rule updated to ' . $rate . '%.']);
        }
        return back()->with('success', 'Custom commission rule updated to ' . $rate . '%.');
    }

    public function approveDuePayment(Request $request, $id, $paymentId)
    {
        $due = FoodDuePayment::where('restaurant_id', $id)->findOrFail($paymentId);
        $due->status = 'paid';
        $due->paid_amount = $due->amount;
        $due->paid_at = now();
        $due->payment_ref = $request->get('payment_ref', 'ADMIN-RECON-' . time());
        $due->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Due payment of ₹' . number_format($due->amount, 2) . ' approved and settled.']);
        }
        return back()->with('success', 'Due payment of ₹' . number_format($due->amount, 2) . ' approved and settled.');
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
