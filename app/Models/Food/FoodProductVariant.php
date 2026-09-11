<?php

namespace App\Models\Food;

use Illuminate\Database\Eloquent\Model;

class FoodProductVariant extends Model
{
    protected $table = 'food_product_variants';

    protected $fillable = ['product_id', 'name', 'price', 'is_active'];

    protected $casts = ['price' => 'float', 'is_active' => 'boolean'];
}
