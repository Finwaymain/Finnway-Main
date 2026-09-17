<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverKit extends Model
{
    use HasFactory;

    protected $table = 'driver_kits';

    protected $fillable = [
        'category_code',
        'category_id',
        'sku',
        'title',
        'description',
        'price',
        'mrp',
        'cashback_amount',
        'stock_quantity',
        'image',
        'images',
        'items_included',
        'sizes',
        'colors',
        'is_compulsory',
        'booking_required',
        'reorder_days_limit',
        'return_window_days',
        'display_order',
        'is_active',
        'checkout_url',
    ];

    protected $casts = [
        'items_included' => 'array',
        'sizes' => 'array',
        'colors' => 'array',
        'images' => 'array',
        'is_compulsory' => 'boolean',
        'booking_required' => 'boolean',
        'is_active' => 'boolean',
        'price' => 'float',
        'mrp' => 'float',
        'cashback_amount' => 'float',
        'stock_quantity' => 'integer',
        'display_order' => 'integer',
    ];

    public function orders()
    {
        return $this->hasMany(DriverKitOrder::class, 'kit_id');
    }
}
