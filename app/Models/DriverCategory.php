<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverCategory extends Model
{
    use HasFactory;

    protected $table = 'tj_conducteur_categories';

    protected $fillable = [
        'driver_id',
        'category_id',
        'subcategory_id',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }

    public function category()
    {
        return $this->belongsTo(UserCategory::class, 'category_id');
    }

    public function subcategory()
    {
        return $this->belongsTo(UserCategory::class, 'subcategory_id');
    }
}
