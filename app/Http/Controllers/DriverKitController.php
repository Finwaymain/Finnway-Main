<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DriverKit;
use App\Models\DriverKitOrder;
use App\Models\Driver;

class DriverKitController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * List all partner kits categorized by role
     */
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'all');
        $validTabs = ['all', 'bike', 'auto', 'car', 'male_salon', 'female_salon', 'cook_made', 'tutor', 'cleaner', 'technician', 'home_service'];
        if (!in_array($tab, $validTabs)) {
            $tab = 'all';
        }

        $query = DriverKit::query();
        if ($tab !== 'all') {
            $query->where('category_code', $tab);
        }

        $kits = $query->orderBy('id', 'asc')->get();

        // Statistics
        $totalKits = DriverKit::count();
        $compulsoryCount = DriverKit::where('is_compulsory', true)->count();
        $totalOrders = DriverKitOrder::where('payment_status', 'paid')->count();
        $totalRevenue = DriverKitOrder::where('payment_status', 'paid')->sum('amount');

        return view('driver_kits.index', compact(
            'kits',
            'tab',
            'totalKits',
            'compulsoryCount',
            'totalOrders',
            'totalRevenue'
        ));
    }

    /**
     * Update Kit Details
     */
    public function update(Request $request, $id)
    {
        $kit = DriverKit::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:150',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'items_included' => 'nullable|array',
        ]);

        // Process custom items or selected items
        $items = $request->input('items_included', []);
        if ($request->filled('custom_item')) {
            $items[] = trim($request->custom_item);
        }

        $kit->title = $request->title;
        $kit->price = $request->price;
        if ($request->filled('mrp')) $kit->mrp = $request->mrp;
        if ($request->filled('cashback_amount')) $kit->cashback_amount = $request->cashback_amount;
        if ($request->filled('stock_quantity')) $kit->stock_quantity = $request->stock_quantity;
        if ($request->filled('sku')) $kit->sku = $request->sku;
        $kit->description = $request->description;
        $kit->items_included = array_values(array_unique(array_filter($items)));
        $kit->is_compulsory = $request->has('is_compulsory') ? true : false;
        $kit->booking_required = $request->has('booking_required') ? true : false;
        $kit->is_active = $request->has('is_active') ? true : false;

        if ($request->hasFile('image')) {
            $imageName = 'kit_' . time() . '.' . $request->image->extension();
            $request->image->move(public_path('assets/images/kits'), $imageName);
            $kit->image = 'assets/images/kits/' . $imageName;
        }

        $kit->save();

        return redirect()->route('driver-kits.index', ['tab' => $kit->category_code])
            ->with('success', "{$kit->title} updated successfully.");
    }

    /**
     * Toggle Category-Level Compulsory Setting via AJAX
     */
    public function toggleCompulsory(Request $request, $id)
    {
        $kit = DriverKit::findOrFail($id);
        $kit->is_compulsory = !$kit->is_compulsory;
        $kit->save();

        return response()->json([
            'success' => true,
            'is_compulsory' => $kit->is_compulsory,
            'message' => "Compulsory status for {$kit->title} set to " . ($kit->is_compulsory ? 'MANDATORY' : 'OPTIONAL'),
        ]);
    }

    /**
     * Toggle Kit Active Status via AJAX
     */
    public function toggleActive(Request $request, $id)
    {
        $kit = DriverKit::findOrFail($id);
        $kit->is_active = !$kit->is_active;
        $kit->save();

        return response()->json([
            'success' => true,
            'is_active' => $kit->is_active,
        ]);
    }

    /**
     * View Driver Kit Orders & Delivery Tracking
     */
    public function orders(Request $request)
    {
        $status = $request->query('status', 'all');
        $search = $request->query('search', '');

        $query = DriverKitOrder::with('driver')->orderBy('id', 'desc');

        if ($status !== 'all') {
            $query->where('delivery_status', $status);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('receiver_name', 'like', "%{$search}%")
                  ->orWhere('receiver_phone', 'like', "%{$search}%")
                  ->orWhere('tracking_number', 'like', "%{$search}%");
            });
        }

        $orders = $query->paginate(20);

        return view('driver_kits.orders', compact('orders', 'status', 'search'));
    }

    /**
     * Update Order Delivery Status & Courier Assignment
     */
    public function updateOrderStatus(Request $request, $id)
    {
        $order = DriverKitOrder::findOrFail($id);

        $request->validate([
            'delivery_status' => 'required|in:booked,processing,picked_up,in_transit,out_for_delivery,delivered,cancelled',
            'tracking_code' => 'nullable|string',
            'courier_partner' => 'nullable|string',
            'tracking_url' => 'nullable|string',
            'expected_delivery_date' => 'nullable|string',
            'delivery_partner_name' => 'nullable|string',
            'delivery_partner_phone' => 'nullable|string',
            'delivery_partner_vehicle' => 'nullable|string',
            'delivery_partner_id' => 'nullable|string',
        ]);

        $newStatus = $request->delivery_status;
        $order->delivery_status = $newStatus;
        if ($request->filled('tracking_code')) {
            $order->tracking_code = $request->tracking_code;
            $order->tracking_number = $request->tracking_code;
        }
        if ($request->filled('courier_partner')) {
            $order->courier_partner = $request->courier_partner;
        }
        if ($request->filled('tracking_url')) {
            $order->tracking_url = $request->tracking_url;
        }
        if ($request->filled('expected_delivery_date')) {
            $order->expected_delivery_date = $request->expected_delivery_date;
        }
        if ($request->filled('delivery_partner_name')) {
            $order->delivery_partner_name = $request->delivery_partner_name;
        }
        if ($request->filled('delivery_partner_phone')) {
            $order->delivery_partner_phone = $request->delivery_partner_phone;
        }
        if ($request->filled('delivery_partner_vehicle')) {
            $order->delivery_partner_vehicle = $request->delivery_partner_vehicle;
        }
        if ($request->filled('delivery_partner_id')) {
            $order->delivery_partner_id = $request->delivery_partner_id;
        }

        // Build updated timeline
        $nowFormatted = date('d M Y, h:i A');
        $timeline = is_array($order->status_timeline) ? $order->status_timeline : (json_decode($order->status_timeline ?? '[]', true) ?: []);
        if (empty($timeline)) {
            $timeline = [
                ['status' => 'booked', 'title' => 'Booking Confirmed', 'date' => $order->purchased_at ? $order->purchased_at->format('d M Y, h:i A') : $nowFormatted, 'description' => 'Your partner marketing kit has been booked successfully.', 'is_completed' => true, 'is_current' => false],
                ['status' => 'picked_up', 'title' => 'Picked Up', 'date' => $nowFormatted, 'description' => 'Picked up by ' . ($order->courier_partner ?? 'courier partner') . '.', 'is_completed' => false, 'is_current' => false],
                ['status' => 'in_transit', 'title' => 'In Transit', 'date' => 'Pending', 'description' => 'Parcel is in transit to destination hub.', 'is_completed' => false, 'is_current' => false],
                ['status' => 'out_for_delivery', 'title' => 'Out for Delivery', 'date' => 'Pending', 'description' => 'Parcel is with delivery executive and will be delivered today.', 'is_completed' => false, 'is_current' => false],
                ['status' => 'delivered', 'title' => 'Delivered', 'date' => 'Pending', 'description' => 'Successfully delivered to partner address.', 'is_completed' => false, 'is_current' => false],
            ];
        }

        // Mark completion according to new status
        $statusLevels = ['booked' => 1, 'processing' => 1, 'picked_up' => 2, 'in_transit' => 3, 'out_for_delivery' => 4, 'delivered' => 5];
        $currentLevel = $statusLevels[$newStatus] ?? 1;

        foreach ($timeline as &$item) {
            $itemLevel = $statusLevels[$item['status']] ?? 1;
            if ($itemLevel < $currentLevel) {
                $item['is_completed'] = true;
                $item['is_current'] = false;
            } elseif ($itemLevel === $currentLevel) {
                $item['is_completed'] = true;
                $item['is_current'] = true;
                $item['date'] = $nowFormatted;
            } else {
                $item['is_completed'] = false;
                $item['is_current'] = false;
            }
        }
        $order->status_timeline = $timeline;

        // If marked delivered and kit had cashback, credit partner wallet
        if ($newStatus === 'delivered' && $order->payment_status === 'paid') {
            $kit = $order->kit;
            if ($kit && floatval($kit->cashback_amount ?? 0) > 0) {
                $driver = $order->driver;
                if ($driver) {
                    $cashback = floatval($kit->cashback_amount);
                    \Illuminate\Support\Facades\DB::table('tj_conducteur')->where('id', $driver->id)->increment('amount', $cashback);
                    \Illuminate\Support\Facades\DB::table('tj_conducteur_transaction')->insert([
                        'id_conducteur' => $driver->id,
                        'amount' => $cashback,
                        'payment_method' => 'wallet',
                        'type' => 'credit',
                        'description' => "Partner Kit Delivery Cashback (#{$order->order_number})",
                        'creer' => date('Y-m-d H:i:s'),
                        'modifier' => date('Y-m-d H:i:s'),
                    ]);
                }
            }
        }

        $order->save();

        return redirect()->back()->with('success', "Order #{$order->order_number} status updated to " . ucfirst(str_replace('_', ' ', $newStatus)));
    }

    /**
     * Printable Order Invoice View
     */
    public function invoice($id)
    {
        $order = DriverKitOrder::with(['driver', 'kit'])->findOrFail($id);
        return view('driver_kits.invoice', compact('order'));
    }
}
