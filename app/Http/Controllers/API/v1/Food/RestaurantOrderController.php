<?php

namespace App\Http\Controllers\API\v1\Food;

use App\Http\Controllers\Controller;
use App\Models\Food\FoodDuePayment;
use App\Models\Food\FoodNotification;
use App\Models\Food\FoodOrder;
use App\Models\Food\FoodOrderItem;
use App\Models\Food\FoodProduct;
use App\Models\Food\FoodRestaurant;
use App\Models\Food\FoodTransaction;
use App\Services\Food\FoodPricingEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RestaurantOrderController extends Controller
{
    protected function restaurant(Request $request): ?FoodRestaurant
    {
        $owner = $request->attributes->get('food_owner');
        return FoodRestaurant::where('owner_id', $owner->id)->orderByDesc('id')->first();
    }

    public function dashboard(Request $request)
    {
        $restaurant = $this->restaurant($request);
        if (!$restaurant) {
            return response()->json(['success' => false, 'error' => 'Restaurant not found.']);
        }
        $today = now()->toDateString();
        $base = FoodOrder::where('restaurant_id', $restaurant->id)->whereDate('created_at', $today);
        return response()->json([
            'success' => true,
            'data' => [
                'restaurant' => $restaurant,
                'today_orders' => (clone $base)->count(),
                'pending' => (clone $base)->where('order_status', 'pending')->count(),
                'preparing' => (clone $base)->whereIn('order_status', ['restaurant_accepted', 'preparing'])->count(),
                'ready' => (clone $base)->where('order_status', 'ready_for_pickup')->count(),
                'out_for_delivery' => (clone $base)->whereIn('order_status', ['rider_assigned', 'food_picked_up', 'out_for_delivery'])->count(),
                'delivered' => (clone $base)->whereIn('order_status', ['delivered', 'completed'])->count(),
                'cancelled' => (clone $base)->whereIn('order_status', ['rejected', 'cancelled'])->count(),
                'today_sales' => (float) (clone $base)->whereIn('order_status', ['delivered', 'completed'])->sum('food_amount'),
                'today_net' => (float) (clone $base)->whereIn('order_status', ['delivered', 'completed'])->sum('restaurant_net_amount'),
                'pending_due' => (float) FoodDuePayment::where('restaurant_id', $restaurant->id)->where('status', '!=', 'paid')->sum(DB::raw('amount - paid_amount')),
            ],
        ]);
    }

    public function incoming(Request $request)
    {
        $restaurant = $this->restaurant($request);
        $orders = FoodOrder::with('items')
            ->where('restaurant_id', $restaurant->id)
            ->where('order_status', 'pending')
            ->orderByDesc('id')
            ->get();
        return response()->json(['success' => true, 'data' => $orders]);
    }

    public function list(Request $request)
    {
        $restaurant = $this->restaurant($request);
        $q = FoodOrder::with('items')->where('restaurant_id', $restaurant->id);
        if ($request->filled('status')) {
            $statuses = explode(',', $request->get('status'));
            $q->whereIn('order_status', $statuses);
        }
        if ($request->get('active') == '1') {
            $q->whereIn('order_status', [
                'pending', 'restaurant_accepted', 'preparing', 'ready_for_pickup',
                'rider_assigned', 'rider_at_restaurant', 'food_picked_up', 'out_for_delivery', 'rider_at_location',
            ]);
        }
        $orders = $q->orderByDesc('id')->paginate((int) $request->get('per_page', 20));
        return response()->json(['success' => true, 'data' => $orders]);
    }

    public function show(Request $request, $id)
    {
        $restaurant = $this->restaurant($request);
        $order = FoodOrder::with('items')->where('restaurant_id', $restaurant->id)->where('id', $id)->first();
        if (!$order) {
            return response()->json(['success' => false, 'error' => 'Order not found.']);
        }
        return response()->json(['success' => true, 'data' => $order]);
    }

    public function accept(Request $request, $id)
    {
        $restaurant = $this->restaurant($request);
        $order = FoodOrder::where('restaurant_id', $restaurant->id)->where('id', $id)->where('order_status', 'pending')->first();
        if (!$order) {
            return response()->json(['success' => false, 'error' => 'Pending order not found.']);
        }
        $order->order_status = 'restaurant_accepted';
        $order->prep_minutes = (int) $request->get('prep_minutes', $restaurant->avg_prep_minutes ?? 20);
        $order->accepted_at = now();
        $order->save();
        $this->notify($restaurant, 'Order Accepted', 'Order #' . $order->order_number . ' accepted.', 'order', $order->id);
        return response()->json(['success' => true, 'data' => $order->load('items')]);
    }

    public function reject(Request $request, $id)
    {
        $restaurant = $this->restaurant($request);
        $order = FoodOrder::where('restaurant_id', $restaurant->id)->where('id', $id)->where('order_status', 'pending')->first();
        if (!$order) {
            return response()->json(['success' => false, 'error' => 'Pending order not found.']);
        }
        $order->order_status = 'rejected';
        $order->reject_reason = $request->get('reason', 'Restaurant rejected');
        $order->rejected_at = now();
        if (in_array($order->payment_status, ['paid'], true)) {
            $order->payment_status = 'refunded';
            $order->refund_amount = $order->customer_payable;
        }
        $order->save();
        return response()->json(['success' => true, 'data' => $order]);
    }

    public function updateStatus(Request $request, $id)
    {
        $restaurant = $this->restaurant($request);
        $order = FoodOrder::where('restaurant_id', $restaurant->id)->where('id', $id)->first();
        if (!$order) {
            return response()->json(['success' => false, 'error' => 'Order not found.']);
        }
        $status = $request->get('order_status', $request->get('status'));
        $allowed = [
            'restaurant_accepted' => ['preparing'],
            'preparing' => ['ready_for_pickup'],
            'ready_for_pickup' => ['ready_for_pickup'],
        ];
        $current = $order->order_status;
        if ($status === 'preparing' && in_array($current, ['restaurant_accepted', 'preparing'], true)) {
            $order->order_status = 'preparing';
        } elseif ($status === 'ready_for_pickup' && in_array($current, ['restaurant_accepted', 'preparing', 'ready_for_pickup'], true)) {
            $order->order_status = 'ready_for_pickup';
            $order->ready_at = now();
            $order->pickup_otp = $order->pickup_otp ?: (string) random_int(1000, 9999);
        } else {
            return response()->json(['success' => false, 'error' => 'Invalid status transition.']);
        }
        $order->save();
        return response()->json(['success' => true, 'data' => $order->load('items')]);
    }

    public function confirmHandover(Request $request, $id)
    {
        $restaurant = $this->restaurant($request);
        $order = FoodOrder::where('restaurant_id', $restaurant->id)->where('id', $id)->first();
        if (!$order) {
            return response()->json(['success' => false, 'error' => 'Order not found.']);
        }
        $otp = (string) $request->get('pickup_otp');
        if ($order->pickup_otp && $otp && $otp !== $order->pickup_otp) {
            return response()->json(['success' => false, 'error' => 'Invalid pickup OTP.']);
        }
        $order->order_status = 'food_picked_up';
        $order->picked_up_at = now();
        $order->rider_status = 'picked_up';
        $order->save();
        return response()->json(['success' => true, 'data' => $order]);
    }

    protected function notify(FoodRestaurant $restaurant, string $title, string $message, string $type, $refId): void
    {
        FoodNotification::create([
            'restaurant_id' => $restaurant->id,
            'owner_id' => $restaurant->owner_id,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'ref_id' => $refId,
            'is_read' => false,
        ]);
    }
}
