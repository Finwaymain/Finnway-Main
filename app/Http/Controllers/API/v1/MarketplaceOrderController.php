<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\MarketplaceOrder;
use App\Models\MarketplaceOrderItem;
use App\Models\MarketplaceProduct;
use App\Models\UserApp;
use App\Http\Controllers\API\v1\GcmController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class MarketplaceOrderController extends Controller
{
    private function getAuthenticatedUserId(Request $request)
    {
        $keys = ['user_id', 'user-id', 'id_user', 'id-user', 'driver_id', 'driver-id', 'id_conducteur'];
        foreach ($keys as $key) {
            $val = $request->json($key) ?? $request->input($key) ?? $request->query($key) ?? $request->header($key);
            if (!empty($val)) {
                return is_array($val) ? $val[0] : $val;
            }
        }

        $serverKeys = ['HTTP_USER_ID', 'HTTP_USER_ID', 'HTTP_ID_USER', 'HTTP_DRIVER_ID', 'HTTP_ID_CONDUCTEUR', 'HTTP_ACCESSTOKEN'];
        foreach ($serverKeys as $sKey) {
            if (!empty($_SERVER[$sKey])) {
                return $_SERVER[$sKey];
            }
        }

        $accessToken = $request->header('accesstoken') ?? $request->query('accesstoken') ?? $request->input('accesstoken') ?? $request->json('accesstoken');
        if (!empty($accessToken)) {
            $userAccess = DB::table('users_access')->where('accesstoken', $accessToken)->first();
            if ($userAccess && !empty($userAccess->user_id)) {
                return $userAccess->user_id;
            }
        }

        return null;
    }

    /**
     * Record wallet transaction history for customer or driver.
     */
    private function recordWalletTransaction($userId, $userType, $amount, $type, $description, $txnId)
    {
        $dateOnly = date('Y-m-d');
        $dateTime = date('Y-m-d H:i:s');
        $deductionType = ($type === 'credit') ? 1 : 0;

        if ($userType === 'driver') {
            DB::table('tj_conducteur_transaction')->insert([
                'id_conducteur'   => $userId,
                'amount'          => (string)$amount,
                'type'            => $type,
                'deduction_type'  => $deductionType,
                'payment_method'  => 'Fiinway Wallet',
                'payment_status'  => 'success',
                'description'     => $description,
                'txn_id'          => $txnId,
                'date'            => $dateOnly,
                'creer'           => $dateTime,
                'modifier'        => $dateTime,
            ]);
        } else {
            DB::table('tj_transaction')->insert([
                'id_user_app'     => $userId,
                'user_type'       => 'customer',
                'amount'          => (string)$amount,
                'type'            => $type,
                'deduction_type'  => $deductionType,
                'payment_method'  => 'Fiinway Wallet',
                'payment_status'  => 'success',
                'description'     => $description,
                'txn_id'          => $txnId,
                'date'            => $dateOnly,
                'creer'           => $dateTime,
                'modifier'        => $dateTime,
            ]);
        }
    }

    /**
     * Calculate all active taxes applicable for the order based on payment method.
     */
    public function calculateMarketplaceTaxes(float $subtotal, string $paymentMethod = 'wallet'): array
    {
        $activeTaxes = DB::table('tj_tax')->where('statut', 'yes')->get();
        $taxBreakdown = [];
        $totalTaxAmount = 0;
        $taxLabels = [];
        $totalRate = 0;

        $normMethod = strtolower(trim($paymentMethod));
        if (str_contains($normMethod, 'wallet')) $normMethod = 'wallet';
        elseif (str_contains($normMethod, 'razorpay') || str_contains($normMethod, 'online') || str_contains($normMethod, 'card')) $normMethod = 'online';
        elseif (str_contains($normMethod, 'upi') || str_contains($normMethod, 'gpay') || str_contains($normMethod, 'phonepe')) $normMethod = 'upi';
        elseif (str_contains($normMethod, 'cash') || str_contains($normMethod, 'cod')) $normMethod = 'cash';

        foreach ($activeTaxes as $tax) {
            $applicable = strtolower($tax->applicable_on ?? '');
            $methods = array_map('trim', explode(',', $applicable));

            $isApplicable = empty($applicable) 
                || in_array($normMethod, $methods) 
                || in_array('all', $methods)
                || ($normMethod === 'online' && (in_array('online', $methods) || in_array('upi', $methods) || in_array('card', $methods)))
                || ($normMethod === 'upi' && (in_array('upi', $methods) || in_array('online', $methods)))
                || ($normMethod === 'wallet' && in_array('wallet', $methods))
                || ($normMethod === 'cash' && in_array('cash', $methods));

            if ($isApplicable) {
                $val = floatval($tax->value ?? 0);
                $isPercent = (strtolower($tax->type ?? '') === 'percentage' || str_contains(strtolower($tax->type ?? ''), 'percent'));

                if ($isPercent) {
                    $amt = round(($subtotal * $val) / 100, 2);
                    $label = "{$tax->libelle} ({$val}%)";
                    $totalRate += $val;
                } else {
                    $amt = round($val, 2);
                    $label = "{$tax->libelle} (₹{$val})";
                }

                $taxBreakdown[] = [
                    'id'         => $tax->id,
                    'name'       => $tax->libelle,
                    'type'       => $tax->type,
                    'rate'       => $val,
                    'rate_label' => $isPercent ? "{$val}%" : "₹{$val}",
                    'amount'     => $amt,
                    'label'      => $label,
                ];

                $totalTaxAmount += $amt;
                $taxLabels[] = $label;
            }
        }

        return [
            'total_tax_amount' => round($totalTaxAmount, 2),
            'taxes'            => $taxBreakdown,
            'tax_name'         => !empty($taxLabels) ? implode(', ', $taxLabels) : 'GST',
            'tax_rate'         => $totalRate,
        ];
    }

    /**
     * Get checkout summary breakdown with active taxes and commission calculations.
     */
    public function checkoutSummary(Request $request)
    {
        $items = $request->input('items', []);
        $subtotal = 0;

        foreach ($items as $item) {
            $p = MarketplaceProduct::find($item['product_id'] ?? 0);
            if ($p) {
                $qty = intval($item['quantity'] ?? 1);
                $subtotal += floatval($p->price) * $qty;
            }
        }

        if ($subtotal <= 0 && $request->has('price')) {
            $subtotal = floatval($request->input('price')) * intval($request->input('quantity', 1));
        }

        $deliveryCharge = floatval($request->input('delivery_charge', 0));
        $paymentMethod = strtolower($request->input('payment_method', 'wallet'));

        // 1. Calculate All Active Applicable Taxes from tj_tax
        $taxCalc = $this->calculateMarketplaceTaxes($subtotal, $paymentMethod);
        $totalTaxAmount = $taxCalc['total_tax_amount'];
        $totalPayable = round($subtotal + $deliveryCharge + $totalTaxAmount, 2);

        // 2. Fetch active Commission Settings
        $commSetting = \App\Models\MarketplaceCommissionSetting::getActiveSetting();
        $commType = $commSetting->commission_type ?? 'percentage';
        $commRate = floatval($commSetting->commission_value ?? 5);
        $adminCommissionAmount = 0;

        if ($commType === 'percentage') {
            $adminCommissionAmount = round(($subtotal * $commRate) / 100, 2);
        } else {
            $adminCommissionAmount = round($commRate, 2);
        }
        $sellerPayoutAmount = max(0, round($subtotal - $adminCommissionAmount, 2));

        return response()->json([
            'success' => 'Success',
            'data' => [
                'subtotal'                 => $subtotal,
                'delivery_charge'          => $deliveryCharge,
                'taxes'                    => $taxCalc['taxes'],
                'total_tax_amount'         => $totalTaxAmount,
                'tax'                      => [
                    'name'   => $taxCalc['tax_name'],
                    'rate'   => $taxCalc['tax_rate'],
                    'amount' => $totalTaxAmount,
                ],
                'total_payable'            => $totalPayable,
                'admin_commission'         => [
                    'type'   => $commType,
                    'rate'   => $commRate,
                    'amount' => $adminCommissionAmount,
                ],
                'estimated_seller_payout'  => $sellerPayoutAmount,
            ]
        ]);
    }

    /**
     * Place a new order with Tax Calculation and Escrow Settlement Hold.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
            'delivery_address' => 'required|string',
            'phone' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => 'Failed',
                'error' => $validator->errors()->first()
            ], 420);
        }

        // 1. Identify Buyer accurately (Driver vs UserApp)
        $userType = $request->input('user_type') ?? $request->query('user_type') ?? $request->header('user_type');
        $driverId = $request->input('driver_id') ?? $request->query('driver_id') ?? $request->header('driver_id');
        $userIdInput = $request->input('user_id') ?? $request->query('user_id') ?? $request->header('user_id');
        $phoneInput = $request->input('phone') ?? $request->query('phone') ?? $request->header('phone');
        $accessToken = $request->header('accesstoken') ?? $request->query('accesstoken') ?? $request->input('accesstoken');

        $buyer = null;
        $buyerType = 'customer';

        if ($accessToken) {
            $userAccess = DB::table('users_access')->where('accesstoken', $accessToken)->first();
            if ($userAccess && !empty($userAccess->user_id)) {
                $uType = ($userAccess->user_type === 'driver') ? 'driver' : 'user';
                if ($uType === 'driver') {
                    $buyer = \App\Models\Driver::find($userAccess->user_id);
                    if ($buyer) $buyerType = 'driver';
                } else {
                    $buyer = UserApp::find($userAccess->user_id);
                    if ($buyer) $buyerType = 'customer';
                }
            }
        }

        if (!$buyer && (!empty($driverId) || $userType === 'driver')) {
            $id = $driverId ?: $userIdInput;
            if ($id) {
                $buyer = \App\Models\Driver::find($id);
                if ($buyer) $buyerType = 'driver';
            }
        }

        if (!$buyer && !empty($userIdInput)) {
            if ($userType === 'driver') {
                $buyer = \App\Models\Driver::find($userIdInput);
                if ($buyer) $buyerType = 'driver';
            }
            if (!$buyer) {
                $buyer = UserApp::find($userIdInput);
                if ($buyer) $buyerType = 'customer';
            }
            if (!$buyer) {
                $buyer = \App\Models\Driver::find($userIdInput);
                if ($buyer) $buyerType = 'driver';
            }
        }

        if (!$buyer && !empty($phoneInput)) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $phoneInput);
            if (strlen($cleanPhone) >= 10) {
                $last10 = substr($cleanPhone, -10);
                $buyer = UserApp::where('phone', 'like', "%{$last10}%")->first();
                if ($buyer) {
                    $buyerType = 'customer';
                } else {
                    $buyer = \App\Models\Driver::where('phone', 'like', "%{$last10}%")->first();
                    if ($buyer) $buyerType = 'driver';
                }
            }
        }

        if (!$buyer) {
            $buyer = UserApp::first();
        }

        $userId = $buyer->id;

        $itemsData = $request->input('items');
        $subtotal = 0;
        $validatedItems = [];
        $firstSellerId = null;

        // Pre-validate products, stock, and seller
        foreach ($itemsData as $item) {
            $product = MarketplaceProduct::find($item['product_id']);
            if (!$product) {
                return response()->json([
                    'success' => 'Failed',
                    'error' => 'Product not found: ID ' . $item['product_id']
                ], 404);
            }

            if (in_array(strtolower($product->status ?? ''), ['sold', 'rejected', 'inactive', 'deleted', 'disabled'])) {
                return response()->json([
                    'success' => 'Failed',
                    'error' => 'Product is not available for purchase: ' . $product->title
                ], 422);
            }

            if (strval($product->user_id) === strval($userId) && $buyerType !== 'driver') {
                return response()->json([
                    'success' => 'Failed',
                    'error' => 'You cannot purchase your own product: ' . $product->title
                ], 422);
            }

            if ($product->stock_quantity < $item['quantity']) {
                return response()->json([
                    'success' => 'Failed',
                    'error' => 'Insufficient stock for product: ' . $product->title . ' (Available: ' . $product->stock_quantity . ')'
                ], 422);
            }

            $itemPrice = floatval($product->price);
            $subtotal += $itemPrice * $item['quantity'];
            if (!$firstSellerId) {
                $firstSellerId = $product->user_id;
            }

            $validatedItems[] = [
                'product' => $product,
                'quantity' => $item['quantity'],
                'price' => $itemPrice
            ];
        }

        $deliveryCharge = floatval($request->input('delivery_charge', 0));
        $paymentMethod = strtolower($request->input('payment_method', 'wallet'));

        // 1. Calculate All Active Applicable Taxes from tj_tax
        $taxCalc = $this->calculateMarketplaceTaxes($subtotal, $paymentMethod);
        $totalTaxAmount = $taxCalc['total_tax_amount'];
        $taxName = $taxCalc['tax_name'];
        $taxRate = $taxCalc['tax_rate'];
        $totalPayable = round($subtotal + $deliveryCharge + $totalTaxAmount, 2);

        // 2. Calculate Admin Commission & Seller Net Payout
        $commSetting = \App\Models\MarketplaceCommissionSetting::getActiveSetting();
        $commType = $commSetting->commission_type ?? 'percentage';
        $commRate = floatval($commSetting->commission_value ?? 5);
        $adminCommissionAmount = 0;

        if ($commType === 'percentage') {
            $adminCommissionAmount = round(($subtotal * $commRate) / 100, 2);
        } else {
            $adminCommissionAmount = round($commRate, 2);
        }
        $sellerPayoutAmount = max(0, round($subtotal - $adminCommissionAmount, 2));

        $txnId = $request->input('txn_id', 'TXN_' . time() . '_' . rand(1000, 9999));
        $buyerBalance = floatval($buyer->amount ?? 0);

        // Check transaction sum fallback if balance is 0
        if ($buyerBalance == 0) {
            $uType = ($buyerType === 'driver') ? 'driver' : 'customer';
            $txnSum = DB::table('tj_transaction')
                ->where('id_user_app', $buyer->id)
                ->where(function($q) use ($uType) {
                    $q->where('user_type', $uType)
                      ->orWhereNull('user_type')
                      ->orWhere('user_type', '');
                })
                ->sum('amount');
            if ($txnSum > 0) {
                $buyerBalance = floatval($txnSum);
                $buyer->amount = $buyerBalance;
                $buyer->save();
            }
        }

        // Check wallet balance & M-PIN ONLY if payment method is wallet
        if ($paymentMethod === 'wallet') {
            if ($buyerBalance < $totalPayable) {
                return response()->json([
                    'success' => 'Failed',
                    'error' => 'Insufficient wallet balance. Required: ₹' . number_format($totalPayable, 2) . ', Available: ₹' . number_format($buyerBalance, 2)
                ], 422);
            }

            $mPin = $request->input('m_pin') ?? $request->input('mpin');
            if (empty($mPin)) {
                return response()->json([
                    'success' => 'Failed',
                    'error' => 'Wallet M-PIN is required to authorize payment.',
                    'require_mpin' => true
                ], 422);
            }

            $userMPin = (string)($buyer->m_pin ?? '');
            $userMdp  = (string)($buyer->mdp ?? '');
            $enteredMPin = (string)$mPin;

            $isMPinValid = (!empty($userMPin) && $userMPin === $enteredMPin) || 
                           (!empty($userMdp) && $userMdp === md5($enteredMPin));

            if (empty($userMPin) && empty($userMdp)) {
                // Initialize user M-PIN if not set yet
                $buyer->m_pin = $enteredMPin;
                $buyer->mdp = md5($enteredMPin);
                $buyer->save();
                $isMPinValid = true;
            }

            if (!$isMPinValid) {
                return response()->json([
                    'success' => 'Failed',
                    'error' => 'Invalid Wallet M-PIN. Please enter your correct M-PIN.',
                    'require_mpin' => true
                ], 422);
            }
        }

        DB::beginTransaction();
        try {
            // 1. Deduct buyer wallet if wallet payment (Includes Tax & Delivery)
            if ($paymentMethod === 'wallet') {
                $buyer->amount = max(0, $buyerBalance - $totalPayable);
                $buyer->save();
            }

            // 2. Create order with full metadata, taxes, and ESCROW payout status
            $order = MarketplaceOrder::create([
                'user_id'                 => $userId,
                'seller_id'               => $firstSellerId,
                'total_amount'            => $totalPayable,
                'subtotal'                => $subtotal,
                'delivery_charge'         => $deliveryCharge,
                'tax_name'                => $taxName,
                'tax_rate'                => $taxRate,
                'tax_amount'              => $totalTaxAmount,
                'payment_method'          => $paymentMethod === 'wallet' ? 'Fiinway Wallet' : ucfirst($paymentMethod),
                'payment_status'          => 'success',
                'txn_id'                  => $txnId,
                'delivery_address'        => $request->input('delivery_address'),
                'phone'                   => $request->input('phone'),
                'contact_name'            => $request->input('contact_name', trim(($buyer->prenom ?? '') . ' ' . ($buyer->nom ?? ''))),
                'city'                    => $request->input('city', ''),
                'pincode'                 => $request->input('pincode', ''),
                'status'                  => 'placed',
                'delivery_days'           => 3,
                'status_notes'            => 'Order placed successfully. Awaiting seller dispatch & admin confirmation.',
                'admin_commission_type'   => $commType,
                'admin_commission_rate'   => $commRate,
                'admin_commission_amount' => $adminCommissionAmount,
                'seller_payout_amount'    => $sellerPayoutAmount,
                'payout_status'           => 'pending', // ESCROW: Seller wallet will be credited after admin confirmation!
            ]);

            // 3. Process items & reduce stock
            $date = date('Y-m-d H:i:s');
            $sellersToNotify = [];

            foreach ($validatedItems as $vItem) {
                $product = $vItem['product'];
                $qty = $vItem['quantity'];
                $price = $vItem['price'];

                // Create order item
                MarketplaceOrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $product->id,
                    'quantity'   => $qty,
                    'price'      => $price,
                ]);

                // Update product stock
                $product->stock_quantity = max(0, $product->stock_quantity - $qty);
                if ($product->stock_quantity <= 0) {
                    $product->status = 'sold';
                }
                $product->save();

                // Seller record to notify (NO immediate wallet credit — funds held in escrow)
                $seller = UserApp::find($product->user_id) ?? \App\Models\Driver::find($product->user_id);
                if ($seller) {
                    $sellersToNotify[$seller->id][] = [
                        'seller' => $seller,
                        'product_title' => $product->title,
                        'qty' => $qty
                    ];
                }
            }

            // 4. Transaction record for buyer (Deduction)
            if ($paymentMethod === 'wallet') {
                $buyerType = ($buyer instanceof \App\Models\Driver) ? 'driver' : 'customer';
                $productSummary = implode(', ', array_map(fn($v) => $v['product']->title, $validatedItems));
                $taxNote = $totalTaxAmount > 0 ? " (Incl. {$taxName} ₹{$totalTaxAmount})" : "";
                $this->recordWalletTransaction(
                    $userId,
                    $buyerType,
                    $totalPayable,
                    'debit',
                    "Marketplace Purchase: Purchased '{$productSummary}'{$taxNote} - Order #{$order->id}",
                    $txnId
                );
            }

            DB::commit();

            // Trigger dynamic referral cashback reward
            try {
                $buyerType = ($buyer instanceof \App\Models\Driver) ? 'driver' : 'customer';
                \App\Services\ReferralRewardService::processReward(
                    $userId,
                    $buyerType,
                    'marketplace_order',
                    $totalPayable,
                    "Marketplace Order #{$order->id}"
                );
            } catch (\Exception $ex) {
                \Illuminate\Support\Facades\Log::error("Marketplace referral reward error: " . $ex->getMessage());
            }

            // 5. Send Notifications to Sellers
            foreach ($sellersToNotify as $sellerId => $notifs) {
                $seller = $notifs[0]['seller'];
                $productTitles = [];
                foreach ($notifs as $n) {
                    $productTitles[] = "'{$n['product_title']}' (x{$n['qty']})";
                }
                $productsStr = implode(', ', $productTitles);

                // Save to database notifications
                DB::table('tj_notification')->insert([
                    'to_id' => $sellerId,
                    'from_id' => $userId,
                    'titre' => 'New Order Received',
                    'message' => "New order for {$productsStr} from {$buyer->prenom} {$buyer->nom}. Payout of ₹" . number_format($sellerPayoutAmount, 2) . " will be credited upon delivery and admin confirmation.",
                    'statut' => 'unread',
                    'type' => 'marketplace',
                    'creer' => $date,
                    'modifier' => $date,
                ]);

                // Send push notification
                if (!empty($seller->fcm_id)) {
                    try {
                        $fcmMessage = [
                            'title' => 'New Order Received',
                            'body' => "You have received a new order for {$productsStr}! Payout will be released upon fulfillment.",
                            'tag' => 'marketplace_order',
                            'order_id' => (string)$order->id,
                        ];
                        GcmController::sendNotification($seller->fcm_id, $fcmMessage);
                    } catch (\Exception $e) {
                        // ignore GCM errors
                    }
                }
            }

            return response()->json([
                'success' => 'Success',
                'message' => 'Order placed successfully',
                'data' => MarketplaceOrder::with(['items.product.images', 'buyer'])->find($order->id)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => 'Failed',
                'error' => 'Failed to process order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buyer's order history.
     */
    public function buyerOrders(Request $request)
    {
        $userId = $this->getAuthenticatedUserId($request)
               ?? $request->input('user_id')
               ?? $request->query('user_id')
               ?? $request->input('driver_id')
               ?? $request->query('driver_id')
               ?? 1;

        $orders = MarketplaceOrder::where(function($q) use ($userId) {
            $q->where('user_id', $userId)
              ->orWhere('user_id', (string)$userId)
              ->orWhere('user_id', (int)$userId);
        })
        ->with(['items.product.images', 'items.product.seller'])
        ->orderBy('id', 'desc')
        ->get()
        ->map(function ($order) {
            foreach ($order->items as $item) {
                if ($item->product) {
                    $sUser = $item->product->seller ?? UserApp::find($item->product->user_id) ?? \App\Models\Driver::find($item->product->user_id);
                    $sellerName = 'Seller';
                    $sellerPhone = '';
                    if ($sUser) {
                        $sellerName = trim(($sUser->prenom ?? '') . ' ' . ($sUser->nom ?? ''));
                        if (empty($sellerName)) {
                            $sellerName = $sUser->name ?? $sUser->phone ?? 'Seller';
                        }
                        $sellerPhone = $sUser->phone ?? $sUser->mobile ?? '';
                    }
                    $item->product->seller_info = [
                        'id' => $item->product->user_id,
                        'name' => $sellerName,
                        'phone' => $sellerPhone,
                    ];
                }
            }
            return $order;
        });

        return response()->json([
            'success' => 'Success',
            'data' => $orders
        ]);
    }

    /**
     * Seller's order management history.
     */
    public function sellerOrders(Request $request)
    {
        $userId = $this->getAuthenticatedUserId($request)
               ?? $request->input('user_id')
               ?? $request->query('user_id')
               ?? $request->input('driver_id')
               ?? $request->query('driver_id')
               ?? 1;

        // Get orders containing products belonging to this seller
        $orders = MarketplaceOrder::whereHas('items.product', function ($query) use ($userId) {
            $query->where('user_id', $userId)
                  ->orWhere('user_id', (string)$userId)
                  ->orWhere('user_id', (int)$userId);
        })
        ->with(['items.product.images', 'buyer'])
        ->orderBy('id', 'desc')
        ->get()
        ->map(function ($order) use ($userId) {
            // Filter items so the seller only sees their own products in the order listing
            $order->items = $order->items->filter(function ($item) use ($userId) {
                return $item->product && (strval($item->product->user_id) === strval($userId));
            })->values();
            return $order;
        });

        return response()->json([
            'success' => 'Success',
            'data' => $orders
        ]);
    }

    /**
     * Order Details.
     */
    public function show(Request $request, $id)
    {
        $userId = $this->getAuthenticatedUserId($request)
               ?? $request->input('user_id')
               ?? $request->query('user_id')
               ?? 1;

        $order = MarketplaceOrder::with(['items.product.images', 'buyer', 'items.product.seller'])->find($id);

        if (!$order) {
            return response()->json(['success' => 'Failed', 'error' => 'Order not found'], 404);
        }

        // Verify if user is either the buyer or the seller of at least one item
        $isBuyer = (strval($order->user_id) === strval($userId));
        $isSeller = $order->items()->whereHas('product', function ($query) use ($userId) {
            $query->where('user_id', $userId)->orWhere('user_id', (string)$userId);
        })->exists();

        if (!$isBuyer && !$isSeller) {
            return response()->json(['success' => 'Failed', 'error' => 'Forbidden'], 403);
        }

        return response()->json([
            'success' => 'Success',
            'data' => $order
        ]);
    }

    /**
     * Update order status (Seller action).
     */
    public function updateStatus(Request $request, $id)
    {
        $userId = $this->getAuthenticatedUserId($request)
               ?? $request->input('user_id')
               ?? $request->query('user_id')
               ?? 1;

        $order = MarketplaceOrder::find($id);
        if (!$order) {
            return response()->json(['success' => 'Failed', 'error' => 'Order not found'], 404);
        }

        // Check if user is seller of products in this order OR buyer of the order
        $isBuyer = (strval($order->user_id) === strval($userId));
        $isSeller = false;

        foreach ($order->items as $item) {
            if ($item->product && (strval($item->product->user_id) === strval($userId) || strval($item->product->driver_id ?? '') === strval($userId))) {
                $isSeller = true;
                break;
            }
        }

        if (!$isSeller && !$isBuyer) {
            $userExists = UserApp::find($userId) ?? \App\Models\Driver::find($userId);
            if (!$userExists) {
                return response()->json(['success' => 'Failed', 'error' => 'Forbidden. Only the seller or buyer can update order status.'], 403);
            }
        }

        $inputData = array_merge($request->all(), $request->json()->all());

        $validator = Validator::make($inputData, [
            'status' => 'required|string|in:placed,processing,dispatched,shipped,out_for_delivery,delivered,cancelled',
            'delivery_days' => 'nullable|integer|min:0',
            'status_notes' => 'nullable|string|max:255',
            'courier_name' => 'nullable|string|max:255',
            'tracking_id' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => 'Failed',
                'error' => $validator->errors()->first()
            ], 420);
        }

        $status = $inputData['status'] ?? $request->input('status');
        $order->status = $status;

        if (isset($inputData['delivery_days']) && Schema::hasColumn('marketplace_orders', 'delivery_days')) {
            $order->delivery_days = $inputData['delivery_days'];
        }
        if (isset($inputData['status_notes']) && Schema::hasColumn('marketplace_orders', 'status_notes')) {
            $order->status_notes = $inputData['status_notes'];
        }
        if (isset($inputData['courier_name']) && Schema::hasColumn('marketplace_orders', 'courier_name')) {
            $order->courier_name = $inputData['courier_name'];
        }
        if (isset($inputData['tracking_id']) && Schema::hasColumn('marketplace_orders', 'tracking_id')) {
            $order->tracking_id = $inputData['tracking_id'];
        }

        $order->save();

        // Notify Buyer
        $buyer = UserApp::find($order->user_id);
        if ($buyer) {
            $date = date('Y-m-d H:i:s');
            $statusLabel = ucfirst(str_replace('_', ' ', $status));
            $notesPart = $order->status_notes ? " (Notes: {$order->status_notes})" : "";
            $daysPart = $order->delivery_days ? " within {$order->delivery_days} days" : "";

            DB::table('tj_notification')->insert([
                'to_id' => $buyer->id,
                'from_id' => $userId,
                'titre' => 'Order Status Updated',
                'message' => "Your order status has been updated to {$statusLabel}{$daysPart}{$notesPart}.",
                'statut' => 'unread',
                'type' => 'marketplace',
                'creer' => $date,
                'modifier' => $date,
            ]);

            if (!empty($buyer->fcm_id)) {
                try {
                    $fcmMessage = [
                        'title' => 'Order Status Updated',
                        'body' => "Your order status is now {$statusLabel}{$daysPart}!",
                        'tag' => 'marketplace_order_status',
                        'status' => $status,
                        'order_id' => (string)$order->id,
                    ];
                    GcmController::sendNotification($buyer->fcm_id, $fcmMessage);
                } catch (\Exception $e) {
                    // ignore GCM errors
                }
            }
        }

        return response()->json([
            'success' => 'Success',
            'message' => 'Order status updated successfully',
            'data' => MarketplaceOrder::with(['items.product.images', 'buyer'])->find($order->id)
        ]);
    }
}
