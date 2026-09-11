<?php

namespace App\Models\Food;

use Illuminate\Database\Eloquent\Model;

class FoodOnboardingPayment extends Model
{
    protected $table = 'food_onboarding_payments';

    protected $fillable = [
        'restaurant_id', 'owner_id', 'amount', 'currency', 'payment_method',
        'gateway', 'gateway_order_id', 'gateway_payment_id', 'status', 'meta',
    ];

    protected $casts = [
        'amount' => 'float',
        'meta' => 'array',
    ];

    public function restaurant()
    {
        return $this->belongsTo(FoodRestaurant::class, 'restaurant_id');
    }
}
