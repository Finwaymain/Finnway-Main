<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class MarketplaceOrder
 *
 * @property int $id
 * @property int $user_id
 * @property float $total_amount
 * @property string $delivery_address
 * @property string $phone
 * @property string $status
 * @property int|null $delivery_days
 * @property string|null $status_notes
 * @property \Illuminate\Database\Eloquent\Collection<int, MarketplaceOrderItem> $items
 * @property UserApp $buyer
 */
class MarketplaceOrder extends Model
{
    protected $table = 'marketplace_orders';
    protected $fillable = ['user_id', 'total_amount', 'delivery_address', 'phone', 'status', 'delivery_days', 'status_notes'];

    public function buyer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(UserApp::class, 'user_id');
    }

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MarketplaceOrderItem::class, 'order_id');
    }
}
