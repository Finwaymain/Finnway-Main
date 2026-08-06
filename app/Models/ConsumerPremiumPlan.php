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
        'free_shipping', 'shipping_min_order', 'free_shipping_count', 'free_ride_limit',
        'quota_hotel_booking', 'quota_home_service', 'quota_shopping', 'quota_food', 'quota_medical',
        'min_order_amount_benefit', 'wallet_monthly_bonus', 'annual_voucher_value',
        'min_amount_hotel', 'min_amount_home_service', 'min_amount_shopping',
        'min_amount_food', 'min_amount_travel', 'min_amount_medical', 'min_amount_cab',
        'discount_delivery', 'discount_delivery_food', 'discount_delivery_shopping',
        'discount_delivery_home_service', 'discount_delivery_medical', 'discount_delivery_parcel',
        'discount_transaction', 'loan_enabled', 'loan_max_amount',
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
