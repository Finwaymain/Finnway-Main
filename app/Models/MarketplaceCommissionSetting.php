<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class MarketplaceCommissionSetting
 *
 * @property int $id
 * @property string $commission_type
 * @property float $commission_value
 * @property bool $is_active
 * @property float $min_order_amount
 * @property string|null $description
 */
class MarketplaceCommissionSetting extends Model
{
    protected $table = 'marketplace_commission_settings';

    protected $fillable = [
        'commission_type',
        'commission_value',
        'is_active',
        'min_order_amount',
        'description',
    ];

    protected $casts = [
        'commission_value' => 'float',
        'is_active' => 'boolean',
        'min_order_amount' => 'float',
    ];

    public static function getActiveSetting()
    {
        return self::where('is_active', true)->first() ?: self::firstOrCreate([], [
            'commission_type' => 'percentage',
            'commission_value' => 5.00,
            'is_active' => true,
            'min_order_amount' => 0.00,
            'description' => 'Default Marketplace Platform Commission'
        ]);
    }
}
