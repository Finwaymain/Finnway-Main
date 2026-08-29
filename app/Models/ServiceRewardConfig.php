<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRewardConfig extends Model
{
    use HasFactory;

    protected $table = 'service_reward_configs';

    protected $fillable = [
        'category_id',
        'service_name',
        'service_slug',
        'reward_mode',
        'business_value',
        'customer_value',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(UserCategory::class, 'category_id', 'id');
    }
}
