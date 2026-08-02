<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeService extends Model
{
    use HasFactory;
    
    protected $table = 'home_services';

    protected $fillable = [
        'category_name',
        'icon',
        'service_name',
        'status',
    ];
}
