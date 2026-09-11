<?php

namespace App\Models\Food;

use Illuminate\Database\Eloquent\Model;

class FoodCategory extends Model
{
    protected $table = 'food_categories';

    protected $fillable = [
        'restaurant_id', 'name', 'image', 'description', 'sort_order', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function restaurant()
    {
        return $this->belongsTo(FoodRestaurant::class, 'restaurant_id');
    }

    public function products()
    {
        return $this->hasMany(FoodProduct::class, 'category_id');
    }
}
