<?php

namespace App\Models\Food;

use Illuminate\Database\Eloquent\Model;

class FoodNotification extends Model
{
    protected $table = 'food_notifications';
    protected $fillable = [
        'restaurant_id', 'owner_id', 'title', 'message', 'type', 'ref_id', 'is_read',
    ];
    protected $casts = ['is_read' => 'boolean'];
}
