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

        $query = MarketplaceProduct::with(['images', 'category', 'subcategory'])
            ->where('status', 'active');

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('condition')) {
            $query->where('condition', $request->condition);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('created_at', 'desc')->get();

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
        $categories = MarketplaceCategory::with('subcategories')->whereNull('parent_id')->get();
        return response()->json([
            'success' => 'Success',
            'data' => $categories
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
            ->where('user_id', $userId)
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
        $userId = $this->getAuthenticatedUserId($request);
        if (!$userId) {
            return response()->json(['success' => 'Failed', 'error' => 'Unauthorized'], 401);
        }

        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:1',
            'category_id' => 'required|integer',
            'subcategory_id' => 'nullable|integer',
            'condition' => 'required|string|in:New,Used',
            'delivery_type' => 'required|string|in:Local,Pan India,Both',
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
                // 1. Create Product
                $product = MarketplaceProduct::create([
                    'title' => $request->title,
                    'description' => $request->description,
                    'price' => $request->price,
                    'stock_quantity' => $request->stock_quantity,
                    'user_id' => $userId,
                    'category_id' => $request->category_id,
                    'subcategory_id' => $request->subcategory_id,
                    'condition' => $request->condition,
                    'delivery_type' => $request->delivery_type,
                    'status' => 'pending_verification',
                ]);

                // 2. Upload/Save Images
                if ($request->has('image_urls') && !empty($request->image_urls)) {
                    $imageUrls = $request->image_urls;
                    foreach ($imageUrls as $index => $imageUrl) {
                        MarketplaceProductImage::create([
                            'product_id' => $product->id,
                            'image_path' => $imageUrl,
                            'is_primary' => ($index === 0)
                        ]);
                    }
                } else {
                    $images = $request->file('images');
                    foreach ($images as $index => $imageFile) {
                        $imagePath = $this->uploadToFirebase($imageFile);
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
                'error' => 'Failed to create product listing. Please try again.'
            ], 500);
        }
    }

    /**
     * Update product details (Protected).
     */
    public function update(Request $request, $id)
    {
        $userId = $this->getAuthenticatedUserId($request);
        if (!$userId) {
            return response()->json(['success' => 'Failed', 'error' => 'Unauthorized'], 401);
        }

        $product = MarketplaceProduct::where('id', $id)->where('user_id', $userId)->first();
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
            'condition' => 'required|string|in:New,Used',
            'delivery_type' => 'required|string|in:Local,Pan India,Both',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => 'Failed',
                'error' => $validator->errors()->first()
            ], 420);
        }

        // Update details and put back to verification stage if title/desc changed
        $needsReverification = ($product->title !== $request->title || $product->description !== $request->description);

        $product->update([
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'stock_quantity' => $request->stock_quantity,
            'category_id' => $request->category_id,
            'subcategory_id' => $request->subcategory_id,
            'condition' => $request->condition,
            'delivery_type' => $request->delivery_type,
            'status' => $needsReverification ? 'pending_verification' : $product->status,
        ]);

        if ($needsReverification) {
            // reset created_at to restart 5-min timer
            $product->created_at = now();
            $product->save();
        }

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
     * Helper to retrieve authenticated user ID.
     */
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
        $userId = $this->getAuthenticatedUserId($request);
        if (!$userId) {
            return response()->json(['success' => 'Failed', 'error' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'folder' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => 'Failed',
                'error' => $validator->errors()->first()
            ], 422);
        }

        $file = $request->file('image');
        $folder = $request->input('folder', '/marketplace/products');

        try {
            $imageUrl = $this->uploadToImageKit($file, $folder);
            return response()->json([
                'success' => 'Success',
                'url' => $imageUrl,
            ]);
        } catch (\Exception $e) {
            Log::error('Image upload proxy failed: ' . $e->getMessage());
            return response()->json([
                'success' => 'Failed',
                'error' => 'Failed to upload image: ' . $e->getMessage()
            ], 500);
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
            return asset('assets/images/marketplace/' . $filename);
        }
    }
}
