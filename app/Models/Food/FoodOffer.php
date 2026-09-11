<?php

namespace App\Models\Food;

use Illuminate\Database\Eloquent\Model;

class FoodOffer extends Model
{
    protected $table = 'food_offers';
    protected $fillable = [
        'restaurant_id', 'name', 'discount_type', 'discount_value',
        'min_order', 'max_discount', 'starts_at', 'ends_at', 'status',
    ];
    protected $casts = [
        'discount_value' => 'float', 'min_order' => 'float', 'max_discount' => 'float',
        'starts_at' => 'datetime', 'ends_at' => 'datetime',
    ];
}
