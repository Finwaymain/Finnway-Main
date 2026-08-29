<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverKitOrder extends Model
{
    use HasFactory;

    protected $table = 'driver_kit_orders';

    protected $fillable = [
        'driver_id',
        'kit_id',
        'order_number',
        'category_code',
        'kit_title',
        'amount',
        'tshirt_size',
        'receiver_name',
        'receiver_phone',
        'shipping_address',
        'payment_method',
        'payment_status',
        'delivery_status',
        'tracking_number',
        'courier_partner',
        'transaction_id',
        'purchased_at',
    ];

    protected $casts = [
        'driver_id' => 'integer',
        'kit_id' => 'integer',
        'amount' => 'float',
        'purchased_at' => 'datetime',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id', 'id');
    }

    public function kit()
    {
        return $this->belongsTo(DriverKit::class, 'kit_id', 'id');
    }
}
