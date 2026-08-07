<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverServicePricing extends Model
{
    protected $table = 'driver_service_pricing';

    protected $fillable = [
        'driver_id',
        'category_id',
        'visiting_charge',
    ];

    protected $casts = [
        'visiting_charge' => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(DriverServiceItem::class, 'category_id', 'category_id')
            ->whereColumn('driver_service_items.driver_id', 'driver_service_pricing.driver_id');
    }
}
