<?php

namespace App\Models\Food;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FoodCuisine extends Model
{
    protected $table = 'food_cuisines';

    protected $fillable = ['name', 'slug', 'icon', 'is_active', 'sort_order'];

    protected $casts = ['is_active' => 'boolean'];

    protected static function booted(): void
    {
        static::saving(function (FoodCuisine $cuisine) {
            if (empty($cuisine->slug)) {
                $cuisine->slug = Str::slug($cuisine->name);
            }
        });
    }
}
