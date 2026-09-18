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
        'cost_price',
        'cashback_amount',
        'stock_quantity',
        'image',
        'images',
        'items_included',
        'products',
        'sizes',
        'colors',
        'is_compulsory',
        'booking_required',
        'reorder_days_limit',
        'return_window_days',
        'display_order',
        'is_active',
        'status',
        'checkout_url',
    ];

    protected $casts = [
        'items_included' => 'array',
        'products' => 'array',
        'sizes' => 'array',
        'colors' => 'array',
        'images' => 'array',
        'is_compulsory' => 'boolean',
        'booking_required' => 'boolean',
        'is_active' => 'boolean',
        'price' => 'float',
        'mrp' => 'float',
        'cost_price' => 'float',
        'cashback_amount' => 'float',
        'stock_quantity' => 'integer',
        'display_order' => 'integer',
    ];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        if (empty($this->image)) {
            return asset('assets/images/placeholder_kit.png');
        }
        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }
        return asset($this->image);
    }

    public function orders()
    {
        return $this->hasMany(DriverKitOrder::class, 'kit_id');
    }
}

