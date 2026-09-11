<?php

namespace App\Models\Food;

use Illuminate\Database\Eloquent\Model;

class FoodRestaurant extends Model
{
    protected $table = 'food_restaurants';

    protected $guarded = [];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'delivery_radius_km' => 'float',
        'min_order_amount' => 'float',
        'max_order_amount' => 'float',
        'onboarding_fee_paid' => 'float',
        'rating_avg' => 'float',
        'delivery_available' => 'boolean',
        'takeaway_available' => 'boolean',
        'dine_in_available' => 'boolean',
        'auto_accept' => 'boolean',
        'is_premium' => 'boolean',
        'pure_veg' => 'boolean',
        'custom_commission_rate' => 'float',
        'doc_status' => 'array',
        'approved_at' => 'datetime',
        'premium_expires_at' => 'datetime',
    ];

    public function owner()
    {
        return $this->belongsTo(FoodOwner::class, 'owner_id');
    }

    public function type()
    {
        return $this->belongsTo(FoodRestaurantType::class, 'type_id');
    }

    public function categories()
    {
        return $this->hasMany(FoodCategory::class, 'restaurant_id');
    }

    public function products()
    {
        return $this->hasMany(FoodProduct::class, 'restaurant_id');
    }

    public function orders()
    {
        return $this->hasMany(FoodOrder::class, 'restaurant_id');
    }

    public function isActivePartner(): bool
    {
        return $this->onboarding_status === 'active';
    }
}
