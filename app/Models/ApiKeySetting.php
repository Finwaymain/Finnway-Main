<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class ApiKeySetting extends Model
{
    use HasFactory;

    protected $table = 'api_key_settings';

    protected $fillable = [
        'group',
        'provider',
        'key_name',
        'key_value',
        'secret_value',
        'is_active',
        'is_sandbox',
        'additional_params',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_sandbox' => 'boolean',
        'additional_params' => 'array',
    ];

    /**
     * Helper to get active API key by provider or key name
     */
    public static function getApiKeyValue(string $provider, string $default = '')
    {
        if (!Schema::hasTable('api_key_settings')) {
            return $default;
        }
        $setting = self::where('provider', $provider)->where('is_active', true)->first();
        return $setting ? $setting->key_value : $default;
    }

    /**
     * Helper to get active Secret key by provider
     */
    public static function getApiSecretValue(string $provider, string $default = '')
    {
        if (!Schema::hasTable('api_key_settings')) {
            return $default;
        }
        $setting = self::where('provider', $provider)->where('is_active', true)->first();
        return $setting ? $setting->secret_value : $default;
    }
}
