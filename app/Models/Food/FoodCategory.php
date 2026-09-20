<?php

namespace App\Models\Food;

use Illuminate\Database\Eloquent\Model;

class FoodCategory extends Model
{
    protected $table = 'food_categories';

    protected $fillable = [
        'restaurant_id', 'name', 'image', 'description', 'sort_order', 'is_active',
    ];

    protected $appends = ['image_url'];

    protected $casts = ['is_active' => 'boolean'];

    public function getImageUrlAttribute(): ?string
    {
        if (empty($this->image)) {
            return null;
        }
        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }
        return asset('storage/' . ltrim($this->image, '/'));
    }

    public function restaurant()
    {
        return $this->belongsTo(FoodRestaurant::class, 'restaurant_id');
    }

    public function products()
    {
        return $this->hasMany(FoodProduct::class, 'category_id');
    }
}
