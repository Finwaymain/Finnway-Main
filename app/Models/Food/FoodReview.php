<?php

namespace App\Models\Food;

use Illuminate\Database\Eloquent\Model;

class FoodReview extends Model
{
    protected $table = 'food_reviews';
    protected $fillable = [
        'order_id', 'restaurant_id', 'customer_id', 'rider_id',
        'restaurant_rating', 'rider_rating', 'restaurant_review', 'rider_review',
        'restaurant_tags', 'rider_tags', 'restaurant_reply', 'replied_at',
    ];
    protected $casts = [
        'restaurant_tags' => 'array', 'rider_tags' => 'array', 'replied_at' => 'datetime',
    ];
}
