<?php

namespace App\Http\Controllers\API\v1\Food;

use App\Http\Controllers\Controller;
use App\Models\Food\FoodCategory;
use App\Models\Food\FoodProduct;
use App\Models\Food\FoodProductAddon;
use App\Models\Food\FoodProductVariant;
use App\Models\Food\FoodRestaurant;
use App\Services\Food\FoodPricingEngine;
use Illuminate\Http\Request;

class RestaurantMenuController extends Controller
{
    protected function restaurant(Request $request): ?FoodRestaurant
    {
        $owner = $request->attributes->get('food_owner');
        return FoodRestaurant::where('owner_id', $owner->id)->orderByDesc('id')->first();
    }

    public function categories(Request $request)
    {
        $restaurant = $this->restaurant($request);
        if (!$restaurant) {
            return response()->json(['success' => false, 'error' => 'Restaurant not found.']);
        }
        $cats = FoodCategory::where('restaurant_id', $restaurant->id)
            ->withCount('products')
            ->orderBy('sort_order')
            ->get();
        return response()->json(['success' => true, 'data' => $cats]);
    }

    public function saveCategory(Request $request, $id = null)
    {
        $restaurant = $this->restaurant($request);
        if (!$restaurant) {
            return response()->json(['success' => false, 'error' => 'Restaurant not found.']);
        }
        $cat = $id
            ? FoodCategory::where('restaurant_id', $restaurant->id)->where('id', $id)->first()
            : new FoodCategory(['restaurant_id' => $restaurant->id]);
        if (!$cat) {
            return response()->json(['success' => false, 'error' => 'Category not found.']);
        }
        $cat->name = $request->get('name', $cat->name);
        $cat->description = $request->get('description', $cat->description);
        $cat->sort_order = (int) $request->get('sort_order', $cat->sort_order ?? 0);
        $cat->is_active = $request->exists('is_active') ? (bool) $request->get('is_active') : ($cat->is_active ?? true);
        if ($request->hasFile('image')) {
            $cat->image = $request->file('image')->store('food/categories', 'public');
        } elseif ($request->filled('image')) {
            $cat->image = $request->get('image');
        }
        $cat->restaurant_id = $restaurant->id;
        $cat->save();
        return response()->json(['success' => true, 'data' => $cat]);
    }

    public function deleteCategory(Request $request, $id)
    {
        $restaurant = $this->restaurant($request);
        $cat = FoodCategory::where('restaurant_id', $restaurant->id)->where('id', $id)->first();
        if (!$cat) {
            return response()->json(['success' => false, 'error' => 'Category not found.']);
        }
        FoodProduct::where('category_id', $cat->id)->update(['category_id' => null]);
        $cat->delete();
        return response()->json(['success' => true, 'message' => 'Category deleted.']);
    }

    public function products(Request $request)
    {
        $restaurant = $this->restaurant($request);
        if (!$restaurant) {
            return response()->json(['success' => false, 'error' => 'Restaurant not found.']);
        }
        $q = FoodProduct::where('restaurant_id', $restaurant->id)
            ->with(['addons', 'variants', 'category']);
        if ($request->filled('category_id')) {
            $q->where('category_id', $request->get('category_id'));
        }
        if ($request->filled('availability')) {
            $q->where('availability', $request->get('availability'));
        }
        $products = $q->orderBy('sort_order')->orderByDesc('id')->get();
        $engine = new FoodPricingEngine();
        $products->transform(function ($p) use ($engine, $restaurant) {
            $price = $engine->customerUnitPrice($p, $restaurant);
            $p->customer_price = $price['customer_price'];
            $p->markup_amount = $price['markup'];
            return $p;
        });
        return response()->json(['success' => true, 'data' => $products]);
    }

    public function saveProduct(Request $request, $id = null)
    {
        $restaurant = $this->restaurant($request);
        if (!$restaurant) {
            return response()->json(['success' => false, 'error' => 'Restaurant not found.']);
        }
        $product = $id
            ? FoodProduct::where('restaurant_id', $restaurant->id)->where('id', $id)->first()
            : new FoodProduct(['restaurant_id' => $restaurant->id]);
        if (!$product) {
            return response()->json(['success' => false, 'error' => 'Product not found.']);
        }

        $product->fill([
            'restaurant_id' => $restaurant->id,
            'category_id' => $request->get('category_id', $product->category_id),
            'name' => $request->get('name', $product->name),
            'description' => $request->get('description', $product->description),
            'food_type' => $request->get('food_type', $product->food_type ?? 'veg'),
            'restaurant_price' => (float) $request->get('restaurant_price', $product->restaurant_price ?? 0),
            'discount_price' => $request->get('discount_price', $product->discount_price),
            'prep_minutes' => $request->get('prep_minutes', $product->prep_minutes),
            'available_qty' => $request->get('available_qty', $product->available_qty),
            'availability' => $request->get('availability', $product->availability ?? 'available'),
            'ingredients' => $request->get('ingredients', $product->ingredients),
            'is_active' => $request->exists('is_active') ? (bool) $request->get('is_active') : ($product->is_active ?? true),
            'sort_order' => (int) $request->get('sort_order', $product->sort_order ?? 0),
        ]);
        if ($request->hasFile('image')) {
            $product->image = $request->file('image')->store('food/products', 'public');
        } elseif ($request->filled('image')) {
            $product->image = $request->get('image');
        }
        $product->save();

        if ($request->has('addons') && is_array($request->get('addons'))) {
            FoodProductAddon::where('product_id', $product->id)->delete();
            foreach ($request->get('addons') as $addon) {
                if (empty($addon['name'])) {
                    continue;
                }
                FoodProductAddon::create([
                    'product_id' => $product->id,
                    'name' => $addon['name'],
                    'price' => (float) ($addon['price'] ?? 0),
                    'is_active' => (bool) ($addon['is_active'] ?? true),
                ]);
            }
        }

        if ($request->has('variants') && is_array($request->get('variants'))) {
            FoodProductVariant::where('product_id', $product->id)->delete();
            foreach ($request->get('variants') as $variant) {
                if (empty($variant['name'])) {
                    continue;
                }
                FoodProductVariant::create([
                    'product_id' => $product->id,
                    'name' => $variant['name'],
                    'price' => (float) ($variant['price'] ?? 0),
                    'is_active' => (bool) ($variant['is_active'] ?? true),
                ]);
            }
        }

        $product->load(['addons', 'variants', 'category']);
        return response()->json(['success' => true, 'data' => $product]);
    }

    public function deleteProduct(Request $request, $id)
    {
        $restaurant = $this->restaurant($request);
        $product = FoodProduct::where('restaurant_id', $restaurant->id)->where('id', $id)->first();
        if (!$product) {
            return response()->json(['success' => false, 'error' => 'Product not found.']);
        }
        FoodProductAddon::where('product_id', $product->id)->delete();
        FoodProductVariant::where('product_id', $product->id)->delete();
        $product->delete();
        return response()->json(['success' => true, 'message' => 'Product deleted.']);
    }

    public function toggleAvailability(Request $request, $id)
    {
        $restaurant = $this->restaurant($request);
        $product = FoodProduct::where('restaurant_id', $restaurant->id)->where('id', $id)->first();
        if (!$product) {
            return response()->json(['success' => false, 'error' => 'Product not found.']);
        }
        $product->availability = $request->get('availability', $product->availability === 'available' ? 'out_of_stock' : 'available');
        $product->save();
        return response()->json(['success' => true, 'data' => $product]);
    }
}
