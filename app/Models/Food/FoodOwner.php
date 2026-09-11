<?php

namespace App\Models\Food;

use Illuminate\Database\Eloquent\Model;

class FoodOwner extends Model
{
    protected $table = 'food_owners';

    protected $fillable = [
        'name', 'phone', 'email', 'password', 'otp', 'otp_expires_at',
        'access_token', 'fcm_token', 'status',
    ];

    protected $hidden = ['password', 'otp', 'access_token'];

    protected $casts = [
        'otp_expires_at' => 'datetime',
    ];

    public function restaurants()
    {
        return $this->hasMany(FoodRestaurant::class, 'owner_id');
    }
}
