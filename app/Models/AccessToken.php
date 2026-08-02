<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class AccessToken extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users_access';
    protected $fillable = [
        'id',
        'user_id',
        'accesstoken',
        'user_type',
    ];
}
