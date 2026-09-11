<?php

namespace App\Models\Food;

use Illuminate\Database\Eloquent\Model;

class FoodSupportTicket extends Model
{
    protected $table = 'food_support_tickets';
    protected $fillable = [
        'ticket_number', 'restaurant_id', 'owner_id', 'category', 'subject',
        'message', 'priority', 'status', 'attachment', 'admin_reply',
    ];
}
