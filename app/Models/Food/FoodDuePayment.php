<?php

namespace App\Models\Food;

use Illuminate\Database\Eloquent\Model;

class FoodDuePayment extends Model
{
    protected $table = 'food_due_payments';
    protected $guarded = [];
    protected $casts = [
        'amount' => 'float',
        'paid_amount' => 'float',
        'paid_at' => 'datetime',
    ];
}
