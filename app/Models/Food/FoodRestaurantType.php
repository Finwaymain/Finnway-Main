<?php

namespace App\Models\Food;

use Illuminate\Database\Eloquent\Model;

class FoodRestaurantType extends Model
{
    protected $table = 'food_restaurant_types';

    protected $fillable = [
        'code', 'name', 'description', 'is_active', 'onboarding_fee',
        'approval_mode', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'onboarding_fee' => 'float',
    ];
}
