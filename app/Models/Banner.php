<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Banner extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'banners';
    protected $fillable = [
        'title',
        'description',
        'image',
        'status'
    ];
    public $timestamps = true;
    protected $casts = [
        'id' => 'string',
    ];
}
