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
        'title',
        'description',
        'price',
        'image',
        'items_included',
        'is_compulsory',
        'is_active',
        'checkout_url',
    ];

    protected $casts = [
        'items_included' => 'array',
        'is_compulsory' => 'boolean',
        'is_active' => 'boolean',
        'price' => 'float',
    ];

    public function orders()
    {
        return $this->hasMany(DriverKitOrder::class, 'kit_id');
    }
}
