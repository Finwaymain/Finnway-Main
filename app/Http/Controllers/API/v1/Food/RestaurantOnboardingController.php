<?php

namespace App\Http\Controllers\API\v1\Food;

use App\Http\Controllers\Controller;
use App\Models\Food\FoodOnboardingPayment;
use App\Models\Food\FoodRestaurant;
use App\Models\Food\FoodRestaurantType;
use App\Models\Food\FoodTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Helpers\RazorpayConfig;

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
        $restaurant->category = $request->input('category');
        $restaurant->sub_category = $request->input('sub_category');
        // cuisines: array of cuisine names selected by owner
        $cuisinesInput = $request->input('cuisines');
        if (is_array($cuisinesInput)) {
            $restaurant->cuisines = $cuisinesInput;
        } elseif (is_string($cuisinesInput) && $cuisinesInput !== '') {
            $decoded = json_decode($cuisinesInput, true);
            $restaurant->cuisines = is_array($decoded) ? $decoded : explode(',', $cuisinesInput);
        }
        $restaurant->name = $name;
        $restaurant->slug = Str::slug($name) . '-' . $owner->id;
        $restaurant->description = $request->input('description');
        $restaurant->owner_name = $request->input('owner_name', $owner->name);
        $restaurant->owner_phone = $request->input('owner_phone', $owner->phone);
        $restaurant->owner_email = $request->input('owner_email', $owner->email);
        $restaurant->address = $request->input('address');
        $restaurant->landmark = $request->input('landmark');
        $restaurant->area = $request->input('area');
        $restaurant->city = $request->input('city');
        $restaurant->state = $request->input('state');
        $restaurant->pincode = $request->input('pincode');
        $restaurant->latitude = $request->input('latitude');
        $restaurant->longitude = $request->input('longitude');
        $restaurant->opening_time = $request->input('opening_time');
        $restaurant->closing_time = $request->input('closing_time');
        $restaurant->working_days = is_array($request->input('working_days'))
            ? json_encode($request->input('working_days'))
            : $request->input('working_days');
        $restaurant->avg_prep_minutes = (int) $request->input('avg_prep_minutes', 20);
        $restaurant->delivery_radius_km = (float) $request->input('delivery_radius_km', 5);
        $restaurant->min_order_amount = (float) $request->input('min_order_amount', 0);
        $restaurant->max_order_amount = $request->input('max_order_amount');
        $restaurant->delivery_available = (bool) $request->input('delivery_available', true);
        $restaurant->takeaway_available = (bool) $request->input('takeaway_available', false);
        $restaurant->dine_in_available = (bool) $request->input('dine_in_available', false);
        $restaurant->fssai_number = $request->input('fssai_number');
        $restaurant->gst_number = $request->input('gst_number');
        $restaurant->pan_number = $request->input('pan_number');
        $restaurant->bank_account_name = $request->input('bank_account_name');
        $restaurant->bank_name = $request->input('bank_name');
        $restaurant->bank_account_number = $request->input('bank_account_number');
        $restaurant->bank_ifsc = $request->input('bank_ifsc');
        $restaurant->bank_branch = $request->input('bank_branch');
        $restaurant->upi_id = $request->input('upi_id');

        foreach (['owner_image', 'logo', 'cover_image', 'id_proof', 'business_proof', 'fssai_doc', 'gst_doc', 'cancelled_cheque'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $folder = $fileField === 'owner_image' ? ('food/owners/' . $owner->id) : ('food/restaurants/' . $owner->id);
                $restaurant->{$fileField} = $request->file($fileField)->store($folder, 'public');
            } elseif ($request->filled($fileField)) {
                $restaurant->{$fileField} = $request->input($fileField);
            }
        }

        if ($restaurant->owner_image) {
            $owner->image = $restaurant->owner_image;
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

        if ($request->filled('owner_name')) {
            $owner->name = $request->get('owner_name');
        }
        if ($request->filled('owner_email')) {
            $owner->email = $request->get('owner_email');
        }
        if ($request->filled('pan_number')) {
            $owner->pan_number = $request->get('pan_number');
        }
        $owner->save();

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

        $config = RazorpayConfig::resolve();
        $razorpayKey = $config['key'] ?? '';
        $razorpaySecret = $config['secret'] ?? '';

        $gatewayOrderId = 'FOOD_ONB_' . $restaurant->id . '_' . time();
        if (!empty($razorpayKey) && !empty($razorpaySecret)) {
            try {
                $client = new \Razorpay\Api\Api($razorpayKey, $razorpaySecret);
                $razorpayOrder = $client->order->create([
                    'receipt' => 'onb_' . $restaurant->id . '_' . time(),
                    'amount' => intval(round($fee * 100)), // in paise
                    'currency' => 'INR',
                    'notes' => [
                        'restaurant_id' => (string) $restaurant->id,
                        'restaurant_name' => (string) $restaurant->name,
                        'type' => 'onboarding_fee',
                    ],
                ]);
                if (isset($razorpayOrder['id'])) {
                    $gatewayOrderId = $razorpayOrder['id'];
                }
            } catch (\Throwable $e) {
                \Log::warning('Food onboarding Razorpay order creation fallback: ' . $e->getMessage());
            }
        }

        $payment = FoodOnboardingPayment::create([
            'restaurant_id' => $restaurant->id,
            'owner_id' => $owner->id,
            'amount' => $fee,
            'currency' => 'INR',
            'payment_method' => 'upi',
            'gateway' => 'razorpay',
            'gateway_order_id' => $gatewayOrderId,
            'status' => 'pending',
        ]);

        $restaurant->onboarding_status = 'payment_pending';
        $restaurant->save();

        return response()->json([
            'success' => true,
            'data' => [
                'payment_id' => $payment->id,
                'gateway_order_id' => $gatewayOrderId,
                'amount' => $fee,
                'amount_paise' => intval(round($fee * 100)),
                'currency' => 'INR',
                'razorpay_key' => $razorpayKey,
                'restaurant_name' => $restaurant->name,
                'owner_name' => $owner->name ?: $restaurant->name,
                'owner_phone' => $owner->phone,
                'owner_email' => $owner->email,
            ],
        ]);
    }

    public function confirmPayment(Request $request)
    {
        $owner = $request->attributes->get('food_owner');
        $payment = FoodOnboardingPayment::where('owner_id', $owner->id)
            ->where(function($q) use ($request) {
                if ($request->filled('payment_id')) {
                    $q->where('id', $request->get('payment_id'));
                }
                if ($request->filled('gateway_order_id')) {
                    $q->orWhere('gateway_order_id', $request->get('gateway_order_id'));
                }
            })
            ->orderByDesc('id')
            ->first();

        $gatewayPaymentId = $request->get('gateway_payment_id', $request->get('razorpay_payment_id', 'RZP_' . Str::upper(Str::random(10))));

        if ($payment) {
            $payment->status = 'paid';
            $payment->gateway_payment_id = $gatewayPaymentId;
            $payment->payment_method = 'upi';
            $payment->save();
            $restaurant = FoodRestaurant::find($payment->restaurant_id);
            $amount = $payment->amount;
        } else {
            $restaurant = FoodRestaurant::where('owner_id', $owner->id)->orderByDesc('id')->first();
            if (!$restaurant) {
                return response()->json(['success' => false, 'error' => 'Restaurant not found.']);
            }
            $type = FoodRestaurantType::find($restaurant->type_id);
            $amount = (float) ($type->onboarding_fee ?? 0);
            $payment = FoodOnboardingPayment::create([
                'restaurant_id' => $restaurant->id,
                'owner_id' => $owner->id,
                'amount' => $amount,
                'currency' => 'INR',
                'payment_method' => 'upi',
                'gateway' => 'razorpay',
                'gateway_order_id' => $request->get('gateway_order_id', 'FOOD_ONB_' . $restaurant->id . '_' . time()),
                'gateway_payment_id' => $gatewayPaymentId,
                'status' => 'paid',
            ]);
        }

        $type = FoodRestaurantType::find($restaurant->type_id);
        $restaurant->onboarding_fee_paid = $amount;
        $restaurant->onboarding_payment_id = $gatewayPaymentId;
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
            'amount' => $amount,
            'payment_method' => 'upi',
            'status' => 'paid',
            'gateway_ref' => $gatewayPaymentId,
            'notes' => 'Restaurant onboarding fee paid via Razorpay UPI',
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
        $restaurant = FoodRestaurant::where('owner_id', $owner->id)->orderByDesc('id')->first();
        if (!$restaurant) {
            return response()->json(['success' => false, 'error' => 'Restaurant not found.']);
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
        /** @var \App\Models\Food\FoodOwner $owner */
        $owner = $request->attributes->get('food_owner');
        $restaurant = FoodRestaurant::where('owner_id', $owner->id)->orderByDesc('id')->first();
        if (!$restaurant) {
            return response()->json(['success' => false, 'error' => 'Restaurant not found.']);
        }
        $allowed = [
            'name', 'description', 'address', 'landmark', 'area', 'city', 'state', 'pincode',
            'latitude', 'longitude', 'opening_time', 'closing_time', 'working_days',
            'avg_prep_minutes', 'delivery_radius_km', 'min_order_amount', 'max_order_amount',
            'delivery_available', 'takeaway_available', 'dine_in_available', 'auto_accept',
            'pure_veg', 'operational_status',
            'bank_account_name', 'bank_name', 'bank_account_number', 'bank_ifsc', 'bank_branch', 'upi_id',
            'fssai_number', 'gst_number', 'pan_number',
            'owner_name', 'owner_email', 'owner_phone',
        ];
        foreach ($allowed as $field) {
            if ($request->exists($field)) {
                $val = $request->get($field);
                if ($field === 'working_days' && is_array($val)) {
                    $val = json_encode($val);
                }
                if ($field === 'pure_veg') {
                    $val = filter_var($val, FILTER_VALIDATE_BOOLEAN);
                }
                $restaurant->{$field} = $val;
            }
        }

        // Handle Owner Image (file upload, base64 data URI, or string path)
        $ownerImageFile = $request->file('owner_image') ?: $request->file('image');
        if ($ownerImageFile) {
            $path = $ownerImageFile->store('food/owners/' . $owner->id, 'public');
            $restaurant->owner_image = $path;
            $owner->image = $path;
            $owner->save();
        } else {
            $rawOwnerImage = $request->input('owner_image') ?: $request->input('image');
            if (is_string($rawOwnerImage) && str_starts_with($rawOwnerImage, 'data:image')) {
                try {
                    $parts = explode(',', $rawOwnerImage);
                    $data = base64_decode($parts[1] ?? '');
                    if (!empty($data)) {
                        $filename = 'food/owners/' . $owner->id . '/owner_' . time() . '.jpg';
                        \Illuminate\Support\Facades\Storage::disk('public')->put($filename, $data);
                        $restaurant->owner_image = $filename;
                        $owner->image = $filename;
                        $owner->save();
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Base64 owner image save error: ' . $e->getMessage());
                }
            } elseif (is_string($rawOwnerImage) && !empty($rawOwnerImage) && !str_starts_with($rawOwnerImage, 'data:image')) {
                $restaurant->owner_image = $rawOwnerImage;
                $owner->image = $rawOwnerImage;
                $owner->save();
            }
        }

        // Handle Restaurant Logo & Cover Image
        foreach (['logo', 'cover_image', 'fssai_doc', 'gst_doc', 'cancelled_cheque'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $restaurant->{$fileField} = $request->file($fileField)->store('food/restaurants/' . $owner->id, 'public');
            } else {
                $rawVal = $request->input($fileField);
                if (is_string($rawVal) && str_starts_with($rawVal, 'data:image')) {
                    try {
                        $parts = explode(',', $rawVal);
                        $data = base64_decode($parts[1] ?? '');
                        if (!empty($data)) {
                            $filename = 'food/restaurants/' . $owner->id . '/' . $fileField . '_' . time() . '.jpg';
                            \Illuminate\Support\Facades\Storage::disk('public')->put($filename, $data);
                            $restaurant->{$fileField} = $filename;
                        }
                    } catch (\Throwable $e) {}
                } elseif (is_string($rawVal) && !empty($rawVal) && !str_starts_with($rawVal, 'data:image')) {
                    $restaurant->{$fileField} = $rawVal;
                }
            }
        }

        // Sync owner profile attributes
        if ($request->filled('owner_name')) {
            $owner->name = $request->input('owner_name');
        }
        if ($request->filled('owner_email')) {
            $owner->email = $request->input('owner_email');
        }
        if ($request->filled('pan_number')) {
            $owner->pan_number = $request->input('pan_number');
        }
        $owner->save();

        $restaurant->save();

        // Build augmented response with full image URLs
        $resData = $restaurant->toArray();
        $resData['logo_url'] = $restaurant->logo ? (str_starts_with($restaurant->logo, 'http') ? $restaurant->logo : asset('storage/' . $restaurant->logo)) : null;
        $resData['cover_url'] = $restaurant->cover_image ? (str_starts_with($restaurant->cover_image, 'http') ? $restaurant->cover_image : asset('storage/' . $restaurant->cover_image)) : null;
        $resData['owner_image_url'] = $restaurant->owner_image ? (str_starts_with($restaurant->owner_image, 'http') ? $restaurant->owner_image : asset('storage/' . $restaurant->owner_image)) : ($owner->image ? asset('storage/' . $owner->image) : null);
        $resData['owner'] = [
            'id' => $owner->id,
            'name' => $owner->name,
            'phone' => $owner->phone,
            'email' => $owner->email,
            'image' => $resData['owner_image_url'],
            'pan_number' => $owner->pan_number,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data' => $resData,
        ]);
    }

    public function uploadImage(Request $request)
    {
        /** @var \App\Models\Food\FoodOwner $owner */
        $owner = $request->attributes->get('food_owner');
        $file = $request->file('image') ?: ($request->file('file') ?: $request->file('photo'));
        $type = $request->input('type', 'general'); // owner | logo | cover | dish | general
        
        $folder = 'food/restaurants/' . $owner->id;
        if ($type === 'owner') {
            $folder = 'food/owners/' . $owner->id;
        } elseif ($type === 'dish') {
            $folder = 'food/products';
        }

        if ($file) {
            $path = $file->store($folder, 'public');
        } else {
            $base64 = $request->input('image') ?: $request->input('data');
            if (is_string($base64) && str_starts_with($base64, 'data:image')) {
                try {
                    $parts = explode(',', $base64);
                    $data = base64_decode($parts[1] ?? '');
                    if (!empty($data)) {
                        $prefix = ($type === 'dish') ? uniqid('prod_', true) : ($type . '_' . time());
                        $filename = $folder . '/' . $prefix . '.jpg';
                        \Illuminate\Support\Facades\Storage::disk('public')->put($filename, $data);
                        $path = $filename;
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Image upload base64 error: ' . $e->getMessage());
                }
            }
        }

        if (!$path) {
            return response()->json(['success' => false, 'error' => 'No image file or valid image data provided.'], 400);
        }

        $url = asset('storage/' . $path);

        // Auto-persist directly to owner / restaurant models based on type
        if ($type === 'owner') {
            $owner->image = $path;
            $owner->save();
            FoodRestaurant::where('owner_id', $owner->id)->update(['owner_image' => $path]);
        } elseif ($type === 'logo') {
            FoodRestaurant::where('owner_id', $owner->id)->update(['logo' => $path]);
        } elseif ($type === 'cover') {
            FoodRestaurant::where('owner_id', $owner->id)->update(['cover_image' => $path]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Image uploaded successfully.',
            'url' => $url,
            'path' => $path,
            'type' => $type,
        ]);
    }
}
