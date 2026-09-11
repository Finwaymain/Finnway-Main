<?php

namespace App\Models\Food;

use Illuminate\Database\Eloquent\Model;

class FoodDeliveryChargeRule extends Model
{
    protected $table = 'food_delivery_charge_rules';
    protected $fillable = [
        'name', 'base_charge', 'free_radius_km', 'base_radius_km', 'per_km_charge',
        'max_distance_km', 'allow_above_max', 'above_max_per_km', 'distance_slabs', 'is_active',
    ];
    protected $casts = [
        'base_charge' => 'float',
        'free_radius_km' => 'float',
        'base_radius_km' => 'float',
        'per_km_charge' => 'float',
        'max_distance_km' => 'float',
        'above_max_per_km' => 'float',
        'distance_slabs' => 'array',
        'allow_above_max' => 'boolean',
        'is_active' => 'boolean',
    ];
}
