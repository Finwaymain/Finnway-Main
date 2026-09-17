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
        'selected_size',
        'selected_color',
        'receiver_name',
        'receiver_phone',
        'shipping_address',
        'pincode',
        'payment_method',
        'payment_status',
        'delivery_status',
        'tracking_number',
        'tracking_code',
        'tracking_url',
        'courier_partner',
        'courier_partner_logo',
        'expected_delivery_date',
        'delivery_partner_name',
        'delivery_partner_phone',
        'delivery_partner_vehicle',
        'delivery_partner_id',
        'status_timeline',
        'transaction_id',
        'purchased_at',
    ];

    protected $casts = [
        'driver_id' => 'integer',
        'kit_id' => 'integer',
        'amount' => 'float',
        'status_timeline' => 'array',
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
