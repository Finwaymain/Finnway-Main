<?php

namespace App\Models\Food;

use Illuminate\Database\Eloquent\Model;

class FoodProductAddon extends Model
{
    protected $table = 'food_product_addons';

    protected $fillable = ['product_id', 'name', 'price', 'is_active'];

    protected $casts = ['price' => 'float', 'is_active' => 'boolean'];
}
