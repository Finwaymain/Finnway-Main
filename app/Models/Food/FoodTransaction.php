<?php

namespace App\Models\Food;

use Illuminate\Database\Eloquent\Model;

class FoodTransaction extends Model
{
    protected $table = 'food_transactions';

    protected $fillable = [
        'txn_number', 'restaurant_id', 'order_id', 'txn_type', 'amount',
        'payment_method', 'status', 'gateway_ref', 'notes',
    ];

    protected $casts = ['amount' => 'float'];
}
