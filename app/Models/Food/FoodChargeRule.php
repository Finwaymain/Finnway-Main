<?php

namespace App\Models\Food;

use Illuminate\Database\Eloquent\Model;

class FoodChargeRule extends Model
{
    protected $table = 'food_charge_rules';
    protected $fillable = [
        'name', 'code', 'charge_type', 'charge_value', 'min_amount', 'max_amount',
        'order_min', 'order_max', 'slab_json', 'time_from', 'time_to',
        'starts_at', 'ends_at', 'is_active',
    ];
    protected $casts = [
        'charge_value' => 'float',
        'min_amount' => 'float',
        'max_amount' => 'float',
        'order_min' => 'float',
        'order_max' => 'float',
        'slab_json' => 'array',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];
}
