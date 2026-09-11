<?php

namespace App\Models\Food;

use Illuminate\Database\Eloquent\Model;

class FoodProduct extends Model
{
    protected $table = 'food_products';

    protected $fillable = [
        'restaurant_id', 'category_id', 'name', 'image', 'description', 'food_type',
        'restaurant_price', 'discount_price', 'prep_minutes', 'available_qty',
        'availability', 'ingredients', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'restaurant_price' => 'float',
        'discount_price' => 'float',
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(FoodCategory::class, 'category_id');
    }

    public function addons()
    {
        return $this->hasMany(FoodProductAddon::class, 'product_id');
    }

    public function variants()
    {
        return $this->hasMany(FoodProductVariant::class, 'product_id');
    }
}
