<?php

namespace App\Models\Food;

use Illuminate\Database\Eloquent\Model;

class FoodSettlement extends Model
{
    protected $table = 'food_settlements';
    protected $guarded = [];
    protected $casts = [
        'gross_sales' => 'float',
        'commission' => 'float',
        'other_charges' => 'float',
        'refunds' => 'float',
        'penalties' => 'float',
        'adjustments' => 'float',
        'net_amount' => 'float',
        'period_from' => 'date',
        'period_to' => 'date',
        'paid_at' => 'datetime',
    ];
}
