<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionHistory extends Model
{
    use HasFactory;
    protected $table = 'subscription_history';
    protected $fillable = [

        'id',
        'expiry_date',
        'payment_type',
        'subscription_plan',
        'user_id',
        'subscriptionPlanId',
        
    ];
    protected $casts = [
        'subscription_plan' => 'array',
        'id' => 'string'
    ];
    
}
