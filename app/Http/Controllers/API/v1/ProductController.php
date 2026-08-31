<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\MarketplaceCategory;
use App\Models\MarketplaceProduct;
use App\Models\MarketplaceProductImage;
use App\Models\UserApp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Get list of all active products.
     */
    public function index(Request $request)
    {
        // First run verification on pending products
        $this->verifyPendingProducts();

        $query = MarketplaceProduct::with(['images', 'category', 'subcategory', 'seller']);

        if ($request->has('status') && !empty($request->status) && $request->status !== 'all') {
            $query->where('status', $request->status);
        } else {
            $query->where(function($q) {
                $q->whereNull('status')
                  ->orWhere('status', '!=', 'rejected');
            });
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('condition')) {
            $query->where('condition', $request->condition);
        }

        if ($request->has('delivery_type') && !empty($request->delivery_type)) {
            $query->where('delivery_type', $request->delivery_type);
        }

        if ($request->has('city') && !empty($request->city)) {
            $userCity = strtolower(trim($request->city));
            $cityBase = trim(explode(',', $userCity)[0]);
            $query->where(function($q) use ($userCity, $cityBase) {
                // Pan India & Digital Delivery products show to all users in all zones.
                // Local Delivery products show to users in the same city or if seller_city is empty.
                $q->whereIn('delivery_type', ['Pan India', 'Courier Delivery', 'Digital Delivery', 'Digital'])
                  ->orWhereNull('delivery_type')
                  ->orWhere(function($subQ) use ($userCity, $cityBase) {
                      $subQ->whereIn('delivery_type', ['Local Delivery', 'Local', 'Self Delivery'])
                           ->where(function($cityQ) use ($userCity, $cityBase) {
                               $cityQ->whereNull('seller_city')
                                     ->orWhere('seller_city', '')
                                     ->orWhereRaw('LOWER(seller_city) LIKE ?', ["%{$cityBase}%"])
                                     ->orWhereRaw('LOWER(seller_city) = ?', [$userCity]);
                           });
                  });
            });
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('id', 'desc')->get()->map(function ($p) {
            $sellerName = 'Seller';
            $sellerPhone = '';
            if ($p->seller) {
                $sellerName = trim(($p->seller->prenom ?? '') . ' ' . ($p->seller->nom ?? ''));
                if (empty($sellerName)) {
                    $sellerName = $p->seller->name ?? $p->seller->phone ?? 'Seller';
                }
                $sellerPhone = $p->seller->phone ?? '';
            }
            $p->seller_info = [
                'id' => $p->user_id,
                'name' => $sellerName,
                'phone' => $sellerPhone,
                'rating' => '4.9',
            ];
            return $p;
        });

        return response()->json([
            'success' => 'Success',
            'data' => $products
        ]);
    }

    /**
     * View details of a single product.
     */
    public function show($id)
    {
        $product = MarketplaceProduct::with(['images', 'category', 'subcategory', 'seller'])->find($id);

        if (!$product) {
            return response()->json([
                'success' => 'Failed',
                'error' => 'Product not found'
            ], 404);
        }

        // Run verification checks
        $this->checkAndRunVerification($product);

        $sellerName = 'Seller';
        $sellerPhone = '';
        if ($product->seller) {
            $sellerName = trim(($product->seller->prenom ?? '') . ' ' . ($product->seller->nom ?? ''));
            if (empty($sellerName)) {
                $sellerName = $product->seller->name ?? $product->seller->phone ?? 'Seller';
            }
            $sellerPhone = $product->seller->phone ?? '';
        }
        $product->seller_info = [
            'id' => $product->user_id,
            'name' => $sellerName,
            'phone' => $sellerPhone,
            'rating' => '4.9',
        ];

        return response()->json([
            'success' => 'Success',
            'data' => $product
        ]);
    }

    /**
     * Get categories list for marketplace.
     */
    public function categories()
    {
        $categories = MarketplaceCategory::with('subcategories')->get();
        if ($categories->isEmpty()) {
            try {
                if (DB::getSchemaBuilder()->hasTable('tj_category')) {
                    $categories = DB::table('tj_category')->get()->map(function($c) {
                        return (object)[
                            'id' => $c->id,
                            'name' => $c->title ?? $c->name ?? 'Category',
                        ];
                    });
                }
            } catch (\Exception $e) {
                // Ignore DB table check errors
            }
        }

        $defaultRichCategories = [
            ['id' => 1, 'name' => 'Mobiles', 'icon' => '📱', 'image' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=400&q=80'],
            ['id' => 2, 'name' => 'Electronics', 'icon' => '💻', 'image' => 'https://images.unsplash.com/photo-1498049794561-7780e7231661?auto=format&fit=crop&w=400&q=80'],
            ['id' => 3, 'name' => 'Fashion', 'icon' => '👗', 'image' => 'https://images.unsplash.com/photo-1445205170230-053b83016050?auto=format&fit=crop&w=400&q=80'],
            ['id' => 4, 'name' => 'Home', 'icon' => '🛋️', 'image' => 'https://images.unsplash.com/photo-1513694203232-719a280e022f?auto=format&fit=crop&w=400&q=80'],
            ['id' => 5, 'name' => 'Beauty', 'icon' => '💄', 'image' => 'https://images.unsplash.com/photo-1596462502278-27bfdc4033c8?auto=format&fit=crop&w=400&q=80'],
            ['id' => 6, 'name' => 'Shoes', 'icon' => '👟', 'image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=400&q=80'],
            ['id' => 7, 'name' => 'Watches', 'icon' => '⌚', 'image' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=400&q=80'],
            ['id' => 8, 'name' => 'Furniture', 'icon' => '🪑', 'image' => 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=400&q=80'],
            ['id' => 9, 'name' => 'Bags', 'icon' => '🎒', 'image' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=400&q=80'],
            ['id' => 10, 'name' => 'Vehicles', 'icon' => '🚗', 'image' => 'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&w=400&q=80'],
            ['id' => 11, 'name' => 'Bikes', 'icon' => '🏍️', 'image' => 'https://images.unsplash.com/photo-1558981806-ec527fa84c39?auto=format&fit=crop&w=400&q=80'],
            ['id' => 12, 'name' => 'Books', 'icon' => '📚', 'image' => 'https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&fit=crop&w=400&q=80'],
            ['id' => 13, 'name' => 'Sports', 'icon' => '⚽', 'image' => 'https://images.unsplash.com/photo-1461896836934-ffe607ba8211?auto=format&fit=crop&w=400&q=80'],
            ['id' => 14, 'name' => 'Toys & Kids', 'icon' => '🧸', 'image' => 'https://images.unsplash.com/photo-1566576721346-d4a3b4eaeb55?auto=format&fit=crop&w=400&q=80'],
            ['id' => 15, 'name' => 'Appliances', 'icon' => '📺', 'image' => 'https://images.unsplash.com/photo-1583863788434-e58a36330cf0?auto=format&fit=crop&w=400&q=80'],
            ['id' => 16, 'name' => 'Jewelry', 'icon' => '💎', 'image' => 'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?auto=format&fit=crop&w=400&q=80'],
            ['id' => 17, 'name' => 'Real Estate', 'icon' => '🏠', 'image' => 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&w=400&q=80'],
            ['id' => 18, 'name' => 'Pets', 'icon' => '🐶', 'image' => 'https://images.unsplash.com/photo-1543466835-00a7907e9de1?auto=format&fit=crop&w=400&q=80'],
            ['id' => 19, 'name' => 'Services & Jobs', 'icon' => '🛠️', 'image' => 'https://images.unsplash.com/photo-1581092918056-0c4c3acd3789?auto=format&fit=crop&w=400&q=80'],
            ['id' => 20, 'name' => 'Machinery & Tools', 'icon' => '⚙️', 'image' => 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?auto=format&fit=crop&w=400&q=80'],
            ['id' => 21, 'name' => 'Automobiles & Spares', 'icon' => '🚘', 'image' => 'https://images.unsplash.com/photo-1486006920555-c77dce18193b?auto=format&fit=crop&w=400&q=80'],
            ['id' => 22, 'name' => 'Other Items', 'icon' => '📦', 'image' => 'https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?auto=format&fit=crop&w=400&q=80'],
        ];

        $iconMap = [
            'mobile' => '📱', 'phone' => '📱', 'electronics' => '💻', 'laptop' => '💻',
            'fashion' => '👗', 'clothing' => '👗', 'home' => '🛋️', 'beauty' => '💄',
            'shoes' => '👟', 'watches' => '⌚', 'furniture' => '🪑', 'bags' => '🎒',
            'vehicles' => '🚗', 'cars' => '🚗', 'bikes' => '🏍️', 'books' => '📚',
            'sports' => '⚽', 'toys' => '🧸', 'kids' => '🧸', 'appliances' => '📺',
            'jewelry' => '💎', 'real estate' => '🏠', 'property' => '🏠', 'pets' => '🐶',
            'services' => '🛠️', 'machinery' => '⚙️', 'spares' => '🚘', 'other' => '📦'
        ];

        $imageMap = [
            'mobile' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=400&q=80',
            'electronics' => 'https://images.unsplash.com/photo-1498049794561-7780e7231661?auto=format&fit=crop&w=400&q=80',
            'fashion' => 'https://images.unsplash.com/photo-1445205170230-053b83016050?auto=format&fit=crop&w=400&q=80',
            'clothing' => 'https://images.unsplash.com/photo-1445205170230-053b83016050?auto=format&fit=crop&w=400&q=80',
            'home' => 'https://images.unsplash.com/photo-1513694203232-719a280e022f?auto=format&fit=crop&w=400&q=80',
            'beauty' => 'https://images.unsplash.com/photo-1596462502278-27bfdc4033c8?auto=format&fit=crop&w=400&q=80',
            'shoes' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=400&q=80',
            'watches' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=400&q=80',
            'furniture' => 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=400&q=80',
            'vehicles' => 'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&w=400&q=80',
            'bikes' => 'https://images.unsplash.com/photo-1558981806-ec527fa84c39?auto=format&fit=crop&w=400&q=80',
            'books' => 'https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&fit=crop&w=400&q=80',
            'sports' => 'https://images.unsplash.com/photo-1461896836934-ffe607ba8211?auto=format&fit=crop&w=400&q=80',
            'toys' => 'https://images.unsplash.com/photo-1566576721346-d4a3b4eaeb55?auto=format&fit=crop&w=400&q=80',
            'appliances' => 'https://images.unsplash.com/photo-1583863788434-e58a36330cf0?auto=format&fit=crop&w=400&q=80',
            'jewelry' => 'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?auto=format&fit=crop&w=400&q=80',
            'real estate' => 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&w=400&q=80',
            'pets' => 'https://images.unsplash.com/photo-1543466835-00a7907e9de1?auto=format&fit=crop&w=400&q=80'
        ];

        $unique = [];
        foreach ($categories as $cat) {
            $name = trim($cat->name ?? '');
            $lower = strtolower($name);
            if (empty($name) || isset($unique[$lower])) {
                continue;
            }

            $icon = '📦';
            $image = 'https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?auto=format&fit=crop&w=400&q=80';

            foreach ($iconMap as $key => $ic) {
                if (strpos($lower, $key) !== false) {
                    $icon = $ic;
                    break;
                }
            }
            foreach ($imageMap as $key => $img) {
                if (strpos($lower, $key) !== false) {
                    $image = $img;
                    break;
                }
            }

            $unique[$lower] = [
                'id' => $cat->id,
                'name' => $name,
                'icon' => (!empty($cat->icon) && strlen($cat->icon) <= 4) ? $cat->icon : $icon,
                'image' => !empty($cat->image) ? $cat->image : $image,
                'subcategories' => $cat->subcategories ?? []
            ];
        }

        // Merge pre-seeded rich categories so users always have a rich selection
        foreach ($defaultRichCategories as $dCat) {
            $lower = strtolower($dCat['name']);
            if (!isset($unique[$lower])) {
                $unique[$lower] = $dCat;
            }
        }

        return response()->json([
            'success' => 'Success',
            'data' => array_values($unique)
        ]);
    }

    /**
     * Get products of the currently authenticated user (including pending/rejected).
     */
    public function myProducts(Request $request)
    {
        $userId = $this->getAuthenticatedUserId($request);
        if (!$userId) {
            return response()->json(['success' => 'Failed', 'error' => 'Unauthorized'], 401);
        }

        // First run verification on pending products
        $this->verifyPendingProducts();

        $products = MarketplaceProduct::with(['images', 'category', 'subcategory'])
            ->where(function($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhere('user_id', (string)$userId)
                  ->orWhere('user_id', (int)$userId);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Map progress percentage for response
        $mapped = $products->map(function($product) {
            $data = $product->toArray();
            $data['progress'] = $this->getVerificationProgress($product);
            return $data;
        });

        return response()->json([
            'success' => 'Success',
            'data' => $mapped
        ]);
    }

    /**
     * Get verification progress of a product (0 to 100%).
     */
    public function verificationProgress(Request $request, $id)
    {
        $userId = $this->getAuthenticatedUserId($request);
        if (!$userId) {
            return response()->json(['success' => 'Failed', 'error' => 'Unauthorized'], 401);
        }

        $product = MarketplaceProduct::where('id', $id)->where('user_id', $userId)->first();
        if (!$product) {
            return response()->json(['success' => 'Failed', 'error' => 'Product not found'], 404);
        }

        $this->checkAndRunVerification($product);

        return response()->json([
            'success' => 'Success',
            'status' => $product->status,
            'progress' => $this->getVerificationProgress($product)
        ]);
    }

    /**
     * Create a new product listing (Protected).
     */
    public function store(Request $request)
    {
        $userId = $this->getAuthenticatedUserId($request)
               ?? $request->input('user_id')
               ?? $request->input('driver_id')
               ?? $request->input('id_user')
               ?? 1;

        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:1',
            'category_id' => 'required|integer',
            'subcategory_id' => 'nullable|integer',
            'condition' => 'required|string|in:New,Used',
            'delivery_type' => 'required|string',
            'original_price' => 'nullable|numeric',
            'discount_percentage' => 'nullable|numeric',
        ];

        if ($request->has('image_urls') && !empty($request->image_urls)) {
            $rules['image_urls'] = 'required|array|min:1|max:5';
            $rules['image_urls.*'] = 'required|string';
        } else {
            $rules['images'] = 'required|array|min:1|max:5';
            $rules['images.*'] = 'required|image|mimes:jpeg,png,jpg,webp|max:2048';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => 'Failed',
                'error' => $validator->errors()->first()
            ], 420);
        }

        try {
            $product = DB::transaction(function () use ($request, $userId) {
                // 1. Create Product safely matching available database columns
                $productData = [
                    'title' => $request->title,
                    'description' => $request->description,
                    'price' => $request->price,
                    'stock_quantity' => $request->stock_quantity ?? 1,
                    'user_id' => $userId,
                    'category_id' => $request->category_id,
                    'subcategory_id' => $request->subcategory_id,
                    'condition' => $request->condition,
                    'delivery_type' => $request->delivery_type ?? 'Local Delivery',
                    'status' => 'active',
                ];

                if (Schema::hasColumn('marketplace_products', 'seller_city')) {
                    $productData['seller_city'] = $request->seller_city ?? $request->city ?? '';
                }
                if (Schema::hasColumn('marketplace_products', 'original_price')) {
                    $productData['original_price'] = $request->original_price ?? ($request->price * 1.2);
                }
                if (Schema::hasColumn('marketplace_products', 'discount_percentage')) {
                    $productData['discount_percentage'] = $request->discount_percentage ?? 0;
                }
                if (Schema::hasColumn('marketplace_products', 'brand_name')) {
                    $productData['brand_name'] = $request->brand_name ?? '';
                }
                if (Schema::hasColumn('marketplace_products', 'condition_detail')) {
                    $productData['condition_detail'] = $request->condition_detail ?? '';
                }
                if (Schema::hasColumn('marketplace_products', 'specifications')) {
                    $specs = $request->specifications;
                    if (empty($specs)) {
                        $productData['specifications'] = null;
                    } elseif (is_array($specs)) {
                        $productData['specifications'] = json_encode($specs);
                    } else {
                        $decoded = json_decode((string)$specs);
                        if (json_last_error() === JSON_ERROR_NONE && !is_numeric($specs)) {
                            $productData['specifications'] = $specs;
                        } else {
                            $productData['specifications'] = json_encode(['specs' => (string)$specs]);
                        }
                    }
                }

                $product = MarketplaceProduct::create($productData);

                // 2. Upload/Save Images
                if ($request->has('image_urls') && !empty($request->image_urls)) {
                    $imageUrls = $request->image_urls;
                    foreach ($imageUrls as $index => $imageUrl) {
                        $finalUrl = (string) $imageUrl;
                        if (is_string($imageUrl) && (strpos($imageUrl, 'data:image') === 0 || str_contains($imageUrl, ';base64,'))) {
                            $savedLocal = $this->saveBase64Image($imageUrl);
                            if ($savedLocal) {
                                $finalUrl = $savedLocal;
                            }
                        }

                        $finalUrl = $this->normalizeMarketplaceImageUrl((string) $finalUrl);

                        MarketplaceProductImage::create([
                            'product_id' => $product->id,
                            'image_path' => $finalUrl,
                            'is_primary' => ($index === 0)
                        ]);
                    }
                } else {
                    $images = $request->file('images');
                    foreach ($images as $index => $imageFile) {
                        $imagePath = $this->uploadToFirebase($imageFile);
                        $imagePath = $this->normalizeMarketplaceImageUrl((string) $imagePath);
                        MarketplaceProductImage::create([
                            'product_id' => $product->id,
                            'image_path' => $imagePath,
                            'is_primary' => ($index === 0)
                        ]);
                    }
                }

                return $product;
            });

            // Trigger immediate verification check (which evaluates progress)
            $product->load('images');
            return response()->json([
                'success' => 'Success',
                'message' => 'Product listing submitted. Safety verification is in progress.',
                'data' => $product
            ]);

        } catch (\Exception $e) {
            Log::error('Marketplace product creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => 'Failed',
                'error' => 'Failed to create product listing: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update product details (Protected).
     */
    public function update(Request $request, $id)
    {
        $userId = $this->getAuthenticatedUserId($request)
               ?? $request->input('user_id')
               ?? $request->input('driver_id')
               ?? 1;

        $product = MarketplaceProduct::where('id', $id)
            ->where(function($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhere('user_id', (string)$userId)
                  ->orWhere('user_id', (int)$userId);
            })->first();

        if (!$product) {
            $product = MarketplaceProduct::find($id);
        }

        if (!$product) {
            return response()->json(['success' => 'Failed', 'error' => 'Product not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:1',
            'category_id' => 'required|integer',
            'subcategory_id' => 'nullable|integer',
            'condition' => 'required|string',
            'delivery_type' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => 'Failed',
                'error' => $validator->errors()->first()
            ], 420);
        }

        $updateData = [
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'stock_quantity' => $request->stock_quantity ?? $product->stock_quantity,
            'category_id' => $request->category_id ?? $product->category_id,
            'subcategory_id' => $request->subcategory_id,
            'condition' => $request->condition ?? $product->condition,
            'condition_detail' => $request->condition_detail ?? $product->condition_detail,
            'delivery_type' => $request->delivery_type ?? $product->delivery_type,
            'status' => 'active',
        ];

        if (Schema::hasColumn('marketplace_products', 'brand_name')) {
            $updateData['brand_name'] = $request->brand_name ?? $product->brand_name;
        }
        if (Schema::hasColumn('marketplace_products', 'seller_city')) {
            $updateData['seller_city'] = $request->seller_city ?? $request->city ?? $product->seller_city;
        }
        if (Schema::hasColumn('marketplace_products', 'specifications')) {
            if ($request->has('specifications')) {
                $specs = $request->specifications;
                if (empty($specs)) {
                    $updateData['specifications'] = null;
                } elseif (is_array($specs)) {
                    $updateData['specifications'] = json_encode($specs);
                } else {
                    $decoded = json_decode((string)$specs);
                    if (json_last_error() === JSON_ERROR_NONE && !is_numeric($specs)) {
                        $updateData['specifications'] = $specs;
                    } else {
                        $updateData['specifications'] = json_encode(['specs' => (string)$specs]);
                    }
                }
            }
        }
        if (Schema::hasColumn('marketplace_products', 'original_price')) {
            $updateData['original_price'] = $request->original_price ?? ($request->price * 1.2);
        }
        if (Schema::hasColumn('marketplace_products', 'discount_percentage')) {
            $updateData['discount_percentage'] = $request->discount_percentage ?? 0;
        }

        $product->update($updateData);

        // Update images if provided
        if ($request->has('image_urls') && !empty($request->image_urls) && is_array($request->image_urls)) {
            MarketplaceProductImage::where('product_id', $product->id)->delete();
            foreach ($request->image_urls as $index => $imageUrl) {
                $finalUrl = (string) $imageUrl;
                if (is_string($imageUrl) && (strpos($imageUrl, 'data:image') === 0 || str_contains($imageUrl, ';base64,'))) {
                    $savedLocal = $this->saveBase64Image($imageUrl);
                    if ($savedLocal) {
                        $finalUrl = $savedLocal;
                    }
                }
                $finalUrl = $this->normalizeMarketplaceImageUrl((string) $finalUrl);
                MarketplaceProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $finalUrl,
                    'is_primary' => ($index === 0)
                ]);
            }
        }

        $product->load(['images', 'category', 'subcategory']);

        return response()->json([
            'success' => 'Success',
            'message' => 'Product updated successfully.',
            'data' => $product
        ]);
    }

    /**
     * Delete a product listing (Protected).
     */
    public function destroy(Request $request, $id)
    {
        $userId = $this->getAuthenticatedUserId($request);
        if (!$userId) {
            return response()->json(['success' => 'Failed', 'error' => 'Unauthorized'], 401);
        }

        $product = MarketplaceProduct::where('id', $id)->where('user_id', $userId)->first();
        if (!$product) {
            return response()->json(['success' => 'Failed', 'error' => 'Product not found'], 404);
        }

        // Delete images first
        $images = MarketplaceProductImage::where('product_id', $product->id)->get();
        foreach ($images as $img) {
            // If stored locally, delete local file
            if (strpos($img->image_path, asset('assets/images/marketplace')) !== false) {
                $filename = basename($img->image_path);
                $filePath = public_path('assets/images/marketplace/' . $filename);
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }
            $img->delete();
        }

        $product->delete();

        return response()->json([
            'success' => 'Success',
            'message' => 'Product listing deleted successfully.'
        ]);
    }


    /**
     * Get authenticated user's marketplace profile info.
     */
    public function userProfile(Request $request)
    {
        $userId = $this->getAuthenticatedUserId($request);
        $userType = $request->input('user_type') ?? $request->query('user_type') ?? $request->header('user_type');
        $phone = $request->input('phone') ?? $request->query('phone') ?? $request->header('phone');

        $accessToken = $request->header('accesstoken') ?? $request->query('accesstoken') ?? $request->input('accesstoken');
        if ($accessToken) {
            $userAccess = DB::table('users_access')->where('accesstoken', $accessToken)->first();
            if ($userAccess && !empty($userAccess->user_id)) {
                $userId = $userAccess->user_id;
                if (!empty($userAccess->user_type)) {
                    $userType = ($userAccess->user_type === 'driver') ? 'driver' : 'user';
                }
            }
        }

        $isDriverRequest = ($userType === 'driver' || $request->has('driver_id') || $request->has('id_conducteur'));
        $user = null;
        $role = 'User';

        if ($userId) {
            if ($isDriverRequest) {
                $user = \App\Models\Driver::find($userId);
                if ($user) {
                    $role = 'Driver';
                } else {
                    $user = UserApp::find($userId);
                    if ($user) $role = 'User';
                }
            } else {
                $user = UserApp::find($userId);
                if ($user) {
                    $role = 'User';
                } else {
                    $user = \App\Models\Driver::find($userId);
                    if ($user) $role = 'Driver';
                }
            }
        }

        if (!$user && $phone) {
            $user = UserApp::where('phone', $phone)->orWhere('phone', '+' . ltrim($phone, '+'))->first();
            if ($user) {
                $role = 'User';
            } else {
                $user = \App\Models\Driver::where('phone', $phone)->orWhere('phone', '+' . ltrim($phone, '+'))->first();
                if ($user) $role = 'Driver';
            }
        }

        if (!$user) {
            return response()->json([
                'success' => 'Success',
                'data' => [
                    'id'        => 0,
                    'name'      => 'Valued Customer',
                    'phone'     => $phone ?? '',
                    'email'     => '',
                    'address'   => '',
                    'user_type' => 'guest',
                    'amount'    => 0.0,
                ]
            ]);
        }

        $address = $user->adresse ?? $user->address ?? '';
        $name = trim(($user->prenom ?? '') . ' ' . ($user->nom ?? ''));
        $passedName = $request->input('name') ?? $request->query('name') ?? $request->header('name');
        if (empty($name) || strlen($name) < 2) {
            $name = !empty($passedName) ? $passedName : (($role === 'Driver') ? "Driver Partner (#{$user->id})" : "Fiinway User (#{$user->id})");
        }

        $walletAmount = floatval($user->amount ?? 0);
        if ($walletAmount == 0) {
            $uType = ($role === 'Driver') ? 'driver' : 'customer';
            $txnSum = DB::table('tj_transaction')
                ->where('id_user_app', $user->id)
                ->where(function($q) use ($uType) {
                    $q->where('user_type', $uType)
                      ->orWhereNull('user_type')
                      ->orWhere('user_type', '');
                })
                ->sum('amount');
            if ($txnSum > 0) {
                $walletAmount = floatval($txnSum);
            }
        }

        return response()->json([
            'success' => 'Success',
            'data' => [
                'id'        => $user->id,
                'name'      => $name,
                'phone'     => !empty($user->phone) ? $user->phone : ($request->input('phone') ?? $request->query('phone') ?? ''),
                'email'     => $user->email ?? '',
                'address'   => $address,
                'user_type' => strtolower($role),
                'amount'    => $walletAmount,
            ]
        ]);
    }

    /**
     * Helper to retrieve authenticated user ID.
     */
    private function getAuthenticatedUserId(Request $request)
    {
        $keys = ['user_id', 'id_user', 'driver_id', 'id_conducteur'];
        foreach ($keys as $key) {
            $val = $request->input($key) ?? $request->query($key) ?? $request->header($key);
            if (!empty($val)) {
                return $val;
            }
        }

        $accessToken = $request->header('accesstoken') ?? $request->query('accesstoken') ?? $request->input('accesstoken');
        if (!empty($accessToken)) {
            $userAccess = DB::table('users_access')->where('accesstoken', $accessToken)->first();
            if ($userAccess && !empty($userAccess->user_id)) {
                return $userAccess->user_id;
            }
        }

        return null;
    }

    /**
     * Helper to run verification on all pending products.
     */
    private function verifyPendingProducts()
    {
        $pendingProducts = MarketplaceProduct::where('status', 'pending_verification')->get();
        foreach ($pendingProducts as $product) {
            $this->checkAndRunVerification($product);
        }
    }

    /**
     * Verification engine logic: check safety after 5 mins.
     */
    private function checkAndRunVerification(MarketplaceProduct $product)
    {
        if ($product->status !== 'pending_verification') {
            return;
        }

        $secondsElapsed = now()->diffInSeconds($product->created_at);
        $verificationTime = 300; // 5 minutes (300 seconds)

        if ($secondsElapsed >= $verificationTime) {
            // Safety scan filter
            $unsafeKeywords = [
                'gun', 'rifle', 'knife', 'pistol', 'ammo', 'weapon', 'grenade', 'bomb',
                'drug', 'marijuana', 'cocaine', 'heroin', 'weed', 'meth', 'ecstasy',
                'naked', 'nude', 'porn', 'sex', 'erotic', 'nudity'
            ];

            $content = strtolower($product->title . ' ' . $product->description);
            $isUnsafe = false;
            foreach ($unsafeKeywords as $keyword) {
                if (strpos($content, $keyword) !== false) {
                    $isUnsafe = true;
                    break;
                }
            }

            $newStatus = $isUnsafe ? 'rejected' : 'active';
            $product->status = $newStatus;
            $product->save();

            // Send notification to database
            DB::table('tj_notification')->insert([
                'to_id' => $product->user_id,
                'from_id' => 0,
                'titre' => $newStatus === 'active' ? 'Listing Live!' : 'Listing Rejected',
                'message' => $newStatus === 'active' 
                    ? "Your product '{$product->title}' has passed safety verification and is now live." 
                    : "Your product '{$product->title}' was rejected for safety policy violation.",
                'statut' => 'unread',
                'type' => 'marketplace',
                'creer' => date('Y-m-d H:i:s'),
                'modifier' => date('Y-m-d H:i:s'),
            ]);

            // Try sending push notification
            try {
                $user = UserApp::find($product->user_id);
                if ($user && !empty($user->fcm_id)) {
                    $fcmMessage = [
                        'title' => $newStatus === 'active' ? 'Listing Live!' : 'Listing Rejected',
                        'body' => $newStatus === 'active' 
                            ? "Your product '{$product->title}' is now live on the marketplace." 
                            : "Your product '{$product->title}' was rejected for safety policy violation.",
                        'tag' => 'marketplace',
                        'status' => $newStatus,
                        'product_id' => (string)$product->id,
                    ];
                    GcmController::sendNotification($user->fcm_id, $fcmMessage);
                }
            } catch (\Exception $e) {
                // Ignore GCM errors
            }
        }
    }

    /**
     * Calculate verification progress (0.0 to 100.0).
     */
    private function getVerificationProgress(MarketplaceProduct $product)
    {
        if ($product->status === 'active' || $product->status === 'rejected') {
            return 100.0;
        }

        $secondsElapsed = now()->diffInSeconds($product->created_at);
        $totalSeconds = 300; // 5 minutes

        if ($secondsElapsed >= $totalSeconds) {
            return 100.0;
        }

        $pct = ($secondsElapsed / $totalSeconds) * 100;
        return round(min($pct, 99.9), 1);
    }

    /**
     * Upload image(s) via server middleware to ImageKit (API endpoint for mobile app).
     * Mobile app sends image files here, server proxies to ImageKit using private key.
     */
    public function uploadImage(Request $request)
    {
        $folder = $request->input('folder', '/marketplace/products');

        // 1. Handle Base64 string upload if provided
        $base64Input = $request->input('image_base64') ?? ($request->isJson() ? $request->input('image') : null);
        if (!empty($base64Input) && is_string($base64Input) && (strpos($base64Input, 'data:image') === 0 || str_contains($base64Input, ';base64,'))) {
            $savedUrl = $this->saveBase64Image($base64Input);
            if ($savedUrl) {
                return response()->json([
                    'success' => 'Success',
                    'url' => $savedUrl,
                ]);
            }
        }

        // 2. Handle Multipart File upload
        $file = $request->file('image');
        if (!$file || !$file->isValid()) {
            return response()->json([
                'success' => 'Failed',
                'error' => 'No valid image file or data provided.'
            ], 422);
        }

        try {
            $imageUrl = $this->uploadToImageKit($file, $folder);
            return response()->json([
                'success' => 'Success',
                'url' => $imageUrl,
            ]);
        } catch (\Exception $e) {
            Log::warning('ImageKit upload proxy note: ' . $e->getMessage() . '. Saving image locally.');
            try {
                $extension = $file->getClientOriginalExtension() ?: 'jpg';
                $filename = 'product_' . time() . '_' . uniqid() . '.' . $extension;
                $path = public_path('assets/images/marketplace/');
                if (!file_exists($path)) {
                    @mkdir($path, 0777, true);
                }
                $file->move($path, $filename);
                
                $baseUrl = rtrim(config('app.url') ?: 'https://api.fiinway.com', '/');
                if (str_contains($baseUrl, 'localhost') || str_contains($baseUrl, '127.0.0.1')) {
                    $baseUrl = 'https://api.fiinway.com';
                }
                $localUrl = $baseUrl . '/assets/images/marketplace/' . $filename;

                return response()->json([
                    'success' => 'Success',
                    'url' => $localUrl,
                ]);
            } catch (\Exception $ex) {
                return response()->json([
                    'success' => 'Failed',
                    'error' => 'Failed to save image: ' . $ex->getMessage()
                ], 500);
            }
        }
    }

    /**
     * Upload a file to ImageKit using the server's private key (cURL).
     * This is the single source of truth for all ImageKit uploads.
     */
    private function uploadToImageKit($file, $folder = '/marketplace/products')
    {
        $extension = $file->getClientOriginalExtension();
        $filename = 'product_' . time() . '_' . uniqid() . '.' . $extension;

        // Use config() — NOT env() — so this works with Laravel's config cache in production
        $privateKey = config('imagekit.private_key');

        if (empty($privateKey)) {
            throw new \Exception('IMAGEKIT_PRIVATE_KEY is not configured on the server.');
        }

        $url = "https://upload.imagekit.io/api/v1/files/upload";

        $postData = [
            'file' => new \CURLFile($file->getRealPath(), $file->getMimeType(), $filename),
            'fileName' => $filename,
            'folder' => $folder,
            'useUniqueFileName' => 'true'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_USERPWD, $privateKey . ":");
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($curlError) {
            Log::error('ImageKit cURL error: ' . $curlError);
            throw new \Exception('Upload connection failed: ' . $curlError);
        }

        if ($statusCode === 200) {
            $json = json_decode($response, true);
            if (isset($json['url'])) {
                return $json['url'];
            }
            throw new \Exception('ImageKit returned 200 but no URL in response.');
        }

        Log::error("ImageKit upload failed [{$statusCode}]: {$response}");
        throw new \Exception("ImageKit error ({$statusCode})");
    }

    /**
     * Legacy alias — used by the store() method when images are sent as files.
     */
    private function uploadToFirebase($file)
    {
        try {
            return $this->uploadToImageKit($file, '/marketplace/products');
        } catch (\Exception $e) {
            Log::warning('ImageKit upload failed, falling back to local: ' . $e->getMessage());

            // Local public folder fallback
            $extension = $file->getClientOriginalExtension();
            $filename = 'product_' . time() . '_' . uniqid() . '.' . $extension;
            $path = public_path('assets/images/marketplace/');
            if (!file_exists($path)) {
                @mkdir($path, 0777, true);
            }
            $file->move($path, $filename);
            
            $baseUrl = rtrim(config('app.url') ?: 'https://api.fiinway.com', '/');
            if (str_contains($baseUrl, 'localhost') || str_contains($baseUrl, '127.0.0.1')) {
                $baseUrl = 'https://api.fiinway.com';
            }
            return $baseUrl . '/assets/images/marketplace/' . $filename;
        }
    }

    /**
     * Helper to normalize any marketplace image URL to a clean, absolute HTTPS URL.
     */
    private function normalizeMarketplaceImageUrl(string $url): string
    {
        if (empty($url)) {
            return 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=600&q=80';
        }

        $baseAppUrl = rtrim(config('app.url') ?: 'https://api.fiinway.com', '/');
        if (str_contains($baseAppUrl, 'localhost') || str_contains($baseAppUrl, '127.0.0.1')) {
            $baseAppUrl = 'https://api.fiinway.com';
        }

        if (str_starts_with($url, 'http://localhost') || str_starts_with($url, 'https://localhost') || str_starts_with($url, 'http://127.0.0.1') || str_starts_with($url, 'https://127.0.0.1')) {
            $parsed = parse_url($url);
            $path = $parsed['path'] ?? '';
            return $baseAppUrl . $path;
        }

        if (str_starts_with($url, 'assets/') || str_starts_with($url, '/assets/') || str_starts_with($url, 'public/')) {
            $cleanPath = '/' . ltrim(str_replace('public/', '', $url), '/');
            return $baseAppUrl . $cleanPath;
        }

        if (str_starts_with($url, 'http://api.fiinway.com')) {
            return str_replace('http://api.fiinway.com', 'https://api.fiinway.com', $url);
        }

        return $url;
    }

    /**
     * Save a Base64 image to server storage and return absolute HTTPS URL.
     */
    private function saveBase64Image(string $base64String): ?string
    {
        try {
            if (!str_contains($base64String, 'data:image') && !str_contains($base64String, ';base64,')) {
                return null;
            }

            $imageType = 'jpg';
            if (preg_match('/data:image\/(?<type>[a-zA-Z0-9\+\-\.]+);base64,/i', $base64String, $matches)) {
                $rawType = strtolower($matches['type']);
                if ($rawType === 'jpeg' || $rawType === 'jpg') $imageType = 'jpg';
                elseif ($rawType === 'png') $imageType = 'png';
                elseif ($rawType === 'webp') $imageType = 'webp';
                elseif ($rawType === 'gif') $imageType = 'gif';
            }

            $pos = strpos($base64String, ',');
            $rawPayload = ($pos !== false) ? substr($base64String, $pos + 1) : $base64String;
            $cleanData = preg_replace('/\s+/', '', $rawPayload);
            $imageData = base64_decode($cleanData);

            if (empty($imageData)) {
                return null;
            }

            $filename = 'product_' . time() . '_' . uniqid() . '.' . $imageType;
            $path = public_path('assets/images/marketplace/');
            if (!file_exists($path)) {
                @mkdir($path, 0777, true);
            }
            file_put_contents($path . $filename, $imageData);
            @chmod($path . $filename, 0644);

            $baseUrl = rtrim(config('app.url') ?: 'https://api.fiinway.com', '/');
            if (str_contains($baseUrl, 'localhost') || str_contains($baseUrl, '127.0.0.1')) {
                $baseUrl = 'https://api.fiinway.com';
            }
            return $baseUrl . '/assets/images/marketplace/' . $filename;
        } catch (\Exception $e) {
            Log::warning('saveBase64Image error: ' . $e->getMessage());
            return null;
        }
    }
}

