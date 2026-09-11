<?php

namespace App\Http\Controllers\API\v1\Food;

use App\Http\Controllers\Controller;
use App\Models\Food\FoodOnboardingPayment;
use App\Models\Food\FoodRestaurant;
use App\Models\Food\FoodRestaurantType;
use App\Models\Food\FoodTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RestaurantOnboardingController extends Controller
{
    public function myRestaurant(Request $request)
    {
        $owner = $request->attributes->get('food_owner');
        $restaurant = FoodRestaurant::where('owner_id', $owner->id)->orderByDesc('id')->first();
        return response()->json(['success' => true, 'data' => $restaurant]);
    }

    public function submit(Request $request)
    {
        $owner = $request->attributes->get('food_owner');
        $typeCode = $request->get('business_type', $request->get('type_code'));
        $type = FoodRestaurantType::where('code', $typeCode)->where('is_active', true)->first();
        if (!$type) {
            return response()->json(['success' => false, 'error' => 'Invalid or inactive restaurant type.']);
        }

        $name = trim((string) $request->get('name'));
        if ($name === '') {
            return response()->json(['success' => false, 'error' => 'Restaurant name is required.']);
        }

        $restaurant = FoodRestaurant::where('owner_id', $owner->id)->orderByDesc('id')->first() ?: new FoodRestaurant();
        $restaurant->owner_id = $owner->id;
        $restaurant->type_id = $type->id;
        $restaurant->business_type = $type->code;
        $restaurant->category = $request->get('category');
        $restaurant->sub_category = $request->get('sub_category');
        $restaurant->name = $name;
        $restaurant->slug = Str::slug($name) . '-' . $owner->id;
        $restaurant->description = $request->get('description');
        $restaurant->owner_name = $request->get('owner_name', $owner->name);
        $restaurant->owner_phone = $request->get('owner_phone', $owner->phone);
        $restaurant->owner_email = $request->get('owner_email', $owner->email);
        $restaurant->address = $request->get('address');
        $restaurant->landmark = $request->get('landmark');
        $restaurant->area = $request->get('area');
        $restaurant->city = $request->get('city');
        $restaurant->state = $request->get('state');
        $restaurant->pincode = $request->get('pincode');
        $restaurant->latitude = $request->get('latitude');
        $restaurant->longitude = $request->get('longitude');
        $restaurant->opening_time = $request->get('opening_time');
        $restaurant->closing_time = $request->get('closing_time');
        $restaurant->working_days = is_array($request->get('working_days'))
            ? json_encode($request->get('working_days'))
            : $request->get('working_days');
        $restaurant->avg_prep_minutes = (int) $request->get('avg_prep_minutes', 20);
        $restaurant->delivery_radius_km = (float) $request->get('delivery_radius_km', 5);
        $restaurant->min_order_amount = (float) $request->get('min_order_amount', 0);
        $restaurant->max_order_amount = $request->get('max_order_amount');
        $restaurant->delivery_available = (bool) $request->get('delivery_available', true);
        $restaurant->takeaway_available = (bool) $request->get('takeaway_available', false);
        $restaurant->dine_in_available = (bool) $request->get('dine_in_available', false);
        $restaurant->fssai_number = $request->get('fssai_number');
        $restaurant->gst_number = $request->get('gst_number');
        $restaurant->pan_number = $request->get('pan_number');
        $restaurant->bank_account_name = $request->get('bank_account_name');
        $restaurant->bank_name = $request->get('bank_name');
        $restaurant->bank_account_number = $request->get('bank_account_number');
        $restaurant->bank_ifsc = $request->get('bank_ifsc');
        $restaurant->bank_branch = $request->get('bank_branch');
        $restaurant->upi_id = $request->get('upi_id');

        foreach (['logo', 'cover_image', 'id_proof', 'business_proof', 'fssai_doc', 'gst_doc', 'cancelled_cheque'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $restaurant->{$fileField} = $request->file($fileField)->store('food/restaurants/' . $owner->id, 'public');
            } elseif ($request->filled($fileField)) {
                $restaurant->{$fileField} = $request->get($fileField);
            }
        }

        $fee = (float) $type->onboarding_fee;
        if ($fee <= 0) {
            $restaurant->onboarding_status = $type->approval_mode === 'auto' ? 'active' : 'pending_approval';
            if ($restaurant->onboarding_status === 'active') {
                $restaurant->operational_status = 'closed';
                $restaurant->approved_at = now();
            }
            $restaurant->onboarding_fee_paid = 0;
        } else {
            $restaurant->onboarding_status = 'payment_pending';
        }
        $restaurant->save();

        if (!$owner->name && $request->filled('owner_name')) {
            $owner->name = $request->get('owner_name');
            $owner->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Onboarding details saved.',
            'data' => [
                'restaurant' => $restaurant->fresh(),
                'onboarding_fee' => $fee,
                'payment_required' => $fee > 0,
                'approval_mode' => $type->approval_mode,
            ],
        ]);
    }

    public function initiatePayment(Request $request)
    {
        $owner = $request->attributes->get('food_owner');
        $restaurant = FoodRestaurant::where('owner_id', $owner->id)->orderByDesc('id')->first();
        if (!$restaurant) {
            return response()->json(['success' => false, 'error' => 'Complete registration first.']);
        }
        $type = FoodRestaurantType::find($restaurant->type_id);
        $fee = (float) ($type->onboarding_fee ?? 0);
        if ($fee <= 0) {
            return response()->json(['success' => false, 'error' => 'No onboarding fee configured.']);
        }

        $payment = FoodOnboardingPayment::create([
            'restaurant_id' => $restaurant->id,
            'owner_id' => $owner->id,
            'amount' => $fee,
            'currency' => 'INR',
            'payment_method' => $request->get('payment_method', 'upi'),
            'gateway' => 'razorpay',
            'gateway_order_id' => 'FOOD_ONB_' . $restaurant->id . '_' . time(),
            'status' => 'pending',
        ]);

        $restaurant->onboarding_status = 'payment_pending';
        $restaurant->save();

        return response()->json([
            'success' => true,
            'data' => [
                'payment_id' => $payment->id,
                'gateway_order_id' => $payment->gateway_order_id,
                'amount' => $fee,
                'currency' => 'INR',
                'mock_payable' => true,
            ],
        ]);
    }

    public function confirmPayment(Request $request)
    {
        $owner = $request->attributes->get('food_owner');
        $payment = FoodOnboardingPayment::where('owner_id', $owner->id)
            ->where('id', $request->get('payment_id'))
            ->first();
        if (!$payment) {
            return response()->json(['success' => false, 'error' => 'Payment not found.']);
        }

        $payment->status = 'paid';
        $payment->gateway_payment_id = $request->get('gateway_payment_id', 'MOCK_' . Str::upper(Str::random(10)));
        $payment->payment_method = $request->get('payment_method', $payment->payment_method);
        $payment->save();

        $restaurant = FoodRestaurant::find($payment->restaurant_id);
        $type = FoodRestaurantType::find($restaurant->type_id);
        $restaurant->onboarding_fee_paid = $payment->amount;
        $restaurant->onboarding_payment_id = $payment->gateway_payment_id;
        $restaurant->onboarding_status = (($type->approval_mode ?? 'manual') === 'auto') ? 'active' : 'pending_approval';
        if ($restaurant->onboarding_status === 'active') {
            $restaurant->operational_status = 'closed';
            $restaurant->approved_at = now();
        }
        $restaurant->save();

        FoodTransaction::create([
            'txn_number' => 'TXN' . time() . $restaurant->id,
            'restaurant_id' => $restaurant->id,
            'txn_type' => 'onboarding',
            'amount' => $payment->amount,
            'payment_method' => $payment->payment_method,
            'status' => 'paid',
            'gateway_ref' => $payment->gateway_payment_id,
            'notes' => 'Restaurant onboarding fee',
        ]);

        return response()->json([
            'success' => true,
            'message' => $restaurant->onboarding_status === 'active'
                ? 'Payment successful. Restaurant activated.'
                : 'Payment successful. Waiting for admin approval.',
            'data' => $restaurant,
        ]);
    }

    public function submitPaymentProof(Request $request)
    {
        $owner = $request->attributes->get('food_owner');
        $restaurant = FoodRestaurant::where('owner_id', $owner->id)->orderByDesc('id')->first();
        if (!$restaurant) {
            return response()->json(['success' => false, 'error' => 'Restaurant registration not found.']);
        }

        $utr = trim((string) $request->get('utr_number', $request->get('transaction_id')));
        if (empty($utr)) {
            return response()->json(['success' => false, 'error' => 'Please provide UTR or Transaction ID.']);
        }

        $type = FoodRestaurantType::find($restaurant->type_id);
        $fee = (float) ($type->onboarding_fee ?? 0);

        $payment = FoodOnboardingPayment::where('owner_id', $owner->id)
            ->where('restaurant_id', $restaurant->id)
            ->orderByDesc('id')
            ->first();

        if (!$payment) {
            $payment = FoodOnboardingPayment::create([
                'restaurant_id' => $restaurant->id,
                'owner_id' => $owner->id,
                'amount' => $fee,
                'currency' => 'INR',
                'payment_method' => $request->get('payment_method', 'upi'),
                'gateway' => 'manual_qr',
                'gateway_order_id' => 'FOOD_ONB_' . $restaurant->id . '_' . time(),
                'status' => 'pending',
            ]);
        }

        $proofPath = null;
        if ($request->hasFile('payment_proof')) {
            $proofPath = $request->file('payment_proof')->store('food/payments/' . $restaurant->id, 'public');
        } elseif ($request->filled('payment_proof')) {
            $proofPath = $request->get('payment_proof');
        }

        $payment->gateway_payment_id = $utr;
        $payment->payment_method = $request->get('payment_method', $payment->payment_method ?: 'upi');
        $payment->meta = array_merge(is_array($payment->meta) ? $payment->meta : [], [
            'utr' => $utr,
            'proof' => $proofPath,
            'submitted_at' => now()->toDateTimeString(),
            'payer_note' => $request->get('note'),
        ]);
        $payment->status = 'submitted';
        $payment->save();

        $restaurant->onboarding_payment_id = $utr;
        $restaurant->onboarding_fee_paid = $fee;
        $restaurant->onboarding_status = 'pending_approval';
        $restaurant->save();

        return response()->json([
            'success' => true,
            'message' => 'Onboarding payment details submitted successfully. Admin will verify and activate your restaurant.',
            'data' => [
                'restaurant' => $restaurant,
                'payment' => $payment,
            ],
        ]);
    }

    public function setOperationalStatus(Request $request)
    {
        $owner = $request->attributes->get('food_owner');
        $restaurant = FoodRestaurant::where('owner_id', $owner->id)->where('onboarding_status', 'active')->first();
        if (!$restaurant) {
            return response()->json(['success' => false, 'error' => 'Active restaurant not found.']);
        }
        $status = $request->get('operational_status', $request->get('status'));
        if (!in_array($status, ['open', 'busy', 'closed', 'temporarily_closed'], true)) {
            return response()->json(['success' => false, 'error' => 'Invalid status.']);
        }
        $restaurant->operational_status = $status;
        $restaurant->save();
        return response()->json(['success' => true, 'data' => $restaurant]);
    }

    public function updateProfile(Request $request)
    {
        $owner = $request->attributes->get('food_owner');
        $restaurant = FoodRestaurant::where('owner_id', $owner->id)->first();
        if (!$restaurant) {
            return response()->json(['success' => false, 'error' => 'Restaurant not found.']);
        }
        $allowed = [
            'name', 'description', 'address', 'landmark', 'area', 'city', 'state', 'pincode',
            'latitude', 'longitude', 'opening_time', 'closing_time', 'working_days',
            'avg_prep_minutes', 'delivery_radius_km', 'min_order_amount', 'max_order_amount',
            'delivery_available', 'takeaway_available', 'dine_in_available', 'auto_accept',
            'bank_account_name', 'bank_name', 'bank_account_number', 'bank_ifsc', 'bank_branch', 'upi_id',
            'fssai_number', 'gst_number', 'pan_number',
        ];
        foreach ($allowed as $field) {
            if ($request->exists($field)) {
                $val = $request->get($field);
                if ($field === 'working_days' && is_array($val)) {
                    $val = json_encode($val);
                }
                $restaurant->{$field} = $val;
            }
        }
        foreach (['logo', 'cover_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $restaurant->{$fileField} = $request->file($fileField)->store('food/restaurants/' . $owner->id, 'public');
            }
        }
        $restaurant->save();
        return response()->json(['success' => true, 'data' => $restaurant]);
    }
}
