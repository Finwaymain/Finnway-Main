<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsumerPremiumPlan extends Model
{
    use HasFactory;

    protected $table = 'consumer_premium_plans';

    protected $fillable = [
        'name', 'price', 'validity_days', 'description', 'status',
        'sender_cashback_type', 'sender_cashback_value',
        'receiver_cashback_type', 'receiver_cashback_value',
        'discount_cab', 'discount_bike', 'discount_home_service',
        'discount_food', 'discount_travel', 'discount_hotel',
        'discount_healthcare', 'discount_marketplace',
        'free_shipping', 'shipping_min_order',
        'loan_personal', 'loan_business', 'loan_credit_card',
        'loan_interest_free', 'loan_virtual', 'virtual_credit_limit',
        'display_order',
    ];

    protected $casts = [
        'free_shipping'      => 'boolean',
        'loan_personal'      => 'boolean',
        'loan_business'      => 'boolean',
        'loan_credit_card'   => 'boolean',
        'loan_interest_free' => 'boolean',
        'loan_virtual'       => 'boolean',
        'price'              => 'decimal:2',
        'virtual_credit_limit' => 'decimal:2',
    ];
}
