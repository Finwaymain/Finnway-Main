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
        $validTabs = ['all', 'bike', 'auto', 'car', 'home_service'];
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
        $kit->description = $request->description;
        $kit->items_included = array_values(array_unique(array_filter($items)));
        $kit->is_compulsory = $request->has('is_compulsory') ? true : false;
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
     * Update Order Delivery Status
     */
    public function updateOrderStatus(Request $request, $id)
    {
        $order = DriverKitOrder::findOrFail($id);

        $request->validate([
            'delivery_status' => 'required|in:processing,dispatched,delivered',
            'tracking_number' => 'nullable|string',
            'courier_partner' => 'nullable|string',
        ]);

        $order->delivery_status = $request->delivery_status;
        $order->tracking_number = $request->tracking_number;
        $order->courier_partner = $request->courier_partner;
        $order->save();

        return redirect()->back()->with('success', "Order #{$order->order_number} status updated to " . ucfirst($order->delivery_status));
    }
}
