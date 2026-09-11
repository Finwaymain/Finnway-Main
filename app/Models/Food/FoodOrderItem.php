<?php

namespace App\Models\Food;

use Illuminate\Database\Eloquent\Model;

class FoodOrderItem extends Model
{
    protected $table = 'food_order_items';
    protected $guarded = [];
    protected $casts = [
        'restaurant_unit_price' => 'float',
        'markup_unit' => 'float',
        'customer_unit_price' => 'float',
        'addons_total' => 'float',
        'line_total' => 'float',
        'addons_json' => 'array',
    ];
}
