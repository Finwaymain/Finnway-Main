<?php

namespace App\Models\Food;

use Illuminate\Database\Eloquent\Model;

class FoodDispute extends Model
{
    protected $table = 'food_disputes';
    protected $fillable = [
        'ticket_number', 'order_id', 'restaurant_id', 'customer_id', 'rider_id',
        'raised_by', 'issue_type', 'description', 'priority', 'status',
        'proof_image', 'resolution', 'refund_amount', 'penalty_amount',
    ];
    protected $casts = [
        'refund_amount' => 'float', 'penalty_amount' => 'float',
    ];
}
