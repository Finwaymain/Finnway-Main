<?php

namespace App\Models\Food;

use Illuminate\Database\Eloquent\Model;

class FoodMarkupRule extends Model
{
    protected $table = 'food_markup_rules';
    protected $fillable = [
        'scope', 'restaurant_id', 'category_id', 'product_id',
        'rule_type', 'rule_value', 'starts_at', 'ends_at', 'is_active',
    ];
    protected $casts = [
        'rule_value' => 'float',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];
}
