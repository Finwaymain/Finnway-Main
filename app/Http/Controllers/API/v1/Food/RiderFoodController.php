<?php

namespace App\Http\Controllers\API\v1\Food;

use App\Http\Controllers\Controller;
use App\Models\Food\FoodDuePayment;
use App\Models\Food\FoodNotification;
use App\Models\Food\FoodOrder;
use App\Models\Food\FoodTransaction;
use Illuminate\Http\Request;

class RiderFoodController extends Controller
{
    public function incoming(Request $request)
    {
        $lat = (float) $request->get('latitude');
        $lng = (float) $request->get('longitude');
        $radius = (float) $request->get('radius_km', 2);

        $orders = FoodOrder::with('restaurant', 'items')
            ->where('order_status', 'ready_for_pickup')
            ->whereNull('rider_id')
            ->orderByDesc('id')
            ->limit(50)
            ->get()
            ->filter(function ($order) use ($lat, $lng, $radius) {
                if (!$lat || !$lng || !$order->restaurant || !$order->restaurant->latitude) {
                    return true;
                }
                $d = \App\Services\Food\FoodPricingEngine::haversineKm(
                    $lat, $lng,
                    (float) $order->restaurant->latitude,
                    (float) $order->restaurant->longitude
                );
                $order->pickup_distance_km = $d;
                return $d <= $radius;
            })
            ->values();

        return response()->json(['success' => true, 'data' => $orders]);
    }

    public function accept(Request $request, $id)
    {
        $order = FoodOrder::where('id', $id)->where('order_status', 'ready_for_pickup')->whereNull('rider_id')->first();
        if (!$order) {
            return response()->json(['success' => false, 'error' => 'Order not available.']);
        }
        $active = FoodOrder::where('rider_id', $request->get('rider_id'))
            ->whereIn('order_status', ['rider_assigned', 'rider_at_restaurant', 'food_picked_up', 'out_for_delivery', 'rider_at_location'])
            ->count();
        $max = (int) \App\Models\Food\FoodSetting::getValue('max_food_orders_per_rider', 2);
        if ($active >= $max) {
            return response()->json(['success' => false, 'error' => 'Active order limit reached.']);
        }

        $order->rider_id = $request->get('rider_id');
        $order->rider_name = $request->get('rider_name');
        $order->rider_phone = $request->get('rider_phone');
        $order->rider_status = 'assigned';
        $order->order_status = 'rider_assigned';
        $order->pickup_otp = $order->pickup_otp ?: (string) random_int(1000, 9999);
        $order->save();

        FoodNotification::create([
            'restaurant_id' => $order->restaurant_id,
            'title' => 'Rider Assigned',
            'message' => 'Rider assigned for order #' . $order->order_number,
            'type' => 'order',
            'ref_id' => $order->id,
            'is_read' => false,
        ]);

        return response()->json(['success' => true, 'data' => $order->load('items', 'restaurant')]);
    }

    public function active(Request $request)
    {
        $orders = FoodOrder::with('items', 'restaurant')
            ->where('rider_id', $request->get('rider_id'))
            ->whereIn('order_status', [
                'rider_assigned', 'rider_at_restaurant', 'food_picked_up', 'out_for_delivery', 'rider_at_location',
            ])
            ->orderBy('id')
            ->get();
        return response()->json(['success' => true, 'data' => $orders]);
    }

    public function updateStatus(Request $request, $id)
    {
        $order = FoodOrder::where('id', $id)->where('rider_id', $request->get('rider_id'))->first();
        if (!$order) {
            return response()->json(['success' => false, 'error' => 'Order not found.']);
        }
        $status = $request->get('status');
        $map = [
            'arrived_restaurant' => 'rider_at_restaurant',
            'picked_up' => 'food_picked_up',
            'out_for_delivery' => 'out_for_delivery',
            'arrived_customer' => 'rider_at_location',
            'delivered' => 'delivered',
        ];
        if (!isset($map[$status])) {
            return response()->json(['success' => false, 'error' => 'Invalid status.']);
        }
        if ($status === 'picked_up') {
            $otp = (string) $request->get('pickup_otp');
            if ($order->pickup_otp && $otp !== $order->pickup_otp) {
                return response()->json(['success' => false, 'error' => 'Invalid pickup OTP.']);
            }
            $order->picked_up_at = now();
        }
        if ($status === 'delivered') {
            $otp = (string) $request->get('delivery_otp');
            if ($order->delivery_otp && $otp && $otp !== $order->delivery_otp) {
                return response()->json(['success' => false, 'error' => 'Invalid delivery OTP.']);
            }
            if ($order->payment_method === 'cod') {
                $order->payment_status = 'cash_collected';
                FoodDuePayment::create([
                    'rider_id' => $order->rider_id,
                    'order_id' => $order->id,
                    'party_type' => 'rider',
                    'due_type' => 'cod',
                    'amount' => $order->customer_payable,
                    'paid_amount' => 0,
                    'status' => 'pending',
                ]);
            } else {
                $order->payment_status = 'paid';
            }
            $order->delivered_at = now();
            $order->order_status = 'delivered';
            $order->rider_status = 'delivered';
            $order->save();
            return response()->json(['success' => true, 'data' => $order]);
        }
        $order->order_status = $map[$status];
        $order->rider_status = $status;
        $order->save();
        return response()->json(['success' => true, 'data' => $order]);
    }

    public function dues(Request $request)
    {
        $dues = FoodDuePayment::where('rider_id', $request->get('rider_id'))
            ->where('party_type', 'rider')
            ->orderByDesc('id')
            ->get();
        $pending = $dues->where('status', '!=', 'paid')->sum(fn ($d) => $d->amount - $d->paid_amount);
        return response()->json(['success' => true, 'data' => ['pending_due' => $pending, 'items' => $dues]]);
    }

    public function payDue(Request $request)
    {
        $due = FoodDuePayment::where('rider_id', $request->get('rider_id'))
            ->where('id', $request->get('due_id'))
            ->first();
        if (!$due) {
            return response()->json(['success' => false, 'error' => 'Due not found.']);
        }
        $amount = (float) $request->get('amount', $due->amount - $due->paid_amount);
        $due->paid_amount = min($due->amount, $due->paid_amount + $amount);
        $due->status = $due->paid_amount >= $due->amount ? 'paid' : 'partial';
        $due->payment_ref = $request->get('payment_ref', 'RIDER_DUE_' . time());
        if ($due->status === 'paid') {
            $due->paid_at = now();
        }
        $due->save();
        FoodTransaction::create([
            'txn_number' => 'TXN' . time() . 'R' . $due->rider_id,
            'order_id' => $due->order_id,
            'txn_type' => 'due_payment',
            'amount' => $amount,
            'payment_method' => $request->get('payment_method', 'upi'),
            'status' => 'paid',
            'gateway_ref' => $due->payment_ref,
            'notes' => 'Rider company due payment',
        ]);
        return response()->json(['success' => true, 'data' => $due]);
    }
}
