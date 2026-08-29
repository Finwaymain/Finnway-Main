<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppVersionControl extends Model
{
    use HasFactory;

    protected $table = 'app_version_controls';

    protected $fillable = [
        'app_type',
        'app_name',
        'latest_version',
        'minimum_version',
        'force_update',
        'playstore_url',
        'appstore_url',
        'title',
        'message',
        'is_maintenance',
        'maintenance_message',
    ];

    protected $casts = [
        'force_update' => 'boolean',
        'is_maintenance' => 'boolean',
    ];
}
