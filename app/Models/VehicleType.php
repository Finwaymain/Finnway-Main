<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class VehicleType extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    public $timestamps = false;
    protected $table = 'tj_type_vehicule';
    protected $fillable = [
        'libelle',
        'prix',
        'base_price',
        'per_km_price',
        'image',
        'selected_image',
        'creer',
        'modifier'
    ];
    protected $casts = [
        'id' => 'string',
    ];

    public function userCategories()
    {
        return $this->belongsToMany(UserCategory::class, 'tj_category_user_vehicle_type', 'vehicle_type_id', 'category_user_id');
    }
}
