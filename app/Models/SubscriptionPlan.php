<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    use HasFactory;
    protected $table = 'subscription_plans';
    protected $fillable = [
        'id',
        'bookingLimit',
        'description',
        'expiryDay',
        'image',
        'isEnable',
        'name',
        'place',
        'plan_points',
        'price',
        'type',
        // Benefit config
        'plan_tier',
        'sender_cashback_type', 'sender_cashback_value',
        'receiver_cashback_type', 'receiver_cashback_value',
        'discount_home_service', 'discount_travel', 'discount_hotel',
        'discount_food', 'discount_medical', 'discount_marketplace',
        'discount_transaction',
        'shopping_discount',
        'free_ride_limit', 'free_ride_reset',
        'wallet_increment_value', 'wallet_increment_period',
        'wallet_decrement_value', 'wallet_decrement_period',
        'referral_bonus_type', 'referral_bonus_value',
        'loan_enabled', 'loan_max_amount',
        'interest_free_loan_enabled', 'interest_free_loan_limit',
        'service_permissions',
    ];
    protected $casts = [
        'plan_points'        => 'array',
        'service_permissions'=> 'array',
        'loan_enabled'       => 'boolean',
        'interest_free_loan_enabled' => 'boolean',
        'id'                 => 'string',
    ];
    public function subscribers(): HasMany
    {
        return $this->hasMany(Driver::class, 'subscriptionPlanId', 'id');
    }
}
