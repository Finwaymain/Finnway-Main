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
use Illuminate\Support\Facades\Validator;

class MarketplaceOrderController extends Controller
{
    private function getAuthenticatedUserId(Request $request)
    {
        $accessToken = $request->header('accesstoken');
        if (!$accessToken) {
            return null;
        }
        $userAccess = DB::table('users_access')->where('accesstoken', $accessToken)->first();
        return $userAccess ? $userAccess->user_id : null;
    }

    /**
     * Place a new order.
     */
    public function store(Request $request)
    {
        $userId = $this->getAuthenticatedUserId($request);
        if (!$userId) {
            return response()->json(['success' => 'Failed', 'error' => 'Unauthorized'], 401);
        }

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

        $buyer = UserApp::find($userId);
        if (!$buyer) {
            return response()->json(['success' => 'Failed', 'error' => 'Buyer account not found'], 404);
        }

        $itemsData = $request->input('items');
        $totalAmount = 0;
        $validatedItems = [];

        // Pre-validate products, stock, and seller
        foreach ($itemsData as $item) {
            $product = MarketplaceProduct::find($item['product_id']);
            if (!$product) {
                return response()->json([
                    'success' => 'Failed',
                    'error' => 'Product not found: ID ' . $item['product_id']
                ], 404);
            }

            if ($product->status !== 'active') {
                return response()->json([
                    'success' => 'Failed',
                    'error' => 'Product is not available for purchase: ' . $product->title
                ], 422);
            }

            if ($product->user_id == $userId) {
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
            $totalAmount += $itemPrice * $item['quantity'];
            $validatedItems[] = [
                'product' => $product,
                'quantity' => $item['quantity'],
                'price' => $itemPrice
            ];
        }

        // Check wallet balance
        $buyerBalance = floatval($buyer->amount ?? 0);
        if ($buyerBalance < $totalAmount) {
            return response()->json([
                'success' => 'Failed',
                'error' => 'Insufficient wallet balance. Required: ₹' . number_format($totalAmount, 2) . ', Available: ₹' . number_format($buyerBalance, 2)
            ], 422);
        }

        DB::beginTransaction();
        try {
            // 1. Deduct buyer wallet
            $buyer->amount = $buyerBalance - $totalAmount;
            $buyer->save();

            // 2. Create order
            $order = MarketplaceOrder::create([
                'user_id' => $userId,
                'total_amount' => $totalAmount,
                'delivery_address' => $request->input('delivery_address'),
                'phone' => $request->input('phone'),
                'status' => 'placed',
            ]);

            // 3. Process items
            $date = date('Y-m-d H:i:s');
            $sellersToNotify = [];

            foreach ($validatedItems as $vItem) {
                $product = $vItem['product'];
                $qty = $vItem['quantity'];
                $price = $vItem['price'];

                // Create order item
                MarketplaceOrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'price' => $price,
                ]);

                // Update product stock
                $product->stock_quantity = $product->stock_quantity - $qty;
                if ($product->stock_quantity <= 0) {
                    $product->status = 'sold';
                }
                $product->save();

                // Credit seller wallet
                $seller = UserApp::find($product->user_id);
                if ($seller) {
                    $sellerBalance = floatval($seller->amount ?? 0);
                    $seller->amount = $sellerBalance + ($price * $qty);
                    $seller->save();

                    // Transaction record for seller (Credit)
                    DB::table('tj_transaction')->insert([
                        'amount' => $price * $qty,
                        'deduction_type' => 1, // Credit
                        'payment_method' => 'Fiinway Wallet',
                        'payment_status' => 'success',
                        'id_user_app' => $seller->id,
                        'creer' => $date,
                        'modifier' => $date,
                    ]);

                    // Queue push notification & database notification for seller
                    $sellersToNotify[$seller->id][] = [
                        'seller' => $seller,
                        'product_title' => $product->title,
                        'qty' => $qty
                    ];
                }
            }

            // Transaction record for buyer (Deduction)
            DB::table('tj_transaction')->insert([
                'amount' => $totalAmount,
                'deduction_type' => 0, // Deduction
                'payment_method' => 'Fiinway Wallet',
                'payment_status' => 'success',
                'id_user_app' => $userId,
                'creer' => $date,
                'modifier' => $date,
            ]);

            DB::commit();

            // 4. Send Notifications to Sellers
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
                    'message' => "You have received a new order for {$productsStr} from {$buyer->prenom} {$buyer->nom}.",
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
                            'body' => "You have received a new order for {$productsStr}!",
                            'tag' => 'marketplace_order',
                            'status' => 'placed',
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
        $userId = $this->getAuthenticatedUserId($request);
        if (!$userId) {
            return response()->json(['success' => 'Failed', 'error' => 'Unauthorized'], 401);
        }

        $orders = MarketplaceOrder::where('user_id', $userId)
            ->with(['items.product.images', 'items.product.seller'])
            ->orderBy('id', 'desc')
            ->get();

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
        $userId = $this->getAuthenticatedUserId($request);
        if (!$userId) {
            return response()->json(['success' => 'Failed', 'error' => 'Unauthorized'], 401);
        }

        // Get orders containing products belonging to this seller
        $orders = MarketplaceOrder::whereHas('items.product', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->with(['items.product.images', 'buyer'])
        ->orderBy('id', 'desc')
        ->get()
        ->map(function ($order) use ($userId) {
            // Filter items so the seller only sees their own products in the order listing
            $order->items = $order->items->filter(function ($item) use ($userId) {
                return $item->product && $item->product->user_id == $userId;
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
        $userId = $this->getAuthenticatedUserId($request);
        if (!$userId) {
            return response()->json(['success' => 'Failed', 'error' => 'Unauthorized'], 401);
        }

        $order = MarketplaceOrder::with(['items.product.images', 'buyer', 'items.product.seller'])->find($id);

        if (!$order) {
            return response()->json(['success' => 'Failed', 'error' => 'Order not found'], 404);
        }

        // Verify if user is either the buyer or the seller of at least one item
        $isBuyer = ($order->user_id == $userId);
        $isSeller = $order->items()->whereHas('product', function ($query) use ($userId) {
            $query->where('user_id', $userId);
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
        $userId = $this->getAuthenticatedUserId($request);
        if (!$userId) {
            return response()->json(['success' => 'Failed', 'error' => 'Unauthorized'], 401);
        }

        $order = MarketplaceOrder::find($id);
        if (!$order) {
            return response()->json(['success' => 'Failed', 'error' => 'Order not found'], 404);
        }

        // Check if user is seller of products in this order
        $isSeller = $order->items()->whereHas('product', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->exists();

        if (!$isSeller) {
            return response()->json(['success' => 'Failed', 'error' => 'Forbidden. Only the seller can update order status.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:placed,dispatched,out_for_delivery,delivered,cancelled',
            'delivery_days' => 'nullable|integer|min:0',
            'status_notes' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => 'Failed',
                'error' => $validator->errors()->first()
            ], 420);
        }

        $status = $request->input('status');
        $order->status = $status;

        if ($request->has('delivery_days')) {
            $order->delivery_days = $request->input('delivery_days');
        }
        if ($request->has('status_notes')) {
            $order->status_notes = $request->input('status_notes');
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
