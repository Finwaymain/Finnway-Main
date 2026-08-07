<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverServiceItem extends Model
{
    protected $table = 'driver_service_items';

    protected $fillable = [
        'driver_id',
        'category_id',
        'service_name',
        'price',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];
}
