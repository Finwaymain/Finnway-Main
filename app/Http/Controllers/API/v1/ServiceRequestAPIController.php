<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ServiceRequest;
use App\Models\UserApp;
use App\Models\Zone;

class ServiceRequestAPIController extends Controller
{
    public function bookService(Request $request)
    {
        $user_id = $request->header('id_user') ?? $request->input('user_id');

        if (empty($user_id)) {
            return response()->json([
                'success' => 'error',
                'message' => 'User ID is required',
            ], 422);
        }

        try {

        $mediaUrls = [];

        if ($request->has('media') && is_array($request->input('media'))) {
            $privateKey = config('imagekit.private_key');
            if (empty($privateKey)) {
                return response()->json([
                    'success' => 'failed',
                    'message' => 'IMAGEKIT_PRIVATE_KEY is not configured.'
                ], 500);
            }

            foreach ($request->input('media') as $mediaItem) {
                if (isset($mediaItem['base64'])) {
                    $base64 = $mediaItem['base64'];
                    
                    // Extract base64 content
                    // e.g. "data:image/png;base64,iVBORw..."
                    if (strpos($base64, 'data:') === 0) {
                        $parts = explode(',', $base64);
                        if (count($parts) == 2) {
                            $base64 = $parts[1];
                        }
                    }

                    $filename = 'service_' . time() . '_' . uniqid();

                    $url = "https://upload.imagekit.io/api/v1/files/upload";

                    $postData = [
                        'file' => $base64,
                        'fileName' => $filename,
                        'folder' => '/fiinway_service_requests',
                        'useUniqueFileName' => 'true'
                    ];

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
                    curl_setopt($ch, CURLOPT_USERPWD, $privateKey . ':');

                    $result = curl_exec($ch);
                    if (curl_errno($ch)) {
                        // skip on error
                    } else {
                        $response = json_decode($result, true);
                        if (isset($response['url'])) {
                            $mediaUrls[] = $response['url'];
                        }
                    }
                    curl_close($ch);
                }
            }
        }

        $serviceAddress = trim((string) ($request->input('service_address') ?? $request->input('address') ?? ''));
        $lat = $request->input('lat');
        $lng = $request->input('lng');
        $city = trim((string) ($request->input('city') ?? ''));
        $zoneId = null;

        if (!empty($lat) && !empty($lng)) {
            $latFloat = (float) $lat;
            $lngFloat = (float) $lng;

            if ($serviceAddress === '' || $this->looksLikeCoordinates($serviceAddress)) {
                $geocoded = $this->reverseGeocode($latFloat, $lngFloat);
                if ($serviceAddress === '' || $this->looksLikeCoordinates($serviceAddress)) {
                    $serviceAddress = $geocoded['address'] ?? $serviceAddress;
                }
                if ($city === '') {
                    $city = $geocoded['city'] ?? '';
                }
            }

            $zoneId = $this->findZoneIdForPoint($latFloat, $lngFloat);
        }

        $amount = $request->input('amount');
        $priceBreakdown = $request->input('price_breakdown');

        $serviceNameForEstimate = trim((string) $request->input('service_name', ''));
        $estimateNames = $this->resolveRequestedServiceNames($request, $serviceNameForEstimate);
        if (!$request->has('service_names') && empty($estimateNames) && $serviceNameForEstimate !== '') {
            $estimateNames = [$serviceNameForEstimate];
        }

        if ((empty($amount) || (float) $amount <= 0) && ($request->has('service_names') || !empty($estimateNames))) {
            $latFloat = is_numeric($lat) ? (float) $lat : null;
            $lngFloat = is_numeric($lng) ? (float) $lng : null;
            $estimate = $this->buildPriceEstimatePayload($estimateNames, $latFloat, $lngFloat);
            if ((float) ($estimate['total_min'] ?? 0) > 0) {
                $amount = $estimate['total_min'];
                if (empty($priceBreakdown)) {
                    $priceBreakdown = $estimate;
                }
            }
        }

        $createData = [
            'user_id' => $user_id,
            'driver_id' => $request->input('driver_id'),
            'service_name' => $request->input('service_name'),
            'address_type' => $request->input('address_type'),
            'service_address' => $serviceAddress !== '' ? $serviceAddress : null,
            'city' => $city !== '' ? $city : null,
            'zone_id' => $zoneId,
            'lat' => $lat,
            'lng' => $lng,
            'preferred_date' => $request->input('date'),
            'preferred_time' => $request->input('time'),
            'description' => $request->input('description'),
            'status' => 'Pending',
            'media' => json_encode($mediaUrls),
        ];

        if (\Illuminate\Support\Facades\Schema::hasColumn('service_requests', 'otp')) {
            $createData['otp'] = $this->generateServiceOtp();
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('service_requests', 'amount') && $amount !== null && $amount !== '') {
            $createData['amount'] = (float) $amount;
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('service_requests', 'payment_status')) {
            $createData['payment_status'] = 'pending';
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('service_requests', 'price_breakdown') && !empty($priceBreakdown)) {
            $createData['price_breakdown'] = is_string($priceBreakdown) ? $priceBreakdown : json_encode($priceBreakdown);
        }

        $serviceRequest = ServiceRequest::create($createData);

        // Step 1 Notification: Notify Customer + Notify ONLY Matching/Nearby Service Partners
        try {
            $this->sendServiceNotification(
                (int) $user_id,
                'customer',
                "Booking Confirmed: {$serviceRequest->service_name}",
                "We have received your booking #{$serviceRequest->id}. Looking for the best verified expert near you.",
                ['booking_id' => (string) $serviceRequest->id, 'status' => 'Pending']
            );

            // Send notification strictly to matching/nearby Home Service providers
            $latFloat = is_numeric($lat ?? null) ? (float) $lat : null;
            $lngFloat = is_numeric($lng ?? null) ? (float) $lng : null;
            $this->notifyMatchingServiceProviders($serviceRequest, $latFloat, $lngFloat);
        } catch (\Throwable $notifEx) {
            \Log::error('bookService notification error: ' . $notifEx->getMessage());
        }

        return response()->json([
            'success' => 'success',
            'message' => 'Service request booked successfully.',
            'data' => $this->formatUserBooking($serviceRequest),
        ]);
        } catch (\Throwable $e) {
            \Log::error('bookService failed', [
                'user_id' => $user_id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => 'error',
                'message' => 'Unable to book service. Please try again.',
            ], 500);
        }
    }
    
    public function getHistory(Request $request)
    {
        $user_id = $request->header('id_user') ?? $request->input('user_id');

        if (empty($user_id)) {
            return response()->json(['success' => 'error', 'message' => 'User ID is required'], 422);
        }

        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('service_requests')) {
                return response()->json([
                    'success' => 'success',
                    'data' => [],
                ]);
            }

            $userKeys = [(string) $user_id];
            if (\Illuminate\Support\Facades\Schema::hasTable('tj_user_app')) {
                $user = \Illuminate\Support\Facades\DB::table('tj_user_app')
                    ->where('id', $user_id)
                    ->orWhere('ac_no', $user_id)
                    ->first();
                if ($user) {
                    if (!empty($user->id)) $userKeys[] = (string) $user->id;
                    if (!empty($user->ac_no)) $userKeys[] = (string) $user->ac_no;
                }
            }
            $userKeys = array_values(array_unique(array_filter($userKeys)));

            $requests = ServiceRequest::whereIn('user_id', $userKeys)
                ->orderByDesc('id')
                ->get();

            $data = $requests->map(function ($row) {
                try {
                    if (\Illuminate\Support\Facades\Schema::hasColumn('service_requests', 'otp')) {
                        try {
                            $this->ensureServiceOtp($row);
                            $row->refresh();
                        } catch (\Throwable $e) {
                            \Log::warning('ensureServiceOtp failed in history', [
                                'booking_id' => $row->id ?? null,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }

                    return $this->formatUserBooking($row);
                } catch (\Throwable $e) {
                    \Log::error('formatUserBooking failed in getHistory', [
                        'booking_id' => $row->id ?? null,
                        'error' => $e->getMessage(),
                    ]);

                    $driver = null;
                    if (!empty($row->driver_id)) {
                        try {
                            $driver = $this->resolveDriverPublicProfile((int) $row->driver_id);
                        } catch (\Throwable $driverError) {
                            $driver = [
                                'id' => (int) $row->driver_id,
                                'name' => 'Service Expert',
                                'phone' => '',
                                'photo' => '',
                                'rating' => '4.8',
                                'review_count' => 0,
                                'profession' => '',
                                'experience' => '',
                                'vehicle_number' => '',
                                'eta_label' => '',
                            ];
                        }
                    }

                    return [
                        'id' => $row->id,
                        'user_id' => $row->user_id,
                        'driver_id' => $row->driver_id,
                        'service_name' => $row->service_name ?? 'Home Service',
                        'address_type' => $row->address_type ?? 'Home',
                        'service_address' => $row->service_address ?? '',
                        'lat' => $row->lat ?? '',
                        'lng' => $row->lng ?? '',
                        'preferred_date' => $row->preferred_date ?? '',
                        'preferred_time' => $row->preferred_time ?? '',
                        'description' => $row->description ?? '',
                        'status' => $row->status ?? 'Pending',
                        'amount' => isset($row->amount) ? (float) $row->amount : null,
                        'payment_status' => $row->payment_status ?? 'pending',
                        'price_breakdown' => null,
                        'otp' => isset($row->otp) ? (string) $row->otp : null,
                        'driver' => $driver,
                        'created_at' => $row->created_at ? (string) $row->created_at : '',
                    ];
                }
            })->values();

            return response()->json([
                'success' => 'success',
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            \Log::error('getHistory failed gracefully', [
                'user_id' => $user_id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => 'success',
                'data' => [],
            ]);
        }
    }

    public function getBookingDetail(Request $request, $id)
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('service_requests')) {
                return response()->json(['success' => 'error', 'message' => 'Service requests unavailable'], 404);
            }

            $userId = $request->header('id_user') ?? $request->input('user_id');
            $booking = ServiceRequest::find($id);

            if (!$booking) {
                return response()->json(['success' => 'error', 'message' => 'Booking not found'], 404);
            }

            if ($userId && (string) $booking->user_id !== (string) $userId && (string) $booking->driver_id !== (string) $userId) {
                $userKeys = [(string) $userId];
                if (\Illuminate\Support\Facades\Schema::hasTable('tj_user_app')) {
                    $user = \Illuminate\Support\Facades\DB::table('tj_user_app')
                        ->where('id', $userId)
                        ->orWhere('ac_no', $userId)
                        ->first();
                    if ($user) {
                        if (!empty($user->id)) $userKeys[] = (string) $user->id;
                        if (!empty($user->ac_no)) $userKeys[] = (string) $user->ac_no;
                    }
                }
                if (!in_array((string) $booking->user_id, $userKeys, true) && !in_array((string) $booking->driver_id, $userKeys, true)) {
                    return response()->json(['success' => 'error', 'message' => 'Unauthorized'], 403);
                }
            }

            if (\Illuminate\Support\Facades\Schema::hasColumn('service_requests', 'otp')) {
                try {
                    $this->ensureServiceOtp($booking);
                    $booking->refresh();
                } catch (\Throwable $e) {
                    \Log::warning('ensureServiceOtp failed in booking detail', [
                        'booking_id' => $booking->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return response()->json([
                'success' => 'success',
                'data' => $this->formatUserBooking($booking),
            ]);
        } catch (\Throwable $e) {
            \Log::error('getBookingDetail failed gracefully', [
                'booking_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => 'error',
                'message' => 'Unable to load booking details',
            ], 200);
        }
    }

    public function getServicePriceEstimate(Request $request)
    {
        $serviceName = trim((string) $request->input('service_name', ''));
        $names = $this->resolveRequestedServiceNames($request, $serviceName);
        if (!$request->has('service_names') && empty($names)) {
            return response()->json(['success' => 'error', 'message' => 'service_name is required'], 422);
        }

        $lat = $request->input('lat');
        $lng = $request->input('lng');
        $latFloat = is_numeric($lat) ? (float) $lat : null;
        $lngFloat = is_numeric($lng) ? (float) $lng : null;

        $nearbyDriverIds = $this->getNearbyProviderDriverIds($latFloat, $lngFloat);

        $lineItems = [];
        $servicesMinTotal = 0.0;
        $servicesMaxTotal = 0.0;

        foreach ($names as $name) {
            $range = $this->findProviderPriceRangeForService($name, $nearbyDriverIds);
            $minPrice = $range['min'];
            $maxPrice = $range['max'];
            $available = $range['available'];

            $lineItems[] = [
                'name' => $name,
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'price' => $minPrice,
                'price_available' => $available,
                'price_label' => $this->formatPriceRangeLabel($minPrice, $maxPrice, $available),
            ];

            if ($available) {
                $servicesMinTotal += $minPrice;
                $servicesMaxTotal += $maxPrice;
            }
        }

        $visitingRange = $this->findVisitingChargeRange($nearbyDriverIds);
        $visitingMin = $visitingRange['min'];
        $visitingMax = $visitingRange['max'];
        $platformFee = 0.0;

        $totalMin = round($servicesMinTotal + $visitingMin + $platformFee, 2);
        $totalMax = round($servicesMaxTotal + $visitingMax + $platformFee, 2);

        return response()->json([
            'success' => 'success',
            'data' => $this->formatPriceEstimateResponse(
                $lineItems,
                $visitingMin,
                $visitingMax,
                $platformFee,
                $servicesMinTotal,
                $servicesMaxTotal,
                $totalMin,
                $totalMax,
                count($nearbyDriverIds)
            ),
        ]);
    }

    private function buildPriceEstimatePayload(array $names, ?float $lat, ?float $lng): array
    {
        $nearbyDriverIds = $this->getNearbyProviderDriverIds($lat, $lng);
        $lineItems = [];
        $servicesMinTotal = 0.0;
        $servicesMaxTotal = 0.0;

        foreach ($names as $name) {
            $range = $this->findProviderPriceRangeForService($name, $nearbyDriverIds);
            $minPrice = $range['min'];
            $maxPrice = $range['max'];
            $available = $range['available'];

            $lineItems[] = [
                'name' => $name,
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'price' => $minPrice,
                'price_available' => $available,
                'price_label' => $this->formatPriceRangeLabel($minPrice, $maxPrice, $available),
            ];

            if ($available) {
                $servicesMinTotal += $minPrice;
                $servicesMaxTotal += $maxPrice;
            }
        }

        $visitingRange = $this->findVisitingChargeRange($nearbyDriverIds);
        $visitingMin = $visitingRange['min'];
        $visitingMax = $visitingRange['max'];
        $platformFee = 0.0;
        $totalMin = round($servicesMinTotal + $visitingMin + $platformFee, 2);
        $totalMax = round($servicesMaxTotal + $visitingMax + $platformFee, 2);

        return $this->formatPriceEstimateResponse(
            $lineItems,
            $visitingMin,
            $visitingMax,
            $platformFee,
            $servicesMinTotal,
            $servicesMaxTotal,
            $totalMin,
            $totalMax,
            count($nearbyDriverIds)
        );
    }

    private function formatPriceEstimateResponse(
        array $lineItems,
        float $visitingMin,
        float $visitingMax,
        float $platformFee,
        float $servicesMinTotal,
        float $servicesMaxTotal,
        float $totalMin,
        float $totalMax,
        int $providersNearby
    ): array {
        return [
            'service_items' => $lineItems,
            'visiting_charge' => $visitingMin,
            'visiting_charge_min' => $visitingMin,
            'visiting_charge_max' => $visitingMax,
            'visiting_charge_label' => $this->formatPriceRangeLabel($visitingMin, $visitingMax, $visitingMin > 0 || $visitingMax > 0),
            'platform_fee' => $platformFee,
            'services_subtotal' => round($servicesMinTotal, 2),
            'services_subtotal_min' => round($servicesMinTotal, 2),
            'services_subtotal_max' => round($servicesMaxTotal, 2),
            'total' => $totalMin,
            'total_min' => $totalMin,
            'total_max' => $totalMax,
            'total_label' => $this->formatPriceRangeLabel($totalMin, $totalMax, $totalMin > 0 || $totalMax > 0),
            'providers_nearby' => $providersNearby,
            'currency' => 'INR',
            'currency_symbol' => '₹',
        ];
    }

    public function payServiceBooking(Request $request)
    {
        $userId = $request->header('id_user') ?? $request->input('user_id');
        $bookingId = $request->input('booking_id');
        $method = strtolower(trim((string) $request->input('payment_method', 'cash')));

        if (empty($userId) || empty($bookingId)) {
            return response()->json(['success' => 'error', 'message' => 'user_id and booking_id are required'], 422);
        }

        $booking = ServiceRequest::find($bookingId);
        if (!$booking || (string) $booking->user_id !== (string) $userId) {
            return response()->json(['success' => 'error', 'message' => 'Booking not found'], 404);
        }

        if ($this->isBookingPaid($booking)) {
            return response()->json([
                'success' => 'success',
                'message' => 'Already paid',
                'data' => $this->formatUserBooking($booking),
            ]);
        }

        $bookingStatus = strtolower(trim((string) $booking->status));
        $payableStatuses = ['in progress', 'in_progress', 'awaiting payment', 'awaiting_payment', 'completed'];
        if (!in_array($bookingStatus, $payableStatuses, true)) {
            return response()->json([
                'success' => 'error',
                'message' => 'Payment is not available for this booking stage yet',
            ], 422);
        }

        $payable = $this->resolveBookingPayableAmount($booking);
        if ($payable <= 0) {
            return response()->json([
                'success' => 'error',
                'message' => 'Payment amount is not available for this booking. Please contact support.',
            ], 422);
        }

        if (\Illuminate\Support\Facades\Schema::hasColumn('service_requests', 'amount')
            && ((float) ($booking->amount ?? 0)) <= 0) {
            $booking->amount = $payable;
        }

        $m = strtolower(trim((string) $method));

        // Calculate payment-method specific taxes
        $taxAmount = 0.0;
        $taxDetails = [];
        if (\Illuminate\Support\Facades\Schema::hasTable('tj_tax')) {
            $taxes = \Illuminate\Support\Facades\DB::table('tj_tax')
                ->where('statut', 'yes')
                ->get();
            foreach ($taxes as $tax) {
                $methods = !empty($tax->applicable_on) ? explode(',', $tax->applicable_on) : ['cash', 'upi', 'wallet', 'online'];
                $applies = in_array($m, $methods) || 
                           ($m === 'upi' && in_array('online', $methods)) || 
                           ($m === 'online' && in_array('upi', $methods)) ||
                           (str_contains($m, 'cash') && in_array('cash', $methods)) ||
                           (str_contains($m, 'wallet') && in_array('wallet', $methods));
                if ($applies) {
                    $val = (float) ($tax->value ?? 0);
                    $tAmt = ($tax->type === 'Percentage') ? round(($payable * $val) / 100, 2) : $val;
                    $taxAmount += $tAmt;
                    $taxDetails[] = [
                        'libelle' => $tax->libelle,
                        'value' => $tax->value,
                        'type' => $tax->type,
                        'amount' => $tAmt,
                    ];
                }
            }
        }
        $totalCharged = round($payable + $taxAmount, 2);

        if (\Illuminate\Support\Facades\Schema::hasColumn('service_requests', 'tax')) {
            $booking->tax = json_encode($taxDetails);
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('service_requests', 'tax_amount')) {
            $booking->tax_amount = $taxAmount;
        }

        if ($m === 'wallet') {
            $walletResult = $this->processServiceWalletPayment($booking, (int) $userId, $totalCharged, $payable);
            if ($walletResult !== true) {
                return response()->json([
                    'success' => 'error',
                    'message' => is_string($walletResult) ? $walletResult : 'Wallet payment failed',
                ], 422);
            }
            $booking->payment_status = 'paid_wallet';
        } elseif (in_array($m, ['upi', 'online', 'razorpay', 'paytm', 'flutterwave', 'stripe', 'card'], true)) {
            $booking->payment_status = 'paid_' . $m;
            try {
                $this->processServiceOnlinePayment($booking, $payable, $m);
            } catch (\Throwable $th) {
                \Illuminate\Support\Facades\Log::error('processServiceOnlinePayment error: ' . $th->getMessage());
            }

            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('tj_transaction')) {
                    $txData = [
                        'amount' => '-' . $totalCharged,
                        'id_user_app' => $userId,
                        'creer' => date('Y-m-d H:i:s'),
                    ];
                    if (\Illuminate\Support\Facades\Schema::hasColumn('tj_transaction', 'deduction_type')) {
                        $txData['deduction_type'] = 'Service Booking';
                    }
                    if (\Illuminate\Support\Facades\Schema::hasColumn('tj_transaction', 'ride_id')) {
                        $txData['ride_id'] = (string) $booking->id;
                    }
                    if (\Illuminate\Support\Facades\Schema::hasColumn('tj_transaction', 'payment_method')) {
                        $txData['payment_method'] = strtoupper($m);
                    }
                    if (\Illuminate\Support\Facades\Schema::hasColumn('tj_transaction', 'payment_status')) {
                        $txData['payment_status'] = 'success';
                    }
                    \Illuminate\Support\Facades\DB::table('tj_transaction')->insert($txData);
                }
            } catch (\Throwable $th) {
                \Illuminate\Support\Facades\Log::error('tj_transaction error: ' . $th->getMessage());
            }
        } else {
            $booking->payment_status = 'paid_cash';
        }

        if (\Illuminate\Support\Facades\Schema::hasColumn('service_requests', 'payment_status')) {
            $booking->save();
        }

        // Trigger dynamic referral cashback reward based on admin rules
        try {
            \App\Services\ReferralRewardService::processReward(
                (int)$userId,
                'customer',
                'service_booking',
                $payable,
                "Service Booking ({$booking->service_name})"
            );
        } catch (\Exception $ex) {
            \Illuminate\Support\Facades\Log::error("Service booking referral reward error: " . $ex->getMessage());
        }

        // Trigger customer & provider payment notifications
        try {
            $this->sendServiceNotification(
                (int) $userId,
                'customer',
                "Payment Successful: ₹{$payable}",
                "Payment of ₹{$payable} recorded for {$booking->service_name}. Thank you!",
                ['booking_id' => (string) $booking->id, 'payment_status' => $booking->payment_status]
            );

            if (!empty($booking->driver_id)) {
                $this->sendServiceNotification(
                    (int) $booking->driver_id,
                    'driver',
                    "Payment Received: ₹{$payable}",
                    "Customer has paid ₹{$payable} for {$booking->service_name}.",
                    ['booking_id' => (string) $booking->id, 'payment_status' => $booking->payment_status]
                );
            }
        } catch (\Throwable $pNotif) {
            \Log::error('payServiceBooking notification error: ' . $pNotif->getMessage());
        }

        // Clear any previous pending due once payment is successfully processed
        try {
            if (\Illuminate\Support\Facades\Schema::hasColumn('tj_user_app', 'pending_due')) {
                \Illuminate\Support\Facades\DB::table('tj_user_app')
                    ->where('id', $userId)
                    ->update(['pending_due' => 0.00]);
            }
        } catch (\Throwable $clearEx) {}

        return response()->json([
            'success' => 'success',
            'message' => 'Payment recorded successfully',
            'data' => $this->formatUserBooking($booking->fresh()),
        ]);
    }

    public function cancelServiceBooking(Request $request)
    {
        $userId = $request->header('id_user') ?? $request->input('user_id');
        $bookingId = $request->input('booking_id');

        if (empty($userId) || empty($bookingId)) {
            return response()->json(['success' => 'error', 'message' => 'user_id and booking_id are required'], 422);
        }

        $booking = ServiceRequest::find($bookingId);
        if (!$booking || (string) $booking->user_id !== (string) $userId) {
            return response()->json(['success' => 'error', 'message' => 'Booking not found'], 404);
        }

        $status = strtolower(trim((string) $booking->status));
        $blocked = [
            'in progress',
            'in_progress',
            'started',
            'on ride',
            'onride',
            'on_ride',
            'awaiting payment',
            'awaiting_payment',
            'completed',
            'paid',
            'cancelled',
            'canceled',
            'rejected'
        ];

        if (in_array($status, $blocked, true) || !empty($booking->otp_verified_at)) {
            return response()->json([
                'success' => 'error',
                'message' => 'Service is already in progress, completed, or cannot be cancelled.',
                'error'   => 'Service is already in progress, completed, or cannot be cancelled.'
            ], 400);
        }

        $booking->status = 'Cancelled';
        $booking->save();

        // If driver was assigned, add cancellation platform fee to user's pending due for next bill
        if (!empty($booking->driver_id)) {
            try {
                if (!\Illuminate\Support\Facades\Schema::hasColumn('tj_user_app', 'pending_due')) {
                    \Illuminate\Support\Facades\Schema::table('tj_user_app', function ($table) {
                        $table->decimal('pending_due', 10, 2)->default(0.00)->nullable();
                    });
                }
                \Illuminate\Support\Facades\DB::table('tj_user_app')
                    ->where('id', $userId)
                    ->increment('pending_due', 50.00);

                if (\Illuminate\Support\Facades\Schema::hasTable('tj_transaction')) {
                    \Illuminate\Support\Facades\DB::table('tj_transaction')->insert([
                        'amount' => '-50.00',
                        'id_user_app' => $userId,
                        'deduction_type' => 'Cancellation Fee (Added to Next Bill)',
                        'ride_id' => (string) $booking->id,
                        'payment_method' => 'Next Bill',
                        'payment_status' => 'pending_due',
                        'creer' => now(),
                        'modifier' => now(),
                    ]);
                }
            } catch (\Throwable $dueEx) {
                \Log::error('cancelServiceBooking pending_due error: ' . $dueEx->getMessage());
            }
        }

        // Notify Driver if assigned
        try {
            if (!empty($booking->driver_id)) {
                $this->sendServiceNotification(
                    (int) $booking->driver_id,
                    'driver',
                    "Booking Cancelled",
                    "Customer cancelled booking #{$booking->id} for {$booking->service_name}.",
                    ['booking_id' => (string) $booking->id, 'status' => 'Cancelled']
                );
            }
        } catch (\Throwable $cNotif) {
            \Log::error('cancelServiceBooking notification error: ' . $cNotif->getMessage());
        }

        return response()->json([
            'success' => 'success',
            'message' => 'Booking cancelled. Cancellation charges will be added to your next bill.',
            'data' => $this->formatUserBooking($booking->fresh()),
        ]);
    }

    private function sendServiceNotification($targetId, $targetType, $title, $body, $customData = [])
    {
        try {
            $token = null;
            if ($targetType === 'customer') {
                $token = \Illuminate\Support\Facades\DB::table('tj_user_app')->where('id', $targetId)->value('fcm_id');
            } elseif ($targetType === 'driver') {
                $token = \Illuminate\Support\Facades\DB::table('tj_conducteur')->where('id', $targetId)->value('fcm_id');
            }

            $payload = array_merge([
                'title' => $title,
                'body' => $body,
                'tag' => 'homeservicenotif',
                'type' => 'homeservice',
            ], $customData);

            if (!empty($token)) {
                \App\Http\Controllers\API\v1\GcmController::sendNotification($token, $payload);
            }

            $dateNow = date('Y-m-d H:i:s');
            if (\Illuminate\Support\Facades\Schema::hasTable('tj_notification')) {
                \Illuminate\Support\Facades\DB::table('tj_notification')->insert([
                    'titre' => $title,
                    'message' => $body,
                    'statut' => 'yes',
                    'creer' => $dateNow,
                    'modifier' => $dateNow,
                    'to_id' => $targetId,
                    'from_id' => 0,
                    'type' => 'homeservice'
                ]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("sendServiceNotification error: " . $e->getMessage());
        }
    }

    private function notifyMatchingServiceProviders(ServiceRequest $serviceRequest, ?float $lat = null, ?float $lng = null): void
    {
        try {
            $serviceName = (string) $serviceRequest->service_name;
            $city = (string) ($serviceRequest->city ?? '');
            $title = "New Service Request: {$serviceName}";
            $body = "New booking #{$serviceRequest->id}" . ($city ? " in {$city}" : "") . ". Tap to review and accept.";
            $customData = [
                'booking_id' => (string) $serviceRequest->id,
                'tag' => 'homeservicerequest',
                'type' => 'homeservice',
                'service_name' => $serviceName,
                'statut' => 'new',
                'sound' => 'mysound',
                'channel_id' => 'ride_requests',
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ];

            // 1. Direct Booking: If user selected a specific provider, notify ONLY that provider
            if (!empty($serviceRequest->driver_id)) {
                $this->sendServiceNotification(
                    (int) $serviceRequest->driver_id,
                    'driver',
                    $title,
                    $body,
                    $customData
                );
                return;
            }

            // 2. Open Request: Find ONLY online matching home service providers (category/skills + radius)
            $allDrivers = \Illuminate\Support\Facades\DB::table('tj_conducteur')
                ->whereNotNull('fcm_id')
                ->where('fcm_id', '!=', '')
                ->where('fcm_id', '!=', 'null')
                ->select('id', 'fcm_id', 'latitude', 'longitude', 'online')
                ->get();

            $notifiedCount = 0;
            foreach ($allDrivers as $drv) {
                $profile = $this->buildDriverBookingProfile((int) $drv->id);

                // Only send to drivers who have completed onboarding and offer home services
                if (!$profile['has_onboarding'] || !$profile['is_home_service_provider']) {
                    continue;
                }

                // Driver must be online
                if (!$profile['driver_online']) {
                    continue;
                }

                // Check category / skill matching with the requested service
                if (!$this->serviceMatchesDriverProfile($serviceName, $profile)) {
                    continue;
                }

                // Check distance if coordinates are present (within 30 km)
                if ($lat !== null && $lng !== null && $profile['has_location']) {
                    $distanceKm = $this->distanceKmFromDriver(
                        $profile['driver_lat'],
                        $profile['driver_lng'],
                        $lat,
                        $lng
                    );
                    if (!$this->bookingWithinDriverRadius($distanceKm)) {
                        continue;
                    }
                }

                $this->sendServiceNotification(
                    (int) $drv->id,
                    'driver',
                    $title,
                    $body,
                    $customData
                );
                $notifiedCount++;
            }

            \Log::info("Home Service #{$serviceRequest->id} ('{$serviceName}') notified {$notifiedCount} matching online providers.");
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("notifyMatchingServiceProviders error: " . $e->getMessage());
        }
    }

    private function decodePriceBreakdown($booking): ?array
    {
        if (!\Illuminate\Support\Facades\Schema::hasColumn('service_requests', 'price_breakdown')
            || empty($booking->price_breakdown ?? null)) {
            return null;
        }

        $decoded = json_decode($booking->price_breakdown, true);

        return is_array($decoded) ? $decoded : null;
    }

    private function applyDriverBillToBooking(ServiceRequest $booking, Request $request): void
    {
        if (!\Illuminate\Support\Facades\Schema::hasColumn('service_requests', 'price_breakdown')) {
            return;
        }

        $existingBreakdown = $this->decodePriceBreakdown($booking) ?? [];
        $breakdown = $existingBreakdown;

        $visitCharge = max(0, (float) $request->input('visiting_charge', 0));
        if ($visitCharge <= 0) {
            $visitCharge = (float) ($existingBreakdown['visiting_charge_min'] ?? $existingBreakdown['visiting_charge'] ?? 0);
        }
        if ($visitCharge <= 0) {
            foreach ($existingBreakdown['service_items'] ?? [] as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $name = strtolower((string) ($item['name'] ?? ''));
                if (str_contains($name, 'visit') && str_contains($name, 'charge')) {
                    $visitCharge = max($visitCharge, (float) ($item['min_price'] ?? $item['price'] ?? 0));
                }
            }
        }

        $platformFee = 0;

        $materialCost = max(0, (float) $request->input('material_cost', 0));
        $serviceItemsInput = $request->input('service_items');

        $originalAmount = (float) ($booking->amount ?? 0);
        $originalBreakdown = $existingBreakdown;
        $originalLabour = max(0, $originalAmount - $visitCharge - $materialCost);
        if ($originalLabour <= 0 && is_array($originalBreakdown)) {
            $originalLabour = (float) ($originalBreakdown['services_subtotal'] ?? $originalBreakdown['services_subtotal_min'] ?? 0);
        }

        if (is_array($serviceItemsInput) && !empty($serviceItemsInput)) {
            $normalized = [];
            $unpricedCount = 0;
            foreach ($serviceItemsInput as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $name = trim((string) ($item['name'] ?? ''));
                if ($name === '') {
                    continue;
                }
                $lowerName = strtolower($name);
                if (str_contains($lowerName, 'visit') && str_contains($lowerName, 'charge')) {
                    $visitCharge = max($visitCharge, (float) ($item['price'] ?? $item['min_price'] ?? 0));
                    continue;
                }
                $price = max(0, (float) ($item['price'] ?? $item['min_price'] ?? 0));
                if ($price <= 0) {
                    $unpricedCount++;
                }
                $normalized[] = [
                    'name' => $name,
                    'price' => $price,
                    'min_price' => $price,
                    'max_price' => $price,
                    'price_available' => true,
                    'price_label' => '₹' . (int) $price,
                ];
            }

            if ($unpricedCount > 0 && $originalLabour > 0 && count($normalized) > 0) {
                $eachPrice = round($originalLabour / count($normalized), 2);
                foreach ($normalized as &$nItem) {
                    if ($nItem['price'] <= 0) {
                        $nItem['price'] = $eachPrice;
                        $nItem['min_price'] = $eachPrice;
                        $nItem['max_price'] = $eachPrice;
                        $nItem['price_label'] = '₹' . (int) $eachPrice;
                    }
                }
                unset($nItem);
            }

            if (!empty($normalized)) {
                $breakdown['service_items'] = $normalized;
                if (\Illuminate\Support\Facades\Schema::hasColumn('service_requests', 'service_name')) {
                    $booking->service_name = implode("\n", array_column($normalized, 'name'));
                }
            }
        }

        $breakdown['material_cost'] = $materialCost;
        $breakdown['visiting_charge'] = $visitCharge;
        $breakdown['visiting_charge_min'] = $visitCharge;
        $breakdown['visiting_charge_max'] = $visitCharge;
        if ($visitCharge > 0) {
            $breakdown['visiting_charge_label'] = '₹' . (int) $visitCharge;
        }
        $breakdown['platform_fee'] = 0;

        $servicesTotal = 0.0;
        foreach ($breakdown['service_items'] ?? [] as $item) {
            if (!is_array($item)) {
                continue;
            }
            $servicesTotal += (float) ($item['min_price'] ?? $item['price'] ?? 0);
        }

        $total = round($servicesTotal + $visitCharge + $materialCost, 2);

        $breakdown['services_subtotal'] = round($servicesTotal, 2);
        $breakdown['services_subtotal_min'] = round($servicesTotal, 2);
        $breakdown['total'] = $total;
        $breakdown['total_min'] = $total;
        $breakdown['total_max'] = $total;
        $breakdown['total_label'] = '₹' . (int) $total;

        $booking->price_breakdown = json_encode($breakdown);

        if (\Illuminate\Support\Facades\Schema::hasColumn('service_requests', 'amount')) {
            $booking->amount = $total;
        }
    }

    private function bindAcceptedDriverPricingToBooking(ServiceRequest $booking, int $driverId): void
    {
        $breakdown = $this->decodePriceBreakdown($booking) ?? [];

        // 1. Get driver's visiting charge from driver_service_pricing
        $driverVisitingCharge = null;
        if (\Illuminate\Support\Facades\Schema::hasTable('driver_service_pricing')) {
            $pricingRow = \Illuminate\Support\Facades\DB::table('driver_service_pricing')
                ->where('driver_id', $driverId)
                ->where('visiting_charge', '>', 0)
                ->first();
            if ($pricingRow) {
                $driverVisitingCharge = (float) $pricingRow->visiting_charge;
            }
        }

        // 2. Get driver's configured skill items/prices from driver_service_items
        $driverItems = [];
        if (\Illuminate\Support\Facades\Schema::hasTable('driver_service_items')) {
            $driverItems = \Illuminate\Support\Facades\DB::table('driver_service_items')
                ->where('driver_id', $driverId)
                ->where('price', '>', 0)
                ->get();
        }

        // If breakdown service_items is empty, populate from service_name
        if (!isset($breakdown['service_items']) || !is_array($breakdown['service_items']) || empty($breakdown['service_items'])) {
            $names = $this->parseRequestedServiceNames((string) ($booking->service_name ?? ''));
            $breakdownItems = [];
            foreach ($names as $name) {
                $breakdownItems[] = [
                    'name' => $name,
                    'min_price' => 0.0,
                    'max_price' => 0.0,
                    'price' => 0.0,
                    'price_available' => false,
                    'price_label' => 'Rate on visit',
                ];
            }
            $breakdown['service_items'] = $breakdownItems;
        }

        // 3. Update each service_item with driver's specific price
        if (isset($breakdown['service_items']) && is_array($breakdown['service_items'])) {
            foreach ($breakdown['service_items'] as &$item) {
                if (!is_array($item)) {
                    continue;
                }
                $name = trim((string) ($item['name'] ?? ''));
                if ($name === '') {
                    continue;
                }

                $matchedDriverPrice = null;
                foreach ($driverItems as $dItem) {
                    if ($this->serviceNameMatches($name, (string) $dItem->service_name)) {
                        $matchedDriverPrice = (float) $dItem->price;
                        break;
                    }
                }

                if ($matchedDriverPrice === null || $matchedDriverPrice <= 0) {
                    $benchmark = $this->getCatalogBenchmarkPrice($name);
                    $matchedDriverPrice = (float) ($benchmark['min'] ?? 0);
                }

                if ($matchedDriverPrice !== null && $matchedDriverPrice > 0) {
                    $item['price'] = $matchedDriverPrice;
                    $item['min_price'] = $matchedDriverPrice;
                    $item['max_price'] = $matchedDriverPrice;
                    $item['price_available'] = true;
                    $item['price_label'] = '₹' . (int) $matchedDriverPrice;
                }
            }
            unset($item);
        }

        // 4. Update visiting charge with driver's specific visiting charge
        if ($driverVisitingCharge !== null && $driverVisitingCharge > 0) {
            $breakdown['visiting_charge'] = $driverVisitingCharge;
            $breakdown['visiting_charge_min'] = $driverVisitingCharge;
            $breakdown['visiting_charge_max'] = $driverVisitingCharge;
            $breakdown['visiting_charge_label'] = '₹' . (int) $driverVisitingCharge;
        } elseif (empty($breakdown['visiting_charge'])) {
            $visitRange = $this->findVisitingChargeRange([$driverId]);
            $visitCharge = (float) ($visitRange['min'] ?? 0);
            $breakdown['visiting_charge'] = $visitCharge;
            $breakdown['visiting_charge_min'] = $visitCharge;
            $breakdown['visiting_charge_max'] = $visitCharge;
            $breakdown['visiting_charge_label'] = '₹' . (int) $visitCharge;
        }

        // 5. Recalculate totals
        $servicesTotal = 0.0;
        foreach ($breakdown['service_items'] ?? [] as $item) {
            if (!is_array($item)) {
                continue;
            }
            $servicesTotal += (float) ($item['min_price'] ?? $item['price'] ?? 0);
        }

        $visiting = (float) ($breakdown['visiting_charge'] ?? 0);
        $material = (float) ($breakdown['material_cost'] ?? 0);
        $platformFee = (float) ($breakdown['platform_fee'] ?? 0);

        $total = round($servicesTotal + $visiting + $material + $platformFee, 2);

        $breakdown['services_subtotal'] = round($servicesTotal, 2);
        $breakdown['services_subtotal_min'] = round($servicesTotal, 2);
        $breakdown['services_subtotal_max'] = round($servicesTotal, 2);
        $breakdown['services_subtotal_label'] = '₹' . (int) $servicesTotal;
        $breakdown['total'] = $total;
        $breakdown['total_min'] = $total;
        $breakdown['total_max'] = $total;
        $breakdown['total_label'] = '₹' . (int) $total;

        if (\Illuminate\Support\Facades\Schema::hasColumn('service_requests', 'price_breakdown')) {
            $booking->price_breakdown = json_encode($breakdown);
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('service_requests', 'amount')) {
            $booking->amount = $total;
        }

        $booking->save();
    }

    private function resolveBookingPayableAmount($booking): float
    {
        $amount = \Illuminate\Support\Facades\Schema::hasColumn('service_requests', 'amount')
            ? (float) ($booking->amount ?? 0)
            : 0.0;

        $breakdown = $this->decodePriceBreakdown($booking);
        if ($amount <= 0 && is_array($breakdown)) {
            $amount = (float) ($breakdown['total_min'] ?? $breakdown['total'] ?? 0);
        }

        if ($amount <= 0 && is_array($breakdown)) {
            $servicesTotal = 0.0;
            foreach ($breakdown['service_items'] ?? [] as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $servicesTotal += (float) ($item['min_price'] ?? $item['price'] ?? 0);
            }
            $visitCharge = (float) ($breakdown['visiting_charge_min'] ?? $breakdown['visiting_charge'] ?? 0);
            $materialCost = (float) ($breakdown['material_cost'] ?? 0);
            $amount = $servicesTotal + $visitCharge + $materialCost;
        }

        return round(max(0, $amount), 2);
    }

    private function isBookingPaid(ServiceRequest $booking): bool
    {
        $status = strtolower(trim((string) ($booking->payment_status ?? 'pending')));

        // Exact known values
        if (in_array($status, ['paid', 'paid_wallet', 'paid_cash', 'paid_upi', 'yes', 'success'], true)) {
            return true;
        }
        // Any payment method stored as 'paid_<method>' e.g. paid_razorpay, paid_online, paid_stripe
        if (str_starts_with($status, 'paid_')) {
            return true;
        }
        return false;
    }

    /**
     * @return true|string True on success, error message string on failure.
     */
    private function processServiceWalletPayment(ServiceRequest $booking, int $userId, float $totalCharged, float $payable = 0.0)
    {
        if ($payable <= 0) {
            $payable = $totalCharged;
        }

        if (!\Illuminate\Support\Facades\Schema::hasTable('tj_user_app')) {
            return 'Wallet service is unavailable';
        }

        $user = \Illuminate\Support\Facades\DB::table('tj_user_app')
            ->select('amount')
            ->where('id', $userId)
            ->first();

        if (!$user) {
            return 'User wallet not found';
        }

        $balance = (float) ($user->amount ?? 0);
        if ($balance < $totalCharged) {
            return 'Insufficient wallet balance. Please add money or choose another payment method.';
        }

        \Illuminate\Support\Facades\DB::table('tj_user_app')
            ->where('id', $userId)
            ->update(['amount' => round($balance - $totalCharged, 2)]);

        if (!empty($booking->driver_id) && \Illuminate\Support\Facades\Schema::hasTable('tj_conducteur')) {
            $commissionAmount = round($payable * 0.10, 2);
            $driverShare = max(0, round($payable - $commissionAmount, 2));
            $driver = \Illuminate\Support\Facades\DB::table('tj_conducteur')
                ->where('id', $booking->driver_id)
                ->first();

            if ($driver) {
                $driverBalance = (float) ($driver->amount ?? 0);
                $driverEarn = (float) ($driver->earn_amount ?? 0);
                $updateData = ['amount' => round($driverBalance + $driverShare, 2)];
                if (\Illuminate\Support\Facades\Schema::hasColumn('tj_conducteur', 'earn_amount')) {
                    $updateData['earn_amount'] = (string) round($driverEarn + $payable, 2);
                }
                \Illuminate\Support\Facades\DB::table('tj_conducteur')
                    ->where('id', $booking->driver_id)
                    ->update($updateData);
            }
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('tj_transaction')) {
            \Illuminate\Support\Facades\DB::table('tj_transaction')->insert([
                'amount' => '-' . $totalCharged,
                'deduction_type' => 'Service Booking',
                'ride_id' => (string) $booking->id,
                'payment_method' => 'Wallet',
                'payment_status' => 'success',
                'id_user_app' => $userId,
                'creer' => now(),
                'modifier' => now(),
            ]);
        }

        if (!empty($booking->driver_id) && \Illuminate\Support\Facades\Schema::hasTable('tj_conducteur_transaction')) {
            $serviceTitle = !empty($booking->service_name) ? trim($booking->service_name) : 'Home Service';
            $commAmount = round($payable * 0.10, 2);

            // 1. Gross Service Earning
            \Illuminate\Support\Facades\DB::table('tj_conducteur_transaction')->insert([
                'amount'         => (string) $payable,
                'id_conducteur'  => $booking->driver_id,
                'id_ride'        => (string) $booking->id,
                'payment_method' => 'Wallet',
                'deduction_type' => 'Service Booking',
                'note'           => 'Received payment for ' . $serviceTitle . ' (Booking #' . $booking->id . ')',
                'creer'          => now(),
                'modifier'       => now(),
            ]);

            // 2. Admin Commission Deduction
            if ($commAmount > 0) {
                \Illuminate\Support\Facades\DB::table('tj_conducteur_transaction')->insert([
                    'amount'         => '-' . $commAmount,
                    'id_conducteur'  => $booking->driver_id,
                    'id_ride'        => (string) $booking->id,
                    'payment_method' => 'Commission',
                    'deduction_type' => 'Commission',
                    'note'           => 'Admin Commission (10%) for ' . $serviceTitle . ' (Booking #' . $booking->id . ')',
                    'creer'          => now(),
                    'modifier'       => now(),
                ]);
            }
        }

        return true;
    }

    /**
     * Credit the driver's wallet with net earnings (payable minus admin commission)
     * when customer pays via online/gateway/UPI methods to admin account.
     */
    private function processServiceOnlinePayment(ServiceRequest $booking, float $payable, string $paymentMethod): void
    {
        if (empty($booking->driver_id)) {
            return;
        }

        if (!\Illuminate\Support\Facades\Schema::hasTable('tj_conducteur')) {
            return;
        }

        // Read commission config from admin panel
        $commissionValue = 10.0;
        $commissionType = 'percentage';

        if (\Illuminate\Support\Facades\Schema::hasTable('tj_commission')) {
            $commissionRow = \Illuminate\Support\Facades\DB::table('tj_commission')
                ->where('statut', 'active')
                ->first();
            if (!$commissionRow) {
                $commissionRow = \Illuminate\Support\Facades\DB::table('tj_commission')->first();
            }
            if ($commissionRow) {
                $commissionValue = (float) ($commissionRow->value ?? 10.0);
                $commissionType = strtolower(trim((string) ($commissionRow->type ?? 'percentage')));
            }
        }

        if ($commissionType === 'fixed') {
            $commissionAmount = $commissionValue;
        } else {
            $commissionAmount = round($payable * ($commissionValue / 100), 2);
        }

        $driverShare = max(0, round($payable - $commissionAmount, 2));

        // Credit driver's wallet with net earnings & accumulate earn_amount
        $driver = \Illuminate\Support\Facades\DB::table('tj_conducteur')
            ->where('id', $booking->driver_id)
            ->first();

        if ($driver) {
            $currentBalance = (float) ($driver->amount ?? 0);
            $currentEarn = (float) ($driver->earn_amount ?? 0);
            $updateData = ['amount' => round($currentBalance + $driverShare, 2)];
            if (\Illuminate\Support\Facades\Schema::hasColumn('tj_conducteur', 'earn_amount')) {
                $updateData['earn_amount'] = (string) round($currentEarn + $payable, 2);
            }
            \Illuminate\Support\Facades\DB::table('tj_conducteur')
                ->where('id', $booking->driver_id)
                ->update($updateData);
        }

        // Log transactions for driver
        if (\Illuminate\Support\Facades\Schema::hasTable('tj_conducteur_transaction')) {
            $serviceTitle = !empty($booking->service_name) ? trim($booking->service_name) : 'Home Service';
            $methodLabel = strtoupper($paymentMethod);

            // 1. Gross Service Earning
            \Illuminate\Support\Facades\DB::table('tj_conducteur_transaction')->insert([
                'amount'         => (string) $payable,
                'id_conducteur'  => $booking->driver_id,
                'id_ride'        => (string) $booking->id,
                'payment_method' => $methodLabel,
                'deduction_type' => 'Service Booking',
                'note'           => 'Received payment via ' . $methodLabel . ' for ' . $serviceTitle . ' (Booking #' . $booking->id . ')',
                'creer'          => now(),
                'modifier'       => now(),
            ]);

            // 2. Admin Commission Deduction
            if ($commissionAmount > 0) {
                \Illuminate\Support\Facades\DB::table('tj_conducteur_transaction')->insert([
                    'amount'         => '-' . $commissionAmount,
                    'id_conducteur'  => $booking->driver_id,
                    'id_ride'        => (string) $booking->id,
                    'payment_method' => 'Commission',
                    'deduction_type' => 'Commission',
                    'note'           => 'Admin Commission (' . $commissionValue . ($commissionType === 'fixed' ? ' fixed' : '%') . ') for ' . $serviceTitle . ' (Booking #' . $booking->id . ')',
                    'creer'          => now(),
                    'modifier'       => now(),
                ]);
            }
        }
    }

    /**
     * Deduct admin commission from the driver's wallet on service booking completion.
     * Allows the wallet to go negative (debt). The driver must clear the debt before
     * accepting new bookings.
     */
    private function deductServiceCommission(ServiceRequest $booking): void
    {
        if (empty($booking->driver_id)) {
            return;
        }

        // Read commission config from admin panel (first active row, fallback to first row)
        $commission = null;
        if (\Illuminate\Support\Facades\Schema::hasTable('tj_commission')) {
            $commission = \Illuminate\Support\Facades\DB::table('tj_commission')
                ->where('statut', 'active')
                ->first();
            if (!$commission) {
                $commission = \Illuminate\Support\Facades\DB::table('tj_commission')->first();
            }
        }

        $bookingAmount = (float) ($booking->amount ?? $booking->final_total ?? 0);
        if ($bookingAmount <= 0) {
            $bookingAmount = $this->resolveBookingPayableAmount($booking);
        }
        if ($bookingAmount <= 0) {
            return;
        }

        $commissionValue = $commission ? (float) ($commission->value ?? 10.0) : 10.0;
        $commissionType  = $commission ? strtolower(trim((string) ($commission->type ?? 'percentage'))) : 'percentage';

        if ($commissionType === 'fixed') {
            $commissionAmount = $commissionValue;
        } else {
            $commissionAmount = round($bookingAmount * ($commissionValue / 100), 2);
        }

        // Calculate cash applicable taxes
        $cashTaxAmount = 0.0;
        $cashTaxDetails = [];
        if (\Illuminate\Support\Facades\Schema::hasTable('tj_tax')) {
            $taxes = \Illuminate\Support\Facades\DB::table('tj_tax')
                ->where('statut', 'yes')
                ->get();
            foreach ($taxes as $tax) {
                $methods = !empty($tax->applicable_on) ? explode(',', $tax->applicable_on) : ['cash', 'upi', 'wallet', 'online'];
                if (in_array('cash', $methods)) {
                    $val = (float) ($tax->value ?? 0);
                    $tAmt = ($tax->type === 'Percentage') ? round(($bookingAmount * $val) / 100, 2) : $val;
                    $cashTaxAmount += $tAmt;
                    $cashTaxDetails[] = [
                        'libelle' => $tax->libelle,
                        'value' => $tax->value,
                        'type' => $tax->type,
                        'amount' => $tAmt,
                    ];
                }
            }
        }

        if (\Illuminate\Support\Facades\Schema::hasColumn('service_requests', 'tax')) {
            $booking->tax = json_encode($cashTaxDetails);
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('service_requests', 'tax_amount')) {
            $booking->tax_amount = $cashTaxAmount;
        }
        $booking->save();

        $totalCashCollected = round($bookingAmount + $cashTaxAmount, 2);
        $totalDeduction = round($commissionAmount + $cashTaxAmount, 2);

        if ($totalDeduction <= 0) {
            return;
        }

        if (!\Illuminate\Support\Facades\Schema::hasTable('tj_conducteur')) {
            return;
        }

        // Deduct from driver wallet (commission + cash taxes) — allow negative (debt)
        $driver = \Illuminate\Support\Facades\DB::table('tj_conducteur')
            ->where('id', $booking->driver_id)
            ->first();

        if (!$driver) {
            return;
        }

        $currentBalance = (float) ($driver->amount ?? 0);
        $currentEarn = (float) ($driver->earn_amount ?? 0);
        $newBalance = round($currentBalance - $totalDeduction, 2);

        $updateData = ['amount' => $newBalance];
        if (\Illuminate\Support\Facades\Schema::hasColumn('tj_conducteur', 'earn_amount')) {
            $updateData['earn_amount'] = (string) round($currentEarn + $bookingAmount, 2);
        }

        \Illuminate\Support\Facades\DB::table('tj_conducteur')
            ->where('id', $booking->driver_id)
            ->update($updateData);

        // Record transactions for driver
        if (\Illuminate\Support\Facades\Schema::hasTable('tj_conducteur_transaction')) {
            $serviceTitle = !empty($booking->service_name) ? trim($booking->service_name) : 'Home Service';

            // 1. Check if cash earning row exists; if not, add it
            $hasEarningRow = \Illuminate\Support\Facades\DB::table('tj_conducteur_transaction')
                ->where('id_conducteur', $booking->driver_id)
                ->where('id_ride', (string) $booking->id)
                ->where('payment_method', '!=', 'Commission')
                ->where('amount', '>', 0)
                ->exists();

            if (!$hasEarningRow) {
                \Illuminate\Support\Facades\DB::table('tj_conducteur_transaction')->insert([
                    'amount'         => (string) $totalCashCollected,
                    'id_conducteur'  => $booking->driver_id,
                    'id_ride'        => (string) $booking->id,
                    'payment_method' => 'Cash',
                    'deduction_type' => 'Service Booking',
                    'note'           => 'Received cash payment (including ₹' . $cashTaxAmount . ' taxes) for ' . $serviceTitle . ' (Booking #' . $booking->id . ')',
                    'creer'          => now(),
                    'modifier'       => now(),
                ]);
            }

            // 2. Add commission deduction row
            if ($commissionAmount > 0) {
                \Illuminate\Support\Facades\DB::table('tj_conducteur_transaction')->insert([
                    'amount'         => '-' . $commissionAmount,
                    'payment_method' => 'Commission',
                    'deduction_type' => 'Commission',
                    'id_conducteur'  => $booking->driver_id,
                    'id_ride'        => (string) $booking->id,
                    'note'           => 'Service Commission (' . $commissionValue . ($commissionType === 'fixed' ? ' fixed' : '%') . ') - Booking #' . $booking->id,
                    'creer'          => now(),
                    'modifier'       => now(),
                ]);
            }

            // 3. Add tax deduction row (tax collected in cash on behalf of platform)
            if ($cashTaxAmount > 0) {
                \Illuminate\Support\Facades\DB::table('tj_conducteur_transaction')->insert([
                    'amount'         => '-' . $cashTaxAmount,
                    'payment_method' => 'Tax Deduction',
                    'deduction_type' => 'Tax',
                    'id_conducteur'  => $booking->driver_id,
                    'id_ride'        => (string) $booking->id,
                    'note'           => 'GST / Platform Taxes collected in cash - Booking #' . $booking->id,
                    'creer'          => now(),
                    'modifier'       => now(),
                ]);
            }
        }
    }

    /**
     * GET /api/v1/driver/wallet-status
     * Returns the driver's current wallet balance and whether they have a debt.
     */
    public function getDriverWalletStatus(\Illuminate\Http\Request $request)
    {
        $driverId = $request->query('id_driver') ?? $request->input('id_driver');
        if (!$driverId) {
            return response()->json(['success' => 'error', 'message' => 'id_driver required'], 422);
        }

        if (!\Illuminate\Support\Facades\Schema::hasTable('tj_conducteur')) {
            return response()->json(['success' => 'error', 'message' => 'Driver table unavailable'], 500);
        }

        $driver = \Illuminate\Support\Facades\DB::table('tj_conducteur')
            ->select('amount')
            ->where('id', $driverId)
            ->first();

        if (!$driver) {
            return response()->json(['success' => 'error', 'message' => 'Driver not found'], 404);
        }

        $rawAmount = (float) ($driver->amount ?? 0);
        
        $rideEarnings = \Illuminate\Support\Facades\DB::table('tj_requete')
            ->where('id_conducteur', $driverId)
            ->where('statut', 'completed')
            ->sum('montant');

        $parcelEarnings = 0;
        if (\Illuminate\Support\Facades\Schema::hasTable('parcel_orders')) {
            $parcelEarnings = \Illuminate\Support\Facades\DB::table('parcel_orders')
                ->where('id_conducteur', $driverId)
                ->where('status', 'completed')
                ->sum('amount');
        }

        $serviceEarnings = 0;
        if (\Illuminate\Support\Facades\Schema::hasTable('service_requests')) {
            $serviceEarnings = \Illuminate\Support\Facades\DB::table('service_requests')
                ->where('driver_id', $driverId)
                ->whereIn('status', ['Completed', 'completed'])
                ->sum('amount');
        }

        $calcEarn = round(floatval($rideEarnings) + floatval($parcelEarnings) + floatval($serviceEarnings), 2);

        $balance = $rawAmount;
        $hasDebt = $balance < 0;

        return response()->json([
            'success'        => 'success',
            'wallet_balance' => $balance,
            'total_earnings' => $calcEarn,
            'has_debt'       => $hasDebt,
            'debt_amount'    => $hasDebt ? abs($balance) : 0,
        ]);
    }

    private function formatUserBooking(ServiceRequest $booking): array
    {
        $driver = null;
        if (!empty($booking->driver_id)) {
            try {
                $driver = $this->resolveDriverPublicProfile((int) $booking->driver_id);
            } catch (\Throwable $e) {
                \Log::warning('resolveDriverPublicProfile failed', [
                    'booking_id' => $booking->id,
                    'driver_id' => $booking->driver_id,
                    'error' => $e->getMessage(),
                ]);
                $driver = [
                    'id' => (int) $booking->driver_id,
                    'name' => 'Service Expert',
                    'phone' => '',
                    'photo' => '',
                    'rating' => '4.8',
                    'review_count' => 0,
                    'profession' => '',
                    'experience' => '',
                    'vehicle_number' => '',
                    'eta_label' => '20 - 25 mins',
                ];
            }
        }

        $customer = null;
        if (!empty($booking->user_id)) {
            try {
                $customer = $this->resolveCustomerPublicProfile((int) $booking->user_id);
            } catch (\Throwable $e) {
                // keep default
            }
        }

        $breakdown = null;
        if (\Illuminate\Support\Facades\Schema::hasColumn('service_requests', 'price_breakdown') && !empty($booking->price_breakdown)) {
            $decoded = json_decode($booking->price_breakdown, true);
            $breakdown = is_array($decoded) ? $decoded : null;
        }

        $amount = \Illuminate\Support\Facades\Schema::hasColumn('service_requests', 'amount')
            ? $booking->amount
            : null;
        if (($amount === null || (float) $amount <= 0) && is_array($breakdown)) {
            $amount = $this->resolveBookingPayableAmount($booking);
        }
        $paymentStatus = \Illuminate\Support\Facades\Schema::hasColumn('service_requests', 'payment_status')
            ? ($booking->payment_status ?? 'pending')
            : 'pending';
        $otp = \Illuminate\Support\Facades\Schema::hasColumn('service_requests', 'otp')
            ? ($this->hasServiceOtp($booking->otp)
                ? str_pad($this->normalizeServiceOtp($booking->otp), 4, '0', STR_PAD_LEFT)
                : null)
            : null;

        return [
            'id' => $booking->id,
            'user_id' => $booking->user_id,
            'driver_id' => $booking->driver_id,
            'customer_name' => $customer['name'] ?? 'Customer',
            'customer_phone' => $customer['phone'] ?? '',
            'customer_photo' => $customer['photo'] ?? '',
            'customer' => $customer,
            'service_name' => $booking->service_name,
            'address_type' => $booking->address_type,
            'service_address' => $booking->service_address,
            'city' => $booking->city ?? null,
            'zone_id' => $booking->zone_id,
            'lat' => $booking->lat,
            'lng' => $booking->lng,
            'preferred_date' => $booking->preferred_date,
            'preferred_time' => $booking->preferred_time,
            'description' => $booking->description,
            'status' => $booking->status,
            'otp' => $otp,
            'amount' => $amount !== null ? (float) $amount : null,
            'tax' => !empty($booking->tax) ? (is_string($booking->tax) ? json_decode($booking->tax, true) : $booking->tax) : null,
            'tax_amount' => (float) ($booking->tax_amount ?? 0.0),
            'payment_status' => $paymentStatus,
            'price_breakdown' => $breakdown,
            'service_items' => $this->buildDriverServiceItems($booking, !empty($booking->driver_id) ? (int) $booking->driver_id : null),
            'driver' => $driver,
            'created_at' => $booking->created_at,
            'updated_at' => $booking->updated_at,
        ];
    }

    private function generateServiceOtp(): string
    {
        return str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
    }

    private function normalizeServiceOtp(?string $otp): string
    {
        return preg_replace('/\D+/', '', (string) $otp);
    }

    private function hasServiceOtp(?string $otp): bool
    {
        return strlen($this->normalizeServiceOtp($otp)) >= 4;
    }

    private function serviceOtpsMatch(?string $storedOtp, ?string $providedOtp): bool
    {
        $stored = str_pad($this->normalizeServiceOtp($storedOtp), 4, '0', STR_PAD_LEFT);
        $provided = str_pad($this->normalizeServiceOtp($providedOtp), 4, '0', STR_PAD_LEFT);

        if (strlen($provided) < 4 || strlen($stored) < 4) {
            return false;
        }

        return hash_equals($stored, $provided);
    }

    private function ensureServiceOtp(ServiceRequest $booking): void
    {
        if (!\Illuminate\Support\Facades\Schema::hasColumn('service_requests', 'otp')) {
            return;
        }

        $status = strtolower(trim((string) $booking->status));
        $needsOtp = ['accepted', 'in progress', 'in_progress'];
        if (!in_array($status, $needsOtp, true) || empty($booking->driver_id)) {
            return;
        }

        $booking->refresh();
        if ($this->hasServiceOtp($booking->otp)) {
            return;
        }

        $otp = $this->generateServiceOtp();
        $updated = ServiceRequest::where('id', $booking->id)
            ->where(function ($query) {
                $query->whereNull('otp')->orWhere('otp', '');
            })
            ->update(['otp' => $otp]);

        if ($updated === 0) {
            $booking->refresh();
            return;
        }

        $booking->otp = $otp;
    }

    private function buildDriverServiceItems($svc, ?int $driverId = null): array
    {
        $items = [];
        $breakdown = null;

        if (\Illuminate\Support\Facades\Schema::hasColumn('service_requests', 'price_breakdown') && !empty($svc->price_breakdown)) {
            $decoded = json_decode($svc->price_breakdown, true);
            if (is_array($decoded) && !empty($decoded['service_items']) && is_array($decoded['service_items'])) {
                foreach ($decoded['service_items'] as $entry) {
                    if (!is_array($entry)) {
                        continue;
                    }
                    $items[] = [
                        'name' => $entry['name'] ?? '',
                        'price' => (float) ($entry['min_price'] ?? $entry['price'] ?? 0),
                        'quantity' => 1,
                    ];
                }
            }
        }

        // Fetch driver items & visiting charge if driverId is provided
        $driverItems = [];
        $driverVisitingCharge = null;
        $activeDriverId = !empty($driverId) ? $driverId : (!empty($svc->driver_id) ? (int) $svc->driver_id : null);

        if (!empty($activeDriverId)) {
            if (\Illuminate\Support\Facades\Schema::hasTable('driver_service_items')) {
                $driverItems = \Illuminate\Support\Facades\DB::table('driver_service_items')
                    ->where('driver_id', $activeDriverId)
                    ->where('price', '>', 0)
                    ->get();
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('driver_service_pricing')) {
                $pRow = \Illuminate\Support\Facades\DB::table('driver_service_pricing')
                    ->where('driver_id', $activeDriverId)
                    ->where('visiting_charge', '>', 0)
                    ->first();
                if ($pRow) {
                    $driverVisitingCharge = (float) $pRow->visiting_charge;
                }
            }
        }

        if (!empty($driverItems)) {
            foreach ($items as &$item) {
                $name = $item['name'] ?? '';
                foreach ($driverItems as $dItem) {
                    if ($this->serviceNameMatches($name, (string) $dItem->service_name)) {
                        $item['price'] = (float) $dItem->price;
                        break;
                    }
                }
            }
            unset($item);
        }

        $decoded = null;
        if (!empty($svc->price_breakdown)) {
            $decoded = json_decode($svc->price_breakdown, true);
        }
        $visit = $driverVisitingCharge ?? (float) ($decoded['visiting_charge_min'] ?? $decoded['visiting_charge'] ?? 0);
        $svcAmount = (float) ($svc->amount ?? 0);
        $baseLabour = max(0, $svcAmount - $visit);

        if (!empty($items)) {
            $unpriced = [];
            foreach ($items as $idx => $item) {
                if (stripos((string) ($item['name'] ?? ''), 'visit') === false && (float) ($item['price'] ?? 0) <= 0) {
                    $unpriced[] = $idx;
                }
            }
            if (!empty($unpriced) && $baseLabour > 0) {
                $eachPrice = round($baseLabour / count($unpriced), 2);
                foreach ($unpriced as $idx) {
                    $items[$idx]['price'] = $eachPrice;
                }
            }

            $hasVisitLine = false;
            foreach ($items as $item) {
                if (stripos((string) ($item['name'] ?? ''), 'visit') !== false) {
                    $hasVisitLine = true;
                    break;
                }
            }
            if ($visit > 0 && !$hasVisitLine) {
                $items[] = [
                    'name' => 'Visiting Charge',
                    'price' => $visit,
                    'quantity' => 1,
                ];
            }

            return $items;
        }

        $names = $this->parseRequestedServiceNames((string) ($svc->service_name ?? ''));
        foreach ($names as $name) {
            $price = 0;
            if (!empty($driverItems)) {
                foreach ($driverItems as $dItem) {
                    if ($this->serviceNameMatches($name, (string) $dItem->service_name)) {
                        $price = (float) $dItem->price;
                        break;
                    }
                }
            }
            $items[] = [
                'name' => $name,
                'price' => $price,
                'quantity' => 1,
            ];
        }

        return $items;
    }

    private function calculateDriverEstimateForBooking($svc, int $driverId): float
    {
        $breakdown = null;
        if (!empty($svc->price_breakdown)) {
            $breakdown = json_decode($svc->price_breakdown, true);
        }

        // Get driver's visiting charge from driver_service_pricing
        $visitingCharge = 0.0;
        if (\Illuminate\Support\Facades\Schema::hasTable('driver_service_pricing')) {
            $row = \Illuminate\Support\Facades\DB::table('driver_service_pricing')
                ->where('driver_id', $driverId)
                ->where('visiting_charge', '>', 0)
                ->first();
            if ($row) {
                $visitingCharge = (float) $row->visiting_charge;
            }
        }
        if ($visitingCharge <= 0 && is_array($breakdown)) {
            $visitingCharge = (float) ($breakdown['visiting_charge_min'] ?? $breakdown['visiting_charge'] ?? 0);
        }

        // Get driver's items prices from driver_service_items
        $driverItems = [];
        if (\Illuminate\Support\Facades\Schema::hasTable('driver_service_items')) {
            $driverItems = \Illuminate\Support\Facades\DB::table('driver_service_items')
                ->where('driver_id', $driverId)
                ->where('price', '>', 0)
                ->get();
        }

        // Resolve service items for this request
        $requestedItems = [];
        if (is_array($breakdown) && !empty($breakdown['service_items']) && is_array($breakdown['service_items'])) {
            foreach ($breakdown['service_items'] as $entry) {
                if (is_array($entry) && !empty($entry['name'])) {
                    $requestedItems[] = trim((string) $entry['name']);
                }
            }
        }
        if (empty($requestedItems) && !empty($svc->service_name)) {
            $requestedItems = $this->parseRequestedServiceNames((string) $svc->service_name);
        }

        $servicesTotal = 0.0;
        foreach ($requestedItems as $sName) {
            $itemPrice = null;
            foreach ($driverItems as $dItem) {
                if ($this->serviceNameMatches($sName, (string) $dItem->service_name)) {
                    $itemPrice = (float) $dItem->price;
                    break;
                }
            }

            if ($itemPrice !== null && $itemPrice > 0) {
                $servicesTotal += $itemPrice;
            } else {
                if (is_array($breakdown) && !empty($breakdown['service_items']) && is_array($breakdown['service_items'])) {
                    foreach ($breakdown['service_items'] as $bItem) {
                        if (is_array($bItem) && $this->serviceNameMatches($sName, (string) ($bItem['name'] ?? ''))) {
                            $servicesTotal += (float) ($bItem['min_price'] ?? $bItem['price'] ?? 0);
                            break;
                        }
                    }
                }
            }
        }

        $materialCost = is_array($breakdown) ? (float) ($breakdown['material_cost'] ?? 0) : 0.0;
        $platformFee = is_array($breakdown) ? (float) ($breakdown['platform_fee'] ?? 0) : 0.0;

        $total = round($servicesTotal + $visitingCharge + $materialCost + $platformFee, 2);

        return $total > 0 ? $total : $this->resolveBookingPayableAmount($svc);
    }

    private function resolveRequestedServiceNames(Request $request, string $serviceName): array
    {
        if ($request->has('service_names')) {
            $rawNames = $request->input('service_names');
            $names = [];

            if (is_array($rawNames)) {
                $names = $rawNames;
            } elseif (is_string($rawNames)) {
                if (trim($rawNames) === '') {
                    return [];
                }
                $names = preg_split('/\|/', $rawNames) ?: [];
            }

            return array_values(array_unique(array_filter(array_map(
                fn ($name) => trim((string) $name),
                $names
            ))));
        }

        if ($serviceName !== '') {
            return $this->parseRequestedServiceNames($serviceName);
        }

        return [];
    }

    private function parseRequestedServiceNames(string $serviceName): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $serviceName) ?: [];
        $names = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if (preg_match('/^Selected\s+.+?\(\d+\):\s*$/i', $line)) {
                continue;
            }
            $line = preg_replace('/^[\-•\*]\s*/u', '', $line);
            $line = preg_replace('/^Selected\s+.+?\(\d+\):\s*/i', '', $line);
            $line = trim($line);
            if ($line !== '') {
                $names[] = $line;
            }
        }

        if (empty($names)) {
            $clean = trim(preg_replace('/^Selected\s+.+?\(\d+\):\s*/i', '', $serviceName));
            if ($clean !== '') {
                $names[] = $clean;
            }
        }

        return array_values(array_unique(array_filter($names)));
    }

    private function serviceNameMatches(string $selectedName, string $providerServiceName): bool
    {
        $needle = strtolower(trim(preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $selectedName)));
        $label = strtolower(trim(preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $providerServiceName)));

        if ($needle === '' || $label === '') {
            return false;
        }

        if ($label === $needle || str_contains($label, $needle) || str_contains($needle, $label)) {
            return true;
        }

        $needleWords = array_filter(explode(' ', $needle), fn($w) => strlen($w) > 2);
        $labelWords = array_filter(explode(' ', $label), fn($w) => strlen($w) > 2);

        $common = array_intersect($needleWords, $labelWords);
        return count($common) >= 1;
    }

    private function getCatalogBenchmarkPrice(string $serviceName): array
    {
        $clean = strtolower(trim($serviceName));
        
        // Lab Tests
        if (str_contains($clean, 'cbc') || str_contains($clean, 'complete blood')) return ['min' => 299.0, 'max' => 349.0];
        if (str_contains($clean, 'sugar') || str_contains($clean, 'glucose')) return ['min' => 149.0, 'max' => 199.0];
        if (str_contains($clean, 'hba1c')) return ['min' => 399.0, 'max' => 499.0];
        if (str_contains($clean, 'thyroid') || str_contains($clean, 'tsh')) return ['min' => 399.0, 'max' => 499.0];
        if (str_contains($clean, 'lipid') || str_contains($clean, 'cholesterol')) return ['min' => 499.0, 'max' => 599.0];
        if (str_contains($clean, 'lft') || str_contains($clean, 'liver')) return ['min' => 599.0, 'max' => 699.0];
        if (str_contains($clean, 'kft') || str_contains($clean, 'kidney')) return ['min' => 599.0, 'max' => 699.0];
        if (str_contains($clean, 'vitamin d') || str_contains($clean, 'b12') || str_contains($clean, 'vitamins') || str_contains($clean, 'vitamin')) return ['min' => 699.0, 'max' => 899.0];
        if (str_contains($clean, 'urine') || str_contains($clean, 'stool')) return ['min' => 149.0, 'max' => 199.0];
        if (str_contains($clean, 'dengue') || str_contains($clean, 'malaria') || str_contains($clean, 'typhoid') || str_contains($clean, 'fever')) return ['min' => 499.0, 'max' => 599.0];
        if (str_contains($clean, 'full body') || str_contains($clean, 'package') || str_contains($clean, 'executive')) return ['min' => 1299.0, 'max' => 1699.0];
        if (str_contains($clean, 'ecg')) return ['min' => 399.0, 'max' => 499.0];
        if (str_contains($clean, 'blood sample') || str_contains($clean, 'express sample') || str_contains($clean, 'home sample') || str_contains($clean, 'collection')) return ['min' => 99.0, 'max' => 149.0];

        // Healthcare
        if (str_contains($clean, 'emergency doctor')) return ['min' => 799.0, 'max' => 999.0];
        if (str_contains($clean, 'doctor') || str_contains($clean, 'physician')) return ['min' => 499.0, 'max' => 699.0];
        if (str_contains($clean, 'physio')) return ['min' => 549.0, 'max' => 699.0];
        if (str_contains($clean, 'nurse') || str_contains($clean, 'nursing')) return ['min' => 349.0, 'max' => 499.0];
        if (str_contains($clean, 'injection') || str_contains($clean, 'iv drip') || str_contains($clean, 'dressing')) return ['min' => 199.0, 'max' => 299.0];
        if (str_contains($clean, 'ambulance')) return ['min' => 999.0, 'max' => 1499.0];

        // Education & Tutors
        if (str_contains($clean, 'tutor') || str_contains($clean, 'tuition') || str_contains($clean, 'teacher')) return ['min' => 300.0, 'max' => 450.0];
        if (str_contains($clean, 'yoga') || str_contains($clean, 'trainer') || str_contains($clean, 'gym')) return ['min' => 400.0, 'max' => 600.0];

        // Home & Repair
        if (str_contains($clean, 'ac gas') || str_contains($clean, 'gas refill')) return ['min' => 1499.0, 'max' => 1999.0];
        if (str_contains($clean, 'ac install') || str_contains($clean, 'ac service') || str_contains($clean, 'ac repair') || str_contains($clean, 'ac & appliances') || str_contains($clean, 'air conditioner') || str_contains($clean, 'ac')) return ['min' => 499.0, 'max' => 799.0];
        if (str_contains($clean, 'refrigerator') || str_contains($clean, 'fridge') || str_contains($clean, 'washing machine') || str_contains($clean, 'microwave') || str_contains($clean, 'water purifier') || str_contains($clean, 'geyser')) return ['min' => 349.0, 'max' => 499.0];
        if (str_contains($clean, 'electrician') || str_contains($clean, 'plumber') || str_contains($clean, 'carpenter') || str_contains($clean, 'repair & maintenance') || str_contains($clean, 'handyman') || str_contains($clean, 'repair')) return ['min' => 199.0, 'max' => 299.0];
        if (str_contains($clean, 'painter') || str_contains($clean, 'painting')) return ['min' => 499.0, 'max' => 999.0];
        if (str_contains($clean, 'cleaning') || str_contains($clean, 'deep clean')) return ['min' => 799.0, 'max' => 1299.0];
        if (str_contains($clean, 'pest control')) return ['min' => 899.0, 'max' => 1199.0];
        if (str_contains($clean, 'shifting') || str_contains($clean, 'packers') || str_contains($clean, 'movers')) return ['min' => 1999.0, 'max' => 3499.0];
        if (str_contains($clean, 'maid') || str_contains($clean, 'cook') || str_contains($clean, 'babysitter') || str_contains($clean, 'personal home assistance')) return ['min' => 299.0, 'max' => 499.0];
        if (str_contains($clean, 'salon') || str_contains($clean, 'barber') || str_contains($clean, 'spa') || str_contains($clean, 'massage') || str_contains($clean, 'personal services')) return ['min' => 299.0, 'max' => 599.0];

        return ['min' => 249.0, 'max' => 349.0];
    }

    private function findProviderPriceRangeForService(string $serviceName, array $driverIds = []): array
    {
        $needle = strtolower(trim($serviceName));
        if ($needle === '') {
            return ['min' => 0.0, 'max' => 0.0, 'available' => false];
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('driver_service_items')) {
            $query = \Illuminate\Support\Facades\DB::table('driver_service_items')
                ->select('service_name', 'price', 'driver_id')
                ->where('price', '>', 0);

            if (!empty($driverIds)) {
                $query->whereIn('driver_id', $driverIds);
            }

            $pricesByDriver = [];
            foreach ($query->get() as $item) {
                if (!$this->serviceNameMatches($serviceName, (string) $item->service_name)) {
                    continue;
                }

                $price = round((float) $item->price, 2);
                if ($price <= 0) {
                    continue;
                }

                $driverKey = (string) ($item->driver_id ?? '0');
                if (!isset($pricesByDriver[$driverKey]) || $price < $pricesByDriver[$driverKey]) {
                    $pricesByDriver[$driverKey] = $price;
                }
            }

            if (empty($pricesByDriver) && !empty($driverIds)) {
                return $this->findProviderPriceRangeForService($serviceName, []);
            }

            $prices = array_values($pricesByDriver);
            if (!empty($prices)) {
                return [
                    'min' => round((float) min($prices), 2),
                    'max' => round((float) max($prices), 2),
                    'available' => true,
                ];
            }
        }

        // Fallback to Catalog Benchmark Price
        $benchmark = $this->getCatalogBenchmarkPrice($serviceName);
        return [
            'min' => $benchmark['min'],
            'max' => $benchmark['max'],
            'available' => true,
        ];
    }

    private function findVisitingChargeRange(array $driverIds = []): array
    {
        if (\Illuminate\Support\Facades\Schema::hasTable('driver_service_pricing')) {
            $query = \Illuminate\Support\Facades\DB::table('driver_service_pricing')
                ->select('driver_id', 'visiting_charge')
                ->where('visiting_charge', '>', 0);

            if (!empty($driverIds)) {
                $query->whereIn('driver_id', $driverIds);
            }

            $chargesByDriver = [];
            foreach ($query->get() as $row) {
                $charge = round((float) $row->visiting_charge, 2);
                if ($charge <= 0) {
                    continue;
                }
                $driverKey = (string) ($row->driver_id ?? '0');
                if (!isset($chargesByDriver[$driverKey]) || $charge < $chargesByDriver[$driverKey]) {
                    $chargesByDriver[$driverKey] = $charge;
                }
            }

            $charges = array_values($chargesByDriver);

            if (empty($charges) && !empty($driverIds)) {
                return $this->findVisitingChargeRange([]);
            }

            if (!empty($charges)) {
                return [
                    'min' => round((float) min($charges), 2),
                    'max' => round((float) max($charges), 2),
                ];
            }
        }

        return [
            'min' => 99.0,
            'max' => 99.0,
        ];
    }

    private function getNearbyProviderDriverIds(?float $lat, ?float $lng): array
    {
        if ($lat === null || $lng === null || $lat == 0.0 || $lng == 0.0) {
            return [];
        }

        if (!\Illuminate\Support\Facades\Schema::hasTable('tj_conducteur')) {
            return [];
        }

        $drivers = \Illuminate\Support\Facades\DB::table('tj_conducteur')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get(['id', 'latitude', 'longitude']);

        $ids = [];
        foreach ($drivers as $driver) {
            $distanceKm = $this->distanceKmFromDriver(
                (float) $driver->latitude,
                (float) $driver->longitude,
                $lat,
                $lng
            );
            if ($this->bookingWithinDriverRadius($distanceKm)) {
                $ids[] = (int) $driver->id;
            }
        }

        return array_values(array_unique($ids));
    }

    private function formatPriceRangeLabel(float $min, float $max, bool $available): string
    {
        if (!$available || ($min <= 0 && $max <= 0)) {
            return 'Rate on visit';
        }

        $minLabel = '₹' . number_format($min, 0, '.', '');
        if ($max > 0 && abs($max - $min) >= 1) {
            return $minLabel . '-' . '₹' . number_format($max, 0, '.', '');
        }

        return $minLabel;
    }

    private function findMinimumProviderPriceForService(string $serviceName): ?float
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('driver_service_items')) {
            return null;
        }

        $needle = strtolower(trim($serviceName));
        if ($needle === '') {
            return null;
        }

        $items = \Illuminate\Support\Facades\DB::table('driver_service_items')
            ->select('service_name', 'price')
            ->get();

        $best = null;
        foreach ($items as $item) {
            if ((float) $item->price <= 0) {
                continue;
            }
            if (!$this->serviceNameMatches($serviceName, (string) $item->service_name)) {
                continue;
            }
            $price = (float) $item->price;
            $best = $best === null ? $price : min($best, $price);
        }

        return $best;
    }

    private function findMinimumVisitingCharge(array $serviceNames): float
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('driver_service_pricing')) {
            return 0.0;
        }

        $charges = \Illuminate\Support\Facades\DB::table('driver_service_pricing')
            ->where('visiting_charge', '>', 0)
            ->pluck('visiting_charge')
            ->map(fn ($v) => (float) $v)
            ->filter(fn ($v) => $v > 0);

        if ($charges->isEmpty()) {
            return 0.0;
        }

        return round((float) $charges->min(), 2);
    }

    private function resolveDriverPublicProfile(int $driverId): array
    {
        $profession = $this->resolveDriverProfession($driverId);
        $profile = [
            'id' => $driverId,
            'name' => 'Service Expert',
            'phone' => '',
            'photo' => '',
            'rating' => '4.8',
            'review_count' => 0,
            'profession' => $profession,
            'experience' => '5+ Years Experience',
            'vehicle_number' => '',
            'eta_label' => '20 - 25 mins',
        ];

        if (!\Illuminate\Support\Facades\Schema::hasTable('tj_conducteur')) {
            return $profile;
        }

        $row = \Illuminate\Support\Facades\DB::table('tj_conducteur')
            ->where('id', $driverId)
            ->first();

        if (!$row) {
            return $profile;
        }

        $name = trim(($row->prenom ?? '') . ' ' . ($row->nom ?? ''));
        if ($name !== '') {
            $profile['name'] = $name;
        }

        $phone = trim((string) ($row->phone ?? ''));
        if ($phone === '' && !empty($row->alternate_phone)) {
            $phone = trim((string) $row->alternate_phone);
        }
        $profile['phone'] = $phone;

        if (isset($row->note) && $row->note !== null && $row->note !== '') {
            $profile['rating'] = (string) $row->note;
        }

        $photo = !empty($row->photo_path) ? (string) $row->photo_path : (!empty($row->photo) ? (string) $row->photo : '');
        if ($photo !== '') {
            $profile['photo'] = filter_var($photo, FILTER_VALIDATE_URL)
                ? $photo
                : (file_exists(public_path('assets/images/driver/' . $photo))
                    ? asset('assets/images/driver/' . $photo)
                    : $photo);
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('tj_vehicule')) {
            $plate = \Illuminate\Support\Facades\DB::table('tj_vehicule')
                ->where('id_conducteur', $driverId)
                ->orderByDesc('id')
                ->value('numberplate');
            if (!empty($plate)) {
                $profile['vehicle_number'] = strtoupper(trim((string) $plate));
            }
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('tj_user_note')) {
            $reviewCount = \Illuminate\Support\Facades\DB::table('tj_user_note')
                ->where('id_conducteur', $driverId)
                ->count();
            $profile['review_count'] = (int) $reviewCount;
        }

        if (!empty($row->creer)) {
            try {
                $years = max(1, (int) now()->diffInYears(\Carbon\Carbon::parse($row->creer)));
                if ($years >= 8) {
                    $profile['experience'] = '8+ Years Experience';
                } elseif ($years >= 5) {
                    $profile['experience'] = '5+ Years Experience';
                } else {
                    $profile['experience'] = $years . '+ Years Experience';
                }
            } catch (\Throwable $e) {
                // keep default
            }
        }

        return $profile;
    }

    private function resolveCustomerPublicProfile(int $userId): array
    {
        $profile = [
            'id' => $userId,
            'name' => 'Customer',
            'phone' => '',
            'photo' => '',
        ];

        if (!\Illuminate\Support\Facades\Schema::hasTable('tj_user_app')) {
            return $profile;
        }

        $row = \Illuminate\Support\Facades\DB::table('tj_user_app')
            ->where('id', $userId)
            ->first();

        if (!$row) {
            return $profile;
        }

        $name = trim(($row->prenom ?? '') . ' ' . ($row->nom ?? ''));
        if ($name !== '') {
            $profile['name'] = $name;
        }

        $phone = trim((string) ($row->phone ?? ''));
        $profile['phone'] = $phone;

        $photo = !empty($row->photo_path) ? (string) $row->photo_path : (!empty($row->photo) ? (string) $row->photo : '');
        if ($photo !== '') {
            $profile['photo'] = filter_var($photo, FILTER_VALIDATE_URL)
                ? $photo
                : (file_exists(public_path('assets/images/user/' . $photo))
                    ? asset('assets/images/user/' . $photo)
                    : $photo);
        }

        return $profile;
    }

    private function resolveDriverProfession(int $driverId): string
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('tj_conducteur_categories')) {
            return 'Service Expert';
        }

        $label = \Illuminate\Support\Facades\DB::table('tj_conducteur_categories as cc')
            ->leftJoin('tj_categorie_user as cu', 'cu.id', '=', 'cc.category_id')
            ->where('cc.driver_id', $driverId)
            ->value('cu.libelle');

        return $label ? trim((string) $label) : 'Service Expert';
    }
    
    /**
     * Returns Home Services catalog for user-app "More" / All Services.
     * Top-level rows are type=consumer_service only (never provider signup categories).
     */
    public function getServiceCategories(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            return $this->searchServiceCategories($search);
        }

        $parentId = $request->input('parent_id');

        if ($parentId) {
            $query = \Illuminate\Support\Facades\DB::table('tj_categorie_user')
                ->where('statut', true)
                ->where('parent_id', $parentId);
        } else {
            $query = \Illuminate\Support\Facades\DB::table('tj_categorie_user')
                ->where('statut', true)
                ->where('type', 'consumer_service')
                ->whereNull('parent_id');
        }

        $rows = $query->select('id', 'libelle', 'image')->orderBy('id')->get();

        $data = $rows->map(function ($row) {
            $hasChildren = \Illuminate\Support\Facades\DB::table('tj_categorie_user')
                ->where('parent_id', $row->id)
                ->where('statut', true)
                ->exists();

            return [
                'id' => $row->id,
                'libelle' => $row->libelle,
                'image' => $row->image,
                'has_children' => $hasChildren,
            ];
        });

        return response()->json([
            'success' => 'success',
            'data' => $data,
        ]);
    }

    private function searchServiceCategories(string $search)
    {
        $rows = \Illuminate\Support\Facades\DB::table('tj_categorie_user')
            ->where('statut', true)
            ->where('type', 'consumer_service')
            ->where('libelle', 'like', '%' . $search . '%')
            ->select('id', 'libelle', 'image', 'parent_id')
            ->orderBy('libelle')
            ->limit(100)
            ->get();

        $allNodes = \Illuminate\Support\Facades\DB::table('tj_categorie_user')
            ->where('type', 'consumer_service')
            ->select('id', 'libelle', 'parent_id')
            ->get()
            ->keyBy('id');

        $data = $rows->map(function ($row) use ($allNodes) {
            $breadcrumb = [];
            $currentId = $row->parent_id;
            $depth = 0;

            while ($currentId && $depth < 12) {
                $parent = $allNodes->get($currentId);
                if (!$parent) {
                    break;
                }
                array_unshift($breadcrumb, $parent->libelle);
                $currentId = $parent->parent_id;
                $depth++;
            }

            $hasChildren = \Illuminate\Support\Facades\DB::table('tj_categorie_user')
                ->where('parent_id', $row->id)
                ->where('statut', true)
                ->where('type', 'consumer_service')
                ->exists();

            return [
                'id' => $row->id,
                'libelle' => $row->libelle,
                'image' => $row->image,
                'has_children' => $hasChildren,
                'breadcrumb' => $breadcrumb,
                'parent_id' => $row->parent_id,
            ];
        });

        return response()->json([
            'success' => 'success',
            'data' => $data,
        ]);
    }

    public function getHomeServices(Request $request)
    {
        $parent = \Illuminate\Support\Facades\DB::table('tj_categorie_user')->where('libelle', '🧹 Home Services')->first();
        if (!$parent) {
            return response()->json(['success' => 'success', 'data' => []]);
        }

        $subCategories = \Illuminate\Support\Facades\DB::table('tj_categorie_user')->where('parent_id', $parent->id)->get();
        
        $categories = [];
        $icons = [
            'Cleaner' => '🧹',
            'Electrician' => '⚡',
            'Plumber' => '🚰',
            'Carpenter' => '🔨',
            'Painter' => '🎨',
            'Pest Control' => '🐜'
        ];

        foreach ($subCategories as $sub) {
            $services = \Illuminate\Support\Facades\DB::table('tj_categorie_user')
                ->where('parent_id', $sub->id)
                ->where('statut', true)
                ->select('libelle', 'image')
                ->get()
                ->toArray();
                
            if (count($services) > 0) {
                $categories[] = [
                    'title' => $sub->libelle,
                    'icon' => $icons[$sub->libelle] ?? '🔧',
                    'services' => $services
                ];
            }
        }
        
        return response()->json([
            'success' => 'success',
            'data' => $categories
        ]);
    }

    /**
     * Unified driver bookings feed for My Booking console.
     * Filters home-service bookings by onboarding category/skills and 0–30 km radius
     * from the driver's saved location in tj_conducteur (updated when GPS is on).
     * Query: id_driver (required), status=incoming|active|history|all
     */
    public function getDriverBookings(Request $request)
    {
        $driverId = $request->input('id_driver') ?? $request->header('id_user');
        $statusFilter = strtolower(trim((string) $request->input('status', 'all')));

        if (empty($driverId)) {
            return response()->json(['success' => 'error', 'message' => 'Driver ID is required', 'data' => []]);
        }

        $profile = $this->buildDriverBookingProfile((int) $driverId);

        if (!$profile['has_onboarding']) {
            return response()->json([
                'success' => 'error',
                'message' => 'Please complete onboarding before viewing bookings',
                'onboarding_required' => true,
                'data' => [],
                'counts' => ['incoming' => 0, 'active' => 0, 'history' => 0],
            ]);
        }

        if ($profile['is_home_service_provider'] && (!$profile['has_location'] || !$profile['driver_online'])) {
            return response()->json([
                'success' => 'error',
                'message' => 'Please turn on your status and enable GPS to capture your location',
                'location_required' => true,
                'driver_online' => $profile['driver_online'],
                'has_location' => $profile['has_location'],
                'data' => [],
                'counts' => ['incoming' => 0, 'active' => 0, 'history' => 0],
            ]);
        }

        $bookings = [];

        if (\Illuminate\Support\Facades\Schema::hasTable('tj_requete')) {
            $rides = \Illuminate\Support\Facades\DB::table('tj_requete')
                ->leftJoin('tj_user_app', 'tj_user_app.id', '=', 'tj_requete.id_user_app')
                ->where('tj_requete.id_conducteur', $driverId)
                ->select(
                    'tj_requete.id',
                    'tj_requete.depart_name',
                    'tj_requete.destination_name',
                    'tj_requete.statut',
                    'tj_requete.montant',
                    'tj_requete.creer',
                    'tj_requete.modifier',
                    'tj_user_app.prenom',
                    'tj_user_app.nom',
                    'tj_user_app.phone'
                )
                ->orderByDesc('tj_requete.id')
                ->limit(100)
                ->get();

            foreach ($rides as $ride) {
                $status = strtolower(trim((string) ($ride->statut ?? '')));
                $group = $this->bookingStatusGroup($status);
                $customer = trim(($ride->prenom ?? '') . ' ' . ($ride->nom ?? ''));
                $bookings[] = [
                    'id' => (string) $ride->id,
                    'type' => 'ride',
                    'title' => 'Ride Booking',
                    'subtitle' => trim(($ride->depart_name ?? 'Pickup') . ' → ' . ($ride->destination_name ?? 'Drop')),
                    'status' => $ride->statut ?? 'new',
                    'status_group' => $group,
                    'customer_name' => $customer !== '' ? $customer : 'Customer',
                    'customer_phone' => $ride->phone ?? '',
                    'amount' => (float) ($ride->montant ?? 0),
                    'date' => $ride->creer ?? $ride->modifier ?? now()->toDateTimeString(),
                    'pickup' => $ride->depart_name ?? '',
                    'drop' => $ride->destination_name ?? '',
                    'address' => $ride->depart_name ?? '',
                    'preferred_date' => '',
                    'preferred_time' => '',
                    'description' => '',
                    'distance_km' => null,
                ];
            }
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('service_requests')) {
            $services = \Illuminate\Support\Facades\DB::table('service_requests')
                ->leftJoin('tj_user_app', 'tj_user_app.id', '=', 'service_requests.user_id')
                ->where(function ($q) use ($driverId) {
                    $q->where('service_requests.driver_id', $driverId)
                        ->orWhere(function($sub) {
                            $sub->whereNull('service_requests.driver_id')
                                ->whereNotIn(\Illuminate\Support\Facades\DB::raw('LOWER(TRIM(service_requests.status))'), ['cancelled', 'canceled', 'rejected', 'completed', 'failed']);
                        });
                })
                ->select(
                    'service_requests.*',
                    'tj_user_app.prenom',
                    'tj_user_app.nom',
                    'tj_user_app.phone',
                    'tj_user_app.photo_path'
                )
                ->orderByDesc('service_requests.id')
                ->limit(200)
                ->get();

            foreach ($services as $svc) {
                $isAssigned = !empty($svc->driver_id);
                if ($isAssigned && (string) $svc->driver_id !== (string) $driverId) {
                    continue;
                }

                // If driver has already rejected this unassigned request, hide it from this driver
                if (!$isAssigned && !empty($svc->rejected_driver_ids)) {
                    $rejected = is_array($svc->rejected_driver_ids)
                        ? $svc->rejected_driver_ids
                        : json_decode($svc->rejected_driver_ids, true);
                    if (is_array($rejected)) {
                        $driverIdStr = (string) $driverId;
                        $driverIdInt = (int) $driverId;
                        if (in_array($driverIdStr, $rejected, true) || in_array($driverIdInt, $rejected, true)) {
                            continue;
                        }
                    }
                }

                $status = strtolower(trim((string) ($svc->status ?? 'pending')));
                $group = $this->bookingStatusGroup($status);
                if (!$isAssigned && $group !== 'incoming') {
                    continue;
                }

                if ($profile['is_home_service_provider'] && !$this->serviceMatchesDriverProfile($svc->service_name ?? '', $profile)) {
                    continue;
                }

                $distanceKm = $this->distanceKmFromDriver(
                    $profile['driver_lat'],
                    $profile['driver_lng'],
                    $svc->lat ?? null,
                    $svc->lng ?? null
                );

                if ($profile['is_home_service_provider'] && !$isAssigned) {
                    if (!$this->bookingWithinDriverRadius($distanceKm)) {
                        continue;
                    }
                }

                $customerName = trim(($svc->prenom ?? '') . ' ' . ($svc->nom ?? ''));
                $customerPhone = trim((string) ($svc->phone ?? ''));
                $customerPhoto = $svc->photo_path ?? '';
                if (($customerName === '' || $customerPhone === '') && !empty($svc->user_id)) {
                    $cProf = $this->resolveCustomerPublicProfile((int) $svc->user_id);
                    if ($customerName === '') $customerName = $cProf['name'];
                    if ($customerPhone === '') $customerPhone = $cProf['phone'];
                    if ($customerPhoto === '') $customerPhoto = $cProf['photo'];
                }

                $address = $this->resolveServiceAddress($svc);
                $scheduleDate = $svc->preferred_date ?? '';
                $scheduleTime = $svc->preferred_time ?? '';
                $serviceItems = $this->buildDriverServiceItems($svc, (int) $driverId);
                $driverCalculated = $this->calculateDriverEstimateForBooking($svc, (int) $driverId);
                $payableAmount = $this->resolveBookingPayableAmount($svc);
                $bookingAmount = ($payableAmount > 0 && $payableAmount >= $driverCalculated)
                    ? $payableAmount
                    : ($driverCalculated > 0 ? $driverCalculated : $payableAmount);
                $description = $svc->description ?? '';

                $bookings[] = [
                    'id' => (string) $svc->id,
                    'type' => 'service',
                    'title' => $svc->service_name ?? 'Service Booking',
                    'subtitle' => $description !== '' ? $description : $address,
                    'status' => $svc->status ?? 'Pending',
                    'status_group' => $group,
                    'customer_name' => $customerName !== '' ? $customerName : 'Customer',
                    'customer_phone' => $customerPhone,
                    'customer_photo' => $customerPhoto,
                    'customer' => [
                        'id' => (int) ($svc->user_id ?? 0),
                        'name' => $customerName !== '' ? $customerName : 'Customer',
                        'phone' => $customerPhone,
                        'photo' => $customerPhoto,
                    ],
                    'customer_rating' => 4.7,
                    'review_count' => 0,
                    'is_urgent' => stripos($description, '[VERY URGENT]') !== false,
                    'service_items' => $serviceItems,
                    'amount' => $bookingAmount,
                    'payment_status' => $svc->payment_status ?? 'pending',
                    'date' => $scheduleDate
                        ? trim($scheduleDate . ' ' . $scheduleTime)
                        : ($svc->created_at ?? now()->toDateTimeString()),
                    'pickup' => $address,
                    'drop' => '',
                    'address' => $address,
                    'address_type' => $svc->address_type ?? '',
                    'city' => $svc->city ?? '',
                    'preferred_date' => $scheduleDate,
                    'preferred_time' => $scheduleTime,
                    'description' => $svc->description ?? '',
                    'service_name' => $svc->service_name ?? '',
                    'lat' => $svc->lat ?? null,
                    'lng' => $svc->lng ?? null,
                    'distance_km' => $distanceKm,
                    'created_at' => $svc->created_at ?? null,
                ];
            }
        }

        if (!$profile['is_home_service_provider'] && \Illuminate\Support\Facades\Schema::hasTable('parcel_orders')) {
            $parcelQuery = \Illuminate\Support\Facades\DB::table('parcel_orders')
                ->leftJoin('tj_user_app', 'tj_user_app.id', '=', 'parcel_orders.id_user_app')
                ->where('parcel_orders.id_conducteur', $driverId)
                ->select('parcel_orders.*', 'tj_user_app.prenom', 'tj_user_app.nom', 'tj_user_app.phone')
                ->orderByDesc('parcel_orders.id')
                ->limit(100)
                ->get();

            foreach ($parcelQuery as $parcel) {
                $status = strtolower(trim((string) ($parcel->statut ?? '')));
                $group = $this->bookingStatusGroup($status);
                $customer = trim(($parcel->prenom ?? '') . ' ' . ($parcel->nom ?? ''));
                if ($customer === '') {
                    $customer = $parcel->sender_name ?? $parcel->receiver_name ?? 'Customer';
                }
                $pickup = $parcel->source ?? $parcel->sender_address ?? $parcel->depart_name ?? '';
                $drop = $parcel->destination ?? $parcel->receiver_address ?? $parcel->destination_name ?? '';
                $bookings[] = [
                    'id' => (string) $parcel->id,
                    'type' => 'parcel',
                    'title' => 'Parcel Booking',
                    'subtitle' => trim(($pickup !== '' ? $pickup : 'Pickup') . ' → ' . ($drop !== '' ? $drop : 'Drop')),
                    'status' => $parcel->statut ?? 'new',
                    'status_group' => $group,
                    'customer_name' => $customer,
                    'customer_phone' => $parcel->phone ?? '',
                    'amount' => (float) ($parcel->amount ?? $parcel->montant ?? 0),
                    'date' => $parcel->creer ?? $parcel->created_at ?? now()->toDateTimeString(),
                    'pickup' => $pickup,
                    'drop' => $drop,
                    'address' => $pickup,
                    'preferred_date' => '',
                    'preferred_time' => '',
                    'description' => '',
                    'distance_km' => null,
                ];
            }
        }

        usort($bookings, function ($a, $b) {
            return strtotime($b['date'] ?? 0) <=> strtotime($a['date'] ?? 0);
        });

        $counts = ['incoming' => 0, 'active' => 0, 'history' => 0];
        foreach ($bookings as $item) {
            $group = $item['status_group'] ?? 'incoming';
            if (isset($counts[$group])) {
                $counts[$group]++;
            }
        }

        if (in_array($statusFilter, ['incoming', 'active', 'history'], true)) {
            $bookings = array_values(array_filter($bookings, function ($item) use ($statusFilter) {
                return ($item['status_group'] ?? '') === $statusFilter;
            }));
        }

        return response()->json([
            'success' => 'success',
            'counts' => $counts,
            'profession' => $profile['profession'],
            'is_home_service_provider' => $profile['is_home_service_provider'],
            'data' => array_values($bookings),
        ]);
    }

    public function updateServiceBookingStatus(Request $request)
    {
        $driverId = $request->input('id_driver') ?? $request->input('driver_id') ?? $request->header('id_conducteur') ?? $request->header('id_user');
        $bookingId = $request->input('booking_id');
        $status = $request->input('status');

        if (empty($bookingId) || empty($status)) {
            return response()->json(['success' => 'error', 'message' => 'booking_id and status are required'], 422);
        }

        $key = strtolower(str_replace(' ', '_', (string) $status));
        $map = [
            'accepted' => 'Accepted',
            'rejected' => 'Rejected',
            'in_progress' => 'In Progress',
            'awaiting_payment' => 'Awaiting Payment',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'canceled' => 'Cancelled',
        ];
        if (!isset($map[$key])) {
            return response()->json(['success' => 'error', 'message' => 'Invalid status'], 422);
        }
        $normalized = $map[$key];

        $booking = ServiceRequest::find($bookingId);
        if (!$booking) {
            return response()->json(['success' => 'error', 'message' => 'Booking not found'], 404);
        }

        if (empty($driverId)) {
            $driverId = $booking->driver_id;
        }

        if (empty($driverId) && $normalized === 'Accepted') {
            $firstDriver = \Illuminate\Support\Facades\DB::table('tj_conducteur')->first();
            if ($firstDriver) $driverId = $firstDriver->id;
        }

        if (empty($driverId)) {
            return response()->json(['success' => 'error', 'message' => 'id_driver is required'], 422);
        }

        if (!empty($booking->driver_id) && (string) $booking->driver_id !== (string) $driverId && $normalized !== 'Accepted') {
            // If assigned to another driver but that driver is not active, allow reassignment or update
            if (!in_array($normalized, ['In Progress', 'Awaiting Payment', 'Completed'])) {
                return response()->json(['success' => 'error', 'message' => 'This booking is assigned to another provider'], 422);
            }
        }

        $currentStatus = strtolower(trim((string) $booking->status));

        if ($normalized === 'Accepted') {
            // Blocking Rule: If provider has outstanding cash collection due debt (negative balance), block accepting new booking
            $providerRecord = \Illuminate\Support\Facades\DB::table('tj_conducteur')->where('id', $driverId)->first();
            if ($providerRecord && floatval($providerRecord->amount ?? 0) < 0) {
                $dueDebt = number_format(abs(floatval($providerRecord->amount)), 2);
                return response()->json([
                    'success' => 'Failed',
                    'error' => 'You have an outstanding cash collection due of ₹' . $dueDebt . '. Please clear your pending dues to continue accepting new bookings.',
                    'message' => 'You have an outstanding cash collection due of ₹' . $dueDebt . '. Please clear your pending dues to continue accepting new bookings.'
                ], 422);
            }

            $booking->driver_id = $driverId;
            $booking->status = 'Confirmed';
            if (\Illuminate\Support\Facades\Schema::hasColumn('service_requests', 'otp') && !$this->hasServiceOtp($booking->otp)) {
                $booking->otp = $this->generateServiceOtp();
            }
            $this->bindAcceptedDriverPricingToBooking($booking, (int) $driverId);
        } elseif ($normalized === 'Rejected') {
            $rejectedDrivers = [];
            if (!empty($booking->rejected_driver_ids)) {
                $decoded = is_array($booking->rejected_driver_ids)
                    ? $booking->rejected_driver_ids
                    : json_decode($booking->rejected_driver_ids, true);
                if (is_array($decoded)) {
                    $rejectedDrivers = $decoded;
                }
            }
            if (!in_array((int) $driverId, $rejectedDrivers, true)) {
                $rejectedDrivers[] = (int) $driverId;
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('service_requests', 'rejected_driver_ids')) {
                $booking->rejected_driver_ids = json_encode(array_values(array_unique($rejectedDrivers)));
            }
            $booking->driver_id = null;
            $booking->status = 'Pending';
            $booking->save();
            return response()->json([
                'success' => 'success',
                'message' => 'Booking request declined',
                'data' => $this->formatUserBooking($booking->fresh()),
            ]);
        } elseif ($normalized === 'In Progress') {
            if (empty($booking->driver_id)) {
                $booking->driver_id = $driverId;
            }

            if (in_array($currentStatus, ['in progress', 'in_progress'], true)) {
                if ((string) $booking->driver_id === (string) $driverId) {
                    return response()->json([
                        'success' => 'success',
                        'message' => 'Service already in progress',
                        'data' => $this->formatUserBooking($booking->fresh()),
                    ]);
                }
            }

            $providedOtp = $this->normalizeServiceOtp($request->input('otp', ''));
            if (strlen($providedOtp) < 4) {
                return response()->json([
                    'success' => 'error',
                    'message' => 'Service OTP is required to start the job',
                ], 422);
            }

            $booking->refresh();

            // If OTP is missing on booking, populate it now
            if (!$this->hasServiceOtp($booking->otp)) {
                $booking->otp = $providedOtp;
                $booking->save();
            }

            if (!$this->serviceOtpsMatch($booking->otp, $providedOtp)) {
                return response()->json([
                    'success' => 'error',
                    'message' => 'Invalid OTP. Please ask the customer for the 4-digit service OTP displayed on their screen.',
                ], 422);
            }
            $this->bindAcceptedDriverPricingToBooking($booking, (int) $driverId);
        } elseif ($normalized === 'Awaiting Payment') {
            if (empty($booking->driver_id)) {
                $booking->driver_id = $driverId;
            }
            // Allow transition to Awaiting Payment from any active service stage
            $allowedStages = [
                'in progress',
                'in_progress',
                'in-progress',
                'confirmed',
                'accepted',
                'started',
                'on the way',
                'on_the_way',
                'awaiting payment',
                'awaiting_payment',
            ];
            if (!in_array($currentStatus, $allowedStages, true)) {
                return response()->json([
                    'success' => 'error',
                    'message' => 'Cannot request payment for a ' . $booking->status . ' booking',
                ], 422);
            }
            $this->applyDriverBillToBooking($booking, $request);
        } elseif ($normalized === 'Completed') {
            $pm = strtolower(trim((string) ($request->input('payment_method') ?? $request->input('payment_type') ?? $request->input('payment_status') ?? '')));
            if ($pm === 'cash' || $pm === 'paid_cash' || $pm === 'other' || empty($booking->payment_status) || $booking->payment_status === 'pending') {
                $booking->payment_status = 'paid_cash';
                $booking->save();
            }
            if (!$this->isBookingPaid($booking)) {
                return response()->json([
                    'success' => 'error',
                    'message' => 'Customer must complete payment before you can mark this job as completed',
                ], 422);
            }
            if (empty($booking->driver_id)) {
                $booking->driver_id = $driverId;
            }
            // Deduct admin commission ONLY IF payment was cash (for online/UPI/wallet, commission was already deducted when crediting driver wallet)
            if ($booking->payment_status === 'paid_cash') {
                $this->deductServiceCommission($booking);
            }
        } elseif ($normalized === 'Cancelled') {
            $curStatus = strtolower(trim((string) $booking->status));
            $blockedStatuses = [
                'in progress',
                'in_progress',
                'started',
                'on ride',
                'onride',
                'on_ride',
                'awaiting payment',
                'awaiting_payment',
                'completed'
            ];
            if (in_array($curStatus, $blockedStatuses, true) || !empty($booking->otp_verified_at)) {
                return response()->json([
                    'success' => 'error',
                    'message' => 'Job has already started / OTP verified / bill generated. Service provider cannot cancel this booking. Only the customer can cancel.',
                ], 422);
            }

            $rejectedDrivers = [];
            if (!empty($booking->rejected_driver_ids)) {
                $decoded = is_array($booking->rejected_driver_ids)
                    ? $booking->rejected_driver_ids
                    : json_decode($booking->rejected_driver_ids, true);
                if (is_array($decoded)) {
                    $rejectedDrivers = $decoded;
                }
            }
            if (!in_array((int) $driverId, $rejectedDrivers, true)) {
                $rejectedDrivers[] = (int) $driverId;
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('service_requests', 'rejected_driver_ids')) {
                $booking->rejected_driver_ids = json_encode(array_values(array_unique($rejectedDrivers)));
            }
            $booking->driver_id = null;
            $booking->status = 'Pending';
            $booking->save();
            return response()->json([
                'success' => 'success',
                'message' => 'Booking cancelled successfully',
                'data' => $this->formatUserBooking($booking->fresh()),
            ]);
        } else {
            $booking->driver_id = $driverId;
        }

        $booking->status = $normalized;
        $booking->save();

        // Trigger step-by-step Home Service notifications to Customer and Provider
        try {
            $driverName = \Illuminate\Support\Facades\DB::table('tj_conducteur')
                ->where('id', $driverId)
                ->selectRaw("CONCAT(prenom, ' ', nom) as name")
                ->value('name') ?: 'Partner';

            if ($normalized === 'Accepted' || $normalized === 'Confirmed') {
                $otpText = !empty($booking->otp) ? " Start OTP: {$booking->otp}" : "";
                $this->sendServiceNotification(
                    (int) $booking->user_id,
                    'customer',
                    "Expert Assigned: {$driverName}",
                    "{$driverName} has accepted your {$booking->service_name} request.{$otpText}",
                    ['booking_id' => (string) $booking->id, 'status' => 'Confirmed', 'driver_id' => (string) $driverId]
                );
            } elseif ($normalized === 'In Progress') {
                $this->sendServiceNotification(
                    (int) $booking->user_id,
                    'customer',
                    "Service In Progress 🛠️",
                    "{$driverName} has started working on your {$booking->service_name}.",
                    ['booking_id' => (string) $booking->id, 'status' => 'In Progress']
                );
            } elseif ($normalized === 'Awaiting Payment') {
                $billAmount = $booking->final_total ?: $booking->amount;
                $this->sendServiceNotification(
                    (int) $booking->user_id,
                    'customer',
                    "Bill Generated: ₹{$billAmount}",
                    "Your {$booking->service_name} is complete. Please review and pay ₹{$billAmount}.",
                    ['booking_id' => (string) $booking->id, 'status' => 'Awaiting Payment', 'amount' => (string) $billAmount]
                );
            } elseif ($normalized === 'Completed') {
                $this->sendServiceNotification(
                    (int) $booking->user_id,
                    'customer',
                    "Service Completed! ⭐",
                    "Your {$booking->service_name} has been completed. Thank you for choosing Fiinway!",
                    ['booking_id' => (string) $booking->id, 'status' => 'Completed']
                );
            }
        } catch (\Throwable $stepNotifEx) {
            \Log::error('updateServiceBookingStatus notification error: ' . $stepNotifEx->getMessage());
}

        return response()->json([
            'success' => 'success',
            'message' => 'Booking status updated',
            'data' => $this->formatUserBooking($booking->fresh()),
        ]);
    }

    private function bookingStatusGroup(string $status): string
    {
        $status = strtolower(trim($status));
        $incoming = ['new', 'pending', 'open', 'requested'];
        $active = ['confirmed', 'accepted', 'on ride', 'onride', 'on_ride', 'in progress', 'in_progress', 'started', 'awaiting payment', 'awaiting_payment'];
        $history = ['completed', 'rejected', 'reject', 'cancelled', 'canceled', 'failed'];

        if (in_array($status, $incoming, true)) return 'incoming';
        if (in_array($status, $active, true)) return 'active';
        if (in_array($status, $history, true)) return 'history';
        return 'history';
    }

    private function buildDriverBookingProfile(int $driverId): array
    {
        $hasOnboarding = false;
        $isHomeServiceProvider = false;
        $profession = '';
        $keywords = [];
        $driverLat = 0.0;
        $driverLng = 0.0;
        $hasLocation = false;
        $driverOnline = false;

        if (\Illuminate\Support\Facades\Schema::hasTable('tj_conducteur_categories')) {
            $rows = \Illuminate\Support\Facades\DB::table('tj_conducteur_categories as cc')
                ->leftJoin('tj_categorie_user as cu', 'cu.id', '=', 'cc.category_id')
                ->leftJoin('tj_categorie_user as parent', 'parent.id', '=', 'cu.parent_id')
                ->where('cc.driver_id', $driverId)
                ->select('cu.id', 'cu.libelle', 'cu.parent_id', 'parent.libelle as parent_libelle')
                ->get();

            foreach ($rows as $row) {
                $label = trim((string) ($row->libelle ?? ''));
                if ($label === '' || strcasecmp($label, 'Online Seller') === 0) {
                    continue;
                }

                $hasOnboarding = true;
                $profession = $profession ?: $label;
                $normalizedLabel = strtolower(trim(preg_replace('/[\x{1F300}-\x{1F9FF}]/u', '', $label)));
                if ($normalizedLabel !== '') {
                    $keywords[] = $normalizedLabel;
                }

                $parentLabel = strtolower(trim(preg_replace('/[\x{1F300}-\x{1F9FF}]/u', '', (string) ($row->parent_libelle ?? ''))));
                if (str_contains($parentLabel, 'home services') || $this->isHomeServiceProfession($label)) {
                    $isHomeServiceProvider = true;
                }
            }
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('driver_service_skills')) {
            $skillRows = \Illuminate\Support\Facades\DB::table('driver_service_skills as ds')
                ->join('tj_categorie_user as cu', 'cu.id', '=', 'ds.skill_id')
                ->where('ds.driver_id', $driverId)
                ->pluck('cu.libelle');

            foreach ($skillRows as $skillLabel) {
                $skillLabel = strtolower(trim((string) $skillLabel));
                if ($skillLabel !== '') {
                    $keywords[] = $skillLabel;
                    $hasOnboarding = true;
                    $isHomeServiceProvider = true;
                }
            }
        }

        if ($hasOnboarding && !$isHomeServiceProvider && $this->isHomeServiceProfession($profession)) {
            $isHomeServiceProvider = true;
        }

        if (!$hasOnboarding && \Illuminate\Support\Facades\Schema::hasTable('tj_vehicule')) {
            $hasVeh = \Illuminate\Support\Facades\DB::table('tj_vehicule')
                ->where('id_conducteur', $driverId)
                ->where('statut', 'yes')
                ->exists();
            if ($hasVeh) {
                $hasOnboarding = true;
                $profession = $profession ?: 'Driver';
            }
        }

        $keywords = array_values(array_unique(array_filter($keywords)));
        $keywords = array_merge($keywords, $this->professionKeywordAliases($profession));

        if (\Illuminate\Support\Facades\Schema::hasTable('tj_conducteur')) {
            $driver = \Illuminate\Support\Facades\DB::table('tj_conducteur')
                ->where('id', $driverId)
                ->first(['latitude', 'longitude', 'online']);

            if ($driver) {
                $driverLat = (float) ($driver->latitude ?: 0);
                $driverLng = (float) ($driver->longitude ?: 0);
                $hasLocation = $driverLat != 0.0 && $driverLng != 0.0;
                $onlineValue = strtolower(trim((string) ($driver->online ?? '')));
                $driverOnline = in_array($onlineValue, ['yes', '1', 'on', 'true'], true);
            }
        }

        return [
            'has_onboarding' => $hasOnboarding,
            'is_home_service_provider' => $isHomeServiceProvider,
            'profession' => $profession,
            'match_keywords' => array_values(array_unique($keywords)),
            'driver_lat' => $driverLat,
            'driver_lng' => $driverLng,
            'has_location' => $hasLocation,
            'driver_online' => $driverOnline,
        ];
    }

    private function isHomeServiceProfession(string $label): bool
    {
        $key = strtolower(trim(preg_replace('/[\x{1F300}-\x{1F9FF}]/u', '', $label)));

        return in_array($key, [
            'electrician',
            'plumber',
            'cleaner',
            'carpenter',
            'painter',
            'pest control',
            'ac repair',
            'appliance repair',
            'home tutor',
            'maid',
            'cook',
            'babysitter',
            'physiotherapist',
            'nurse',
        ], true);
    }

    private function bookingWithinDriverRadius(?float $distanceKm): bool
    {
        if ($distanceKm === null) {
            return false;
        }

        return $distanceKm >= 0 && $distanceKm <= 30;
    }

    public static function countPendingServiceRequestsForDriver(int $driverId): int
    {
        $controller = app(self::class);
        $profile = $controller->buildDriverBookingProfile($driverId);

        if (!$profile['has_onboarding'] || !$profile['is_home_service_provider']) {
            return 0;
        }

        if (!$profile['has_location'] || !$profile['driver_online']) {
            return 0;
        }

        if (!\Illuminate\Support\Facades\Schema::hasTable('service_requests')) {
            return 0;
        }

        $services = \Illuminate\Support\Facades\DB::table('service_requests')
            ->whereNull('driver_id')
            ->whereNotIn(\Illuminate\Support\Facades\DB::raw('LOWER(TRIM(status))'), ['cancelled', 'canceled', 'rejected', 'completed', 'failed'])
            ->orderByDesc('id')
            ->limit(200)
            ->get();

        $count = 0;
        foreach ($services as $svc) {
            $status = strtolower(trim((string) ($svc->status ?? 'pending')));
            if ($controller->bookingStatusGroup($status) !== 'incoming') {
                continue;
            }

            if (!$controller->serviceMatchesDriverProfile($svc->service_name ?? '', $profile)) {
                continue;
            }

            $distanceKm = $controller->distanceKmFromDriver(
                $profile['driver_lat'],
                $profile['driver_lng'],
                $svc->lat ?? null,
                $svc->lng ?? null
            );

            if ($controller->bookingWithinDriverRadius($distanceKm)) {
                $count++;
            }
        }

        return $count;
    }

    private function professionKeywordAliases(string $profession): array
    {
        $key = strtolower(trim($profession));
        $map = [
            'electrician' => ['electric', 'wiring', 'switch', 'mcb', 'fan', 'light', 'inverter', 'geyser'],
            'plumber' => ['plumb', 'pipe', 'tap', 'leak', 'drain', 'water', 'bathroom', 'toilet'],
            'cleaner' => ['clean', 'deep clean', 'sofa', 'kitchen', 'carpet', 'mattress'],
            'carpenter' => ['carpent', 'wood', 'furniture', 'door', 'wardrobe'],
            'painter' => ['paint', 'wall', 'polish'],
            'pest control' => ['pest', 'termite', 'cockroach', 'mosquito', 'rodent'],
        ];

        return $map[$key] ?? [];
    }

    private function serviceMatchesDriverProfile(string $serviceName, array $profile): bool
    {
        $service = strtolower(trim($serviceName));
        if ($service === '') {
            return false;
        }

        foreach ($profile['match_keywords'] as $keyword) {
            if ($keyword === '') {
                continue;
            }
            if (str_contains($service, $keyword) || str_contains($keyword, $service)) {
                return true;
            }
            if (strlen($keyword) >= 4 && str_contains($service, substr($keyword, 0, 4))) {
                return true;
            }
        }

        return false;
    }

    private function bookingMatchesDriverZone($svc, array $driverZoneIds): bool
    {
        if (empty($driverZoneIds)) {
            return true;
        }

        $driverZoneIds = array_map('strval', $driverZoneIds);
        $bookingZoneId = trim((string) ($svc->zone_id ?? ''));

        if ($bookingZoneId !== '' && in_array($bookingZoneId, $driverZoneIds, true)) {
            return true;
        }

        $lat = (float) ($svc->lat ?? 0);
        $lng = (float) ($svc->lng ?? 0);
        if ($lat != 0.0 && $lng != 0.0) {
            foreach ($driverZoneIds as $zoneId) {
                if ($this->isPointInsideZone($lat, $lng, (int) $zoneId)) {
                    return true;
                }
            }

            return false;
        }

        $bookingCity = strtolower(trim((string) ($svc->city ?? '')));
        if ($bookingCity !== '') {
            foreach ($driverZoneIds as $zoneId) {
                $zone = Zone::find((int) $zoneId);
                if ($zone && $this->cityMatchesZone($bookingCity, (string) $zone->name)) {
                    return true;
                }
            }

            return false;
        }

        return false;
    }

    private function cityMatchesZone(string $city, string $zoneName): bool
    {
        $city = strtolower(trim($city));
        $zoneName = strtolower(trim($zoneName));

        if ($city === '' || $zoneName === '') {
            return false;
        }

        return str_contains($zoneName, $city) || str_contains($city, $zoneName);
    }

    private function findZoneIdForPoint(float $lat, float $lng): ?int
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('zones')) {
            return null;
        }

        $zones = Zone::where('status', 'yes')->get();
        foreach ($zones as $zone) {
            if ($this->isPointInsideZone($lat, $lng, (int) $zone->id)) {
                return (int) $zone->id;
            }
        }

        return null;
    }

    private function isPointInsideZone(float $lat, float $lng, int $zoneId): bool
    {
        $zone = Zone::find($zoneId);
        if (!$zone || !$zone->area) {
            return false;
        }

        $zoneAreaArray = json_decode($zone->area->toJson(), true);
        if (!is_array($zoneAreaArray) || empty($zoneAreaArray['coordinates'])) {
            return false;
        }

        $verticesX = [];
        $verticesY = [];
        foreach ($zoneAreaArray['coordinates'] as $data) {
            foreach ($data as $vertex) {
                $verticesX[] = $vertex[1];
                $verticesY[] = $vertex[0];
            }
        }

        $pointsPolygon = count($verticesX) - 1;
        if ($pointsPolygon < 3) {
            return false;
        }

        return $this->isInPolygon($pointsPolygon, $verticesX, $verticesY, $lng, $lat);
    }

    private function isInPolygon(int $pointsPolygon, array $verticesX, array $verticesY, float $longitudeX, float $latitudeY): bool
    {
        $c = 0;
        for ($i = 0, $j = $pointsPolygon; $i < $pointsPolygon; $j = $i++) {
            $point = $i;
            if ($point == $pointsPolygon) {
                $point = 0;
            }

            if ((($verticesY[$point] > $latitudeY) != ($verticesY[$j] > $latitudeY))
                && ($longitudeX < ($verticesX[$j] - $verticesX[$point]) * ($latitudeY - $verticesY[$point]) / ($verticesY[$j] - $verticesY[$point]) + $verticesX[$point])) {
                $c = !$c;
            }
        }

        return (bool) $c;
    }

    private function resolveZoneName($zoneId): string
    {
        if (empty($zoneId) || !\Illuminate\Support\Facades\Schema::hasTable('zones')) {
            return '';
        }

        $zone = Zone::find((int) $zoneId);

        return $zone ? (string) $zone->name : '';
    }

    private function looksLikeCoordinates(string $value): bool
    {
        return (bool) preg_match('/^-?\d+(\.\d+)?\s*,\s*-?\d+(\.\d+)?$/', trim($value));
    }

    private function isGenericAddressLabel(string $value): bool
    {
        $label = strtolower(trim($value));

        return in_array($label, ['home', 'work', 'other', 'online', 'remote'], true);
    }

    private function reverseGeocode(float $lat, float $lng): array
    {
        $result = ['address' => '', 'city' => ''];

        try {
            $url = 'https://nominatim.openstreetmap.org/reverse?format=json&lat=' . urlencode((string) $lat)
                . '&lon=' . urlencode((string) $lng) . '&zoom=18&addressdetails=1';
            $context = stream_context_create([
                'http' => [
                    'header' => "User-Agent: Fiinway/1.0\r\n",
                    'timeout' => 5,
                ],
            ]);
            $response = @file_get_contents($url, false, $context);
            if ($response === false) {
                return $result;
            }

            $data = json_decode($response, true);
            if (!is_array($data)) {
                return $result;
            }

            $displayName = trim((string) ($data['display_name'] ?? ''));
            if ($displayName !== '' && !$this->looksLikeCoordinates($displayName)) {
                $result['address'] = $displayName;
            }

            $addr = $data['address'] ?? null;
            if (is_array($addr)) {
                $city = $addr['city'] ?? $addr['town'] ?? $addr['village'] ?? $addr['suburb'] ?? '';
                $result['city'] = trim((string) $city);

                if ($result['address'] === '') {
                    $parts = array_filter([
                        $addr['house_number'] ?? null,
                        $addr['road'] ?? null,
                        $addr['suburb'] ?? null,
                        $result['city'] !== '' ? $result['city'] : null,
                        $addr['state'] ?? null,
                        $addr['postcode'] ?? null,
                    ], fn ($part) => is_string($part) && trim($part) !== '');

                    if (!empty($parts)) {
                        $result['address'] = implode(', ', $parts);
                    }
                }
            }
        } catch (\Throwable $e) {
            return $result;
        }

        return $result;
    }

    private function distanceKmFromDriver(float $driverLat, float $driverLng, $bookingLat, $bookingLng): ?float
    {
        $lat = (float) ($bookingLat ?? 0);
        $lng = (float) ($bookingLng ?? 0);

        if ($driverLat == 0.0 || $driverLng == 0.0 || $lat == 0.0 || $lng == 0.0) {
            return null;
        }

        $earthRadius = 6371;
        $latFrom = deg2rad($driverLat);
        $lonFrom = deg2rad($driverLng);
        $latTo = deg2rad($lat);
        $lonTo = deg2rad($lng);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return round($earthRadius * $angle, 2);
    }

    private function resolveServiceAddress($svc): string
    {
        $serviceAddress = trim((string) ($svc->service_address ?? ''));
        if ($serviceAddress !== '' && !$this->looksLikeCoordinates($serviceAddress)) {
            return $serviceAddress;
        }

        $addrType = trim((string) ($svc->address_type ?? ''));
        if ($addrType !== '' && !$this->isGenericAddressLabel($addrType) && !$this->looksLikeCoordinates($addrType)) {
            return $addrType;
        }

        $lat = (float) ($svc->lat ?? 0);
        $lng = (float) ($svc->lng ?? 0);
        if ($lat != 0.0 && $lng != 0.0) {
            $geocoded = $this->reverseGeocode($lat, $lng);
            if (!empty($geocoded['address'])) {
                if (!empty($svc->id) && \Illuminate\Support\Facades\Schema::hasTable('service_requests')) {
                    \Illuminate\Support\Facades\DB::table('service_requests')
                        ->where('id', $svc->id)
                        ->update([
                            'service_address' => $geocoded['address'],
                            'city' => $geocoded['city'] ?: ($svc->city ?? null),
                        ]);
                }

                return $geocoded['address'];
            }
        }

        $city = trim((string) ($svc->city ?? ''));
        if ($city !== '') {
            return $city;
        }

        $zoneName = $this->resolveZoneName($svc->zone_id ?? null);
        if ($zoneName !== '') {
            return $zoneName;
        }

        return 'Address not available';
    }
}
