<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DriverKit;
use App\Models\DriverKitOrder;
use App\Models\Driver;
use App\Models\MarketplaceProduct;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class DriverKitController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function getPartnerTypes(): array
    {
        return [
            'bike' => 'Bike Taxi / Rider Partner',
            'auto' => 'Auto Rickshaw Partner',
            'car' => 'Cab / Car Taxi Partner',
            'delivery' => 'Delivery Partner',
            'male_salon' => 'Male Salon / Grooming Partner',
            'female_salon' => 'Female Salon / Beauty Partner',
            'cook_made' => 'Cook / Maid Home Partner',
            'tutor' => 'Tutor / Education Partner',
            'cleaner' => 'House Cleaner / Deep Cleaning Partner',
            'technician' => 'Electrician / Plumber / Technician Partner',
            'home_service' => 'General Home Service Partner',
        ];
    }

    private function processProducts(Request $request): array
    {
        $raw = $request->input('products', []);
        $processed = [];
        $itemsIncluded = [];

        $kitDir = public_path('assets/images/kits');
        if (!File::isDirectory($kitDir)) {
            File::makeDirectory($kitDir, 0777, true, true);
        }

        if (is_array($raw)) {
            foreach ($raw as $idx => $p) {
                if (empty($p['name'])) continue;

                $name = trim($p['name']);
                $variant = !empty($p['variant']) ? trim($p['variant']) : '';
                $qty = max(1, intval($p['quantity'] ?? 1));
                $isFree = isset($p['is_free']) && ($p['is_free'] == '1' || $p['is_free'] === true || $p['is_free'] === 'true' || $p['is_free'] === 'free');
                $price = $isFree ? 0.0 : floatval($p['price'] ?? 0);
                $isMandatory = isset($p['is_mandatory']) && ($p['is_mandatory'] == '1' || $p['is_mandatory'] === true || $p['is_mandatory'] === 'true' || $p['is_mandatory'] === 'mandatory');
                $imageUrl = $p['image'] ?? '';

                if ($request->hasFile("products.{$idx}.image_file")) {
                    $file = $request->file("products.{$idx}.image_file");
                    $imgName = 'prod_' . time() . "_{$idx}." . $file->extension();
                    $file->move($kitDir, $imgName);
                    $imageUrl = 'assets/images/kits/' . $imgName;
                }

                $processed[] = [
                    'id' => !empty($p['id']) ? intval($p['id']) : null,
                    'name' => $name,
                    'image' => $imageUrl,
                    'variant' => $variant,
                    'quantity' => $qty,
                    'is_free' => $isFree,
                    'price' => $price,
                    'is_mandatory' => $isMandatory,
                ];

                $label = $name;
                if ($variant) $label .= " ({$variant})";
                if ($qty > 1) $label .= " x {$qty}";
                $itemsIncluded[] = $label;
            }
        }

        return [$processed, $itemsIncluded];
    }

    /**
     * List all partner kits categorized by role
     */
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'all');
        $validTabs = ['all', 'bike', 'auto', 'car', 'delivery', 'male_salon', 'female_salon', 'cook_made', 'tutor', 'cleaner', 'technician', 'home_service'];
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
     * Show Create Kit Form
     */
    public function create()
    {
        $partnerTypes = $this->getPartnerTypes();
        $availableProducts = MarketplaceProduct::with('primaryImage', 'images')
            ->where('status', 'active')
            ->orderBy('id', 'desc')
            ->take(50)
            ->get();

        return view('driver_kits.create', compact('partnerTypes', 'availableProducts'));
    }

    /**
     * Store Newly Created Kit
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:150',
            'category_code' => 'required|string',
            'price' => 'required|numeric|min:0',
            'mrp' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'cashback_amount' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:5120',
            'images.*' => 'nullable|image|max:5120',
        ]);

        $kit = new DriverKit();
        $kit->title = $request->title;
        $kit->category_code = $request->category_code;
        $kit->sku = $request->filled('sku') ? $request->sku : ('KIT-' . strtoupper($request->category_code) . '-' . strtoupper(Str::random(6)));
        $kit->description = $request->description;
        $kit->price = $request->price;
        $kit->mrp = $request->filled('mrp') ? $request->mrp : $request->price;
        $kit->cost_price = $request->filled('cost_price') ? $request->cost_price : 0;
        $kit->cashback_amount = $request->filled('cashback_amount') ? $request->cashback_amount : 0;
        $kit->stock_quantity = $request->filled('stock_quantity') ? $request->stock_quantity : 100;
        
        list($processedProducts, $itemsIncluded) = $this->processProducts($request);
        $kit->products = $processedProducts;
        $kit->items_included = $itemsIncluded;

        $kit->is_compulsory = $request->has('is_compulsory') ? true : false;
        $kit->booking_required = $request->has('booking_required') ? true : false;
        $kit->is_active = $request->has('is_active') ? true : true;
        
        $status = $request->input('status', 'published');
        $kit->status = in_array($status, ['published', 'draft']) ? $status : 'published';

        $kitDir = public_path('assets/images/kits');
        if (!File::isDirectory($kitDir)) {
            File::makeDirectory($kitDir, 0777, true, true);
        }

        $galleryImages = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if ($file && $file->isValid()) {
                    $imgName = 'kit_' . time() . '_' . uniqid() . '.' . $file->extension();
                    $file->move($kitDir, $imgName);
                    $galleryImages[] = 'assets/images/kits/' . $imgName;
                }
            }
        }
        $kit->images = $galleryImages;

        if ($request->hasFile('image')) {
            $imageName = 'kit_' . time() . '_' . uniqid() . '.' . $request->image->extension();
            $request->image->move($kitDir, $imageName);
            $kit->image = 'assets/images/kits/' . $imageName;
        } elseif (!empty($galleryImages)) {
            $kit->image = $galleryImages[0];
        }

        $kit->save();

        return redirect()->route('driver-kits.index', ['tab' => $kit->category_code])
            ->with('success', "Driver Kit '{$kit->title}' created successfully!");
    }

    /**
     * Show Edit Kit Form
     */
    public function edit($id)
    {
        $kit = DriverKit::findOrFail($id);
        $partnerTypes = $this->getPartnerTypes();
        $availableProducts = MarketplaceProduct::with('primaryImage', 'images')
            ->where('status', 'active')
            ->orderBy('id', 'desc')
            ->take(50)
            ->get();

        return view('driver_kits.edit', compact('kit', 'partnerTypes', 'availableProducts'));
    }

    /**
     * Update Kit Details
     */
    public function update(Request $request, $id)
    {
        $kit = DriverKit::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:150',
            'category_code' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'mrp' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'cashback_amount' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:5120',
            'images.*' => 'nullable|image|max:5120',
        ]);

        $kit->title = $request->title;
        if ($request->filled('category_code')) {
            $kit->category_code = $request->category_code;
        }
        $kit->price = $request->price;
        if ($request->filled('mrp')) $kit->mrp = $request->mrp;
        if ($request->filled('cost_price')) $kit->cost_price = $request->cost_price;
        if ($request->filled('cashback_amount')) $kit->cashback_amount = $request->cashback_amount;
        if ($request->filled('stock_quantity')) $kit->stock_quantity = $request->stock_quantity;
        if ($request->filled('sku')) $kit->sku = $request->sku;
        $kit->description = $request->description;

        if ($request->has('products')) {
            list($processedProducts, $itemsIncluded) = $this->processProducts($request);
            $kit->products = $processedProducts;
            $kit->items_included = $itemsIncluded;
        } elseif ($request->has('items_included')) {
            $items = $request->input('items_included', []);
            if ($request->filled('custom_item')) {
                $items[] = trim($request->custom_item);
            }
            $kit->items_included = array_values(array_unique(array_filter($items)));
        }

        $kit->is_compulsory = $request->has('is_compulsory') ? true : false;
        $kit->booking_required = $request->has('booking_required') ? true : false;
        $kit->is_active = $request->has('is_active') ? true : false;

        if ($request->filled('status')) {
            $kit->status = in_array($request->status, ['published', 'draft']) ? $request->status : 'published';
        }

        $kitDir = public_path('assets/images/kits');
        if (!File::isDirectory($kitDir)) {
            File::makeDirectory($kitDir, 0777, true, true);
        }

        $currentImages = is_array($kit->images) ? $kit->images : (json_decode($kit->images ?? '[]', true) ?: []);

        // Handle removal of specific images
        if ($request->has('remove_images')) {
            $toRemove = (array)$request->input('remove_images');
            $currentImages = array_values(array_filter($currentImages, function($img) use ($toRemove) {
                return !in_array($img, $toRemove);
            }));
        }

        // Handle newly uploaded gallery images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if ($file && $file->isValid()) {
                    $imgName = 'kit_' . time() . '_' . uniqid() . '.' . $file->extension();
                    $file->move($kitDir, $imgName);
                    $currentImages[] = 'assets/images/kits/' . $imgName;
                }
            }
        }
        $kit->images = array_values($currentImages);

        // Handle single primary image upload
        if ($request->hasFile('image')) {
            $imageName = 'kit_' . time() . '_' . uniqid() . '.' . $request->image->extension();
            $request->image->move($kitDir, $imageName);
            $kit->image = 'assets/images/kits/' . $imageName;
        } elseif (empty($kit->image) && !empty($currentImages)) {
            $kit->image = $currentImages[0];
        }

        $kit->save();

        return redirect()->route('driver-kits.index', ['tab' => $kit->category_code])
            ->with('success', "Driver Kit '{$kit->title}' updated successfully.");
    }

    /**
     * Delete Kit
     */
    public function destroy($id)
    {
        $kit = DriverKit::findOrFail($id);
        $tab = $kit->category_code;
        $title = $kit->title;
        $kit->delete();

        return redirect()->route('driver-kits.index', ['tab' => $tab])
            ->with('success', "Kit '{$title}' deleted successfully.");
    }

    /**
     * AJAX Product Search for Kit Builder
     */
    public function searchProducts(Request $request)
    {
        $q = trim($request->query('q', ''));
        $query = MarketplaceProduct::with('primaryImage', 'images')->where('status', 'active');

        if (!empty($q)) {
            $query->where(function ($b) use ($q) {
                $b->where('title', 'like', "%{$q}%")
                  ->orWhere('brand_name', 'like', "%{$q}%")
                  ->orWhere('description', 'like', "%{$q}%");
            });
        }

        $products = $query->orderBy('id', 'desc')->take(30)->get()->map(function ($prod) {
            $img = '';
            if ($prod->primaryImage && !empty($prod->primaryImage->image_path)) {
                $img = $prod->primaryImage->image_path;
            } elseif ($prod->images->isNotEmpty() && !empty($prod->images->first()->image_path)) {
                $img = $prod->images->first()->image_path;
            }
            return [
                'id' => $prod->id,
                'title' => $prod->title,
                'price' => floatval($prod->price),
                'image' => $img,
                'stock' => intval($prod->stock_quantity ?? 0),
            ];
        });

        return response()->json([
            'success' => true,
            'products' => $products,
        ]);
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
