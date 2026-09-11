<?php

namespace App\Http\Controllers\API\v1\Food;

use App\Http\Controllers\Controller;
use App\Models\Food\FoodCategory;
use App\Models\Food\FoodDuePayment;
use App\Models\Food\FoodNotification;
use App\Models\Food\FoodOrder;
use App\Models\Food\FoodOrderItem;
use App\Models\Food\FoodProduct;
use App\Models\Food\FoodRestaurant;
use App\Models\Food\FoodReview;
use App\Models\Food\FoodSetting;
use App\Models\Food\FoodTransaction;
use App\Services\Food\FoodPricingEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerFoodController extends Controller
{
    public function nearby(Request $request)
    {
        $lat = (float) $request->get('latitude', $request->get('lat'));
        $lng = (float) $request->get('longitude', $request->get('lng'));
        $radius = (float) FoodSetting::getValue('nearby_restaurant_radius_km', 15);

        $restaurants = FoodRestaurant::query()
            ->where('onboarding_status', 'active')
            ->where('operational_status', 'open')
            ->where('delivery_available', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(function ($r) use ($lat, $lng) {
                $r->distance_km = ($lat && $lng)
                    ? FoodPricingEngine::haversineKm($lat, $lng, (float) $r->latitude, (float) $r->longitude)
                    : null;
                return $r;
            })
            ->filter(function ($r) use ($radius) {
                if ($r->distance_km === null) {
                    return true;
                }
                return $r->distance_km <= min($radius, (float) $r->delivery_radius_km);
            })
            ->sortBy('distance_km')
            ->values();

        return response()->json(['success' => true, 'data' => $restaurants]);
    }

    public function restaurantMenu(Request $request, $id)
    {
        $restaurant = FoodRestaurant::where('id', $id)->where('onboarding_status', 'active')->first();
        if (!$restaurant) {
            return response()->json(['success' => false, 'error' => 'Restaurant not found.']);
        }
        $engine = new FoodPricingEngine();
        $categories = FoodCategory::where('restaurant_id', $restaurant->id)->where('is_active', true)->orderBy('sort_order')->get();
        $products = FoodProduct::with(['addons', 'variants'])
            ->where('restaurant_id', $restaurant->id)
            ->where('is_active', true)
            ->where('availability', 'available')
            ->orderBy('sort_order')
            ->get()
            ->map(function ($p) use ($engine, $restaurant) {
                $price = $engine->customerUnitPrice($p, $restaurant);
                $p->customer_price = $price['customer_price'];
                $p->markup_amount = $price['markup'];
                return $p;
            });
        return response()->json([
            'success' => true,
            'data' => [
                'restaurant' => $restaurant,
                'categories' => $categories,
                'products' => $products,
            ],
        ]);
    }

    public function placeOrder(Request $request)
    {
        $restaurant = FoodRestaurant::where('id', $request->get('restaurant_id'))
            ->where('onboarding_status', 'active')
            ->where('operational_status', 'open')
            ->first();
        if (!$restaurant) {
            return response()->json(['success' => false, 'error' => 'Restaurant not available.']);
        }

        $items = $request->get('items', []);
        if (!is_array($items) || count($items) === 0) {
            return response()->json(['success' => false, 'error' => 'Cart is empty.']);
        }

        $engine = new FoodPricingEngine();
        $foodAmount = 0.0;
        $markupAmount = 0.0;
        $foodSubtotal = 0.0;
        $lineRows = [];

        foreach ($items as $item) {
            $product = FoodProduct::where('restaurant_id', $restaurant->id)
                ->where('id', $item['product_id'] ?? 0)
                ->where('is_active', true)
                ->first();
            if (!$product) {
                return response()->json(['success' => false, 'error' => 'Invalid product in cart.']);
            }
            $qty = max(1, (int) ($item['quantity'] ?? 1));
            $price = $engine->customerUnitPrice($product, $restaurant);
            $addonsTotal = 0.0;
            $addonsJson = $item['addons'] ?? [];
            if (is_array($addonsJson)) {
                foreach ($addonsJson as $ad) {
                    $addonsTotal += (float) ($ad['price'] ?? 0);
                }
            }
            $lineFood = $price['base_price'] * $qty;
            $lineMarkup = $price['markup'] * $qty;
            $lineCustomer = ($price['customer_price'] + $addonsTotal) * $qty;
            $foodAmount += $lineFood;
            $markupAmount += $lineMarkup;
            $foodSubtotal += $lineCustomer;
            $lineRows[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $qty,
                'restaurant_unit_price' => $price['base_price'],
                'markup_unit' => $price['markup'],
                'customer_unit_price' => $price['customer_price'],
                'addons_total' => $addonsTotal * $qty,
                'line_total' => $lineCustomer,
                'variant_name' => $item['variant_name'] ?? null,
                'addons_json' => $addonsJson,
                'instructions' => $item['instructions'] ?? null,
            ];
        }

        $lat = (float) $request->get('delivery_lat');
        $lng = (float) $request->get('delivery_lng');
        $distance = ($lat && $lng && $restaurant->latitude && $restaurant->longitude)
            ? FoodPricingEngine::haversineKm($lat, $lng, (float) $restaurant->latitude, (float) $restaurant->longitude)
            : (float) $request->get('distance_km', 0);
        $delivery = $engine->calculateDeliveryCharge($distance);
        if (!$delivery['available']) {
            return response()->json(['success' => false, 'error' => 'Delivery not available for this distance.']);
        }

        $platform = $engine->calculatePlatformCharges($foodSubtotal);
        $discount = (float) $request->get('discount_amount', 0);
        $deliveryCharge = $delivery['amount'];
        $customerPayable = round($foodSubtotal + $platform['total'] + $deliveryCharge - $discount, 2);
        $commission = $engine->resolveCommission($restaurant, $foodAmount);
        $restaurantNet = round($foodAmount - $commission['amount'], 2);
        $companyDue = round($commission['amount'] + $markupAmount + $platform['total'], 2);

        $paymentMethod = $request->get('payment_method', 'cod');
        $paymentStatus = $paymentMethod === 'cod' ? 'cash_pending' : 'paid';

        $order = null;
        DB::transaction(function () use (
            &$order, $restaurant, $request, $foodAmount, $markupAmount, $foodSubtotal, $discount,
            $platform, $deliveryCharge, $customerPayable, $commission, $restaurantNet, $companyDue,
            $paymentMethod, $paymentStatus, $distance, $lineRows
        ) {
            $order = FoodOrder::create([
                'order_number' => 'FIIN-FOOD-' . time() . random_int(10, 99),
                'restaurant_id' => $restaurant->id,
                'customer_id' => $request->get('customer_id'),
                'customer_name' => $request->get('customer_name'),
                'customer_phone' => $request->get('customer_phone'),
                'delivery_address' => $request->get('delivery_address'),
                'delivery_landmark' => $request->get('delivery_landmark'),
                'delivery_lat' => $request->get('delivery_lat'),
                'delivery_lng' => $request->get('delivery_lng'),
                'distance_km' => $distance,
                'special_instructions' => $request->get('special_instructions'),
                'food_amount' => $foodAmount,
                'markup_amount' => $markupAmount,
                'food_subtotal' => $foodSubtotal,
                'discount_amount' => $discount,
                'platform_charges' => $platform['total'],
                'other_charges' => 0,
                'delivery_charge' => $deliveryCharge,
                'tax_amount' => 0,
                'customer_payable' => $customerPayable,
                'commission_amount' => $commission['amount'],
                'restaurant_net_amount' => $restaurantNet,
                'company_due_amount' => $companyDue,
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentStatus,
                'order_status' => 'pending',
                'settlement_status' => 'pending',
                'delivery_otp' => (string) random_int(1000, 9999),
                'charges_breakdown' => $platform['breakdown'],
                'is_test' => (bool) $request->get('is_test', false),
            ]);

            foreach ($lineRows as $row) {
                $row['order_id'] = $order->id;
                FoodOrderItem::create($row);
            }

            FoodDuePayment::create([
                'restaurant_id' => $restaurant->id,
                'order_id' => $order->id,
                'party_type' => 'restaurant',
                'due_type' => 'commission',
                'amount' => $commission['amount'],
                'paid_amount' => 0,
                'status' => 'pending',
            ]);

            if ($paymentMethod !== 'cod') {
                FoodTransaction::create([
                    'txn_number' => 'TXN' . time() . $order->id,
                    'restaurant_id' => $restaurant->id,
                    'order_id' => $order->id,
                    'txn_type' => 'order_payment',
                    'amount' => $customerPayable,
                    'payment_method' => $paymentMethod,
                    'status' => 'paid',
                    'gateway_ref' => $request->get('payment_ref'),
                    'notes' => 'Customer food order payment',
                ]);
            }

            FoodNotification::create([
                'restaurant_id' => $restaurant->id,
                'owner_id' => $restaurant->owner_id,
                'title' => 'New Food Order',
                'message' => 'Order #' . $order->order_number . ' received. Amount ₹' . $order->customer_payable,
                'type' => 'order',
                'ref_id' => $order->id,
                'is_read' => false,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Order placed successfully.',
            'data' => $order->load('items'),
        ]);
    }

    public function track(Request $request, $id)
    {
        $order = FoodOrder::with('items', 'restaurant')->where('id', $id)->first();
        if (!$order) {
            return response()->json(['success' => false, 'error' => 'Order not found.']);
        }
        if ($request->filled('customer_id') && (int) $order->customer_id !== (int) $request->get('customer_id')) {
            return response()->json(['success' => false, 'error' => 'Unauthorized.']);
        }
        return response()->json(['success' => true, 'data' => $order]);
    }

    public function myOrders(Request $request)
    {
        $q = FoodOrder::with('items', 'restaurant')->orderByDesc('id');
        if ($request->filled('customer_id')) {
            $q->where('customer_id', $request->get('customer_id'));
        } elseif ($request->filled('customer_phone')) {
            $q->where('customer_phone', $request->get('customer_phone'));
        } else {
            return response()->json(['success' => false, 'error' => 'customer_id or customer_phone required.']);
        }
        if ($request->filled('status')) {
            $q->where('order_status', $request->get('status'));
        }
        return response()->json(['success' => true, 'data' => $q->paginate(20)]);
    }

    public function rate(Request $request, $id)
    {
        $order = FoodOrder::where('id', $id)->whereIn('order_status', ['delivered', 'completed'])->first();
        if (!$order) {
            return response()->json(['success' => false, 'error' => 'Delivered order not found.']);
        }
        $review = FoodReview::updateOrCreate(
            ['order_id' => $order->id],
            [
                'restaurant_id' => $order->restaurant_id,
                'customer_id' => $order->customer_id,
                'rider_id' => $order->rider_id,
                'restaurant_rating' => $request->get('restaurant_rating'),
                'rider_rating' => $request->get('rider_rating'),
                'restaurant_review' => $request->get('restaurant_review'),
                'rider_review' => $request->get('rider_review'),
                'restaurant_tags' => $request->get('restaurant_tags'),
                'rider_tags' => $request->get('rider_tags'),
            ]
        );
        $avg = FoodReview::where('restaurant_id', $order->restaurant_id)->whereNotNull('restaurant_rating')->avg('restaurant_rating');
        $count = FoodReview::where('restaurant_id', $order->restaurant_id)->whereNotNull('restaurant_rating')->count();
        FoodRestaurant::where('id', $order->restaurant_id)->update([
            'rating_avg' => round((float) $avg, 2),
            'rating_count' => $count,
        ]);
        return response()->json(['success' => true, 'data' => $review]);
    }

    public function reorder(Request $request, $id)
    {
        $old = FoodOrder::with('items')->where('id', $id)->first();
        if (!$old) {
            return response()->json(['success' => false, 'error' => 'Order not found.']);
        }
        $items = [];
        $unavailable = [];
        foreach ($old->items as $item) {
            $product = FoodProduct::where('id', $item->product_id)
                ->where('is_active', true)
                ->where('availability', 'available')
                ->first();
            if (!$product) {
                $unavailable[] = $item->product_name;
                continue;
            }
            $items[] = [
                'product_id' => $product->id,
                'quantity' => $item->quantity,
                'addons' => $item->addons_json,
                'variant_name' => $item->variant_name,
            ];
        }
        return response()->json([
            'success' => true,
            'data' => [
                'restaurant_id' => $old->restaurant_id,
                'items' => $items,
                'unavailable' => $unavailable,
            ],
        ]);
    }
}
