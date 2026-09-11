<?php

namespace App\Models\Food;

use Illuminate\Database\Eloquent\Model;

class FoodPremiumDeal extends Model
{
    protected $table = 'food_premium_deals';
    protected $fillable = [
        'restaurant_id', 'deal_type', 'deal_value', 'starts_at', 'ends_at', 'is_active', 'notes',
    ];
    protected $casts = [
        'deal_value' => 'float', 'is_active' => 'boolean',
        'starts_at' => 'datetime', 'ends_at' => 'datetime',
    ];
}
