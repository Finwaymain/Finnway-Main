<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $table = 'service_requests';

    protected $fillable = [
        'user_id',
        'driver_id',
        'service_name',
        'address_type',
        'lat',
        'lng',
        'preferred_date',
        'preferred_time',
        'description',
        'status',
        'media',
        'otp',
        'amount',
        'payment_status'
    ];

    public function user()
    {
        return $this->belongsTo(UserApp::class, 'user_id', 'id');
    }

    public function provider()
    {
        return $this->belongsTo(Driver::class, 'driver_id', 'id');
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id', 'id');
    }
}
