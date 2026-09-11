<?php

namespace App\Models\Food;

use Illuminate\Database\Eloquent\Model;

class FoodOrder extends Model
{
    protected $table = 'food_orders';
    protected $guarded = [];
    protected $casts = [
        'food_amount' => 'float',
        'markup_amount' => 'float',
        'food_subtotal' => 'float',
        'discount_amount' => 'float',
        'platform_charges' => 'float',
        'other_charges' => 'float',
        'delivery_charge' => 'float',
        'tax_amount' => 'float',
        'customer_payable' => 'float',
        'commission_amount' => 'float',
        'restaurant_net_amount' => 'float',
        'company_due_amount' => 'float',
        'penalty_amount' => 'float',
        'refund_amount' => 'float',
        'distance_km' => 'float',
        'delivery_lat' => 'float',
        'delivery_lng' => 'float',
        'charges_breakdown' => 'array',
        'meta' => 'array',
        'is_test' => 'boolean',
        'accepted_at' => 'datetime',
        'ready_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'delivered_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(FoodOrderItem::class, 'order_id');
    }

    public function restaurant()
    {
        return $this->belongsTo(FoodRestaurant::class, 'restaurant_id');
    }
}
