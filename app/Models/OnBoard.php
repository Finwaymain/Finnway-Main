<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class OnBoard extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'on_boardings';
    protected $fillable = [
        'title',
        'type',
        'description', 
        'image',
    ];
    protected $casts = [
        'id' => 'string',
    ];
    public $timestamps = false;

}
