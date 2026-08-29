<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class MarketplaceOrderItem
 *
 * @property int $id
 * @property int $order_id
 * @property int $product_id
 * @property int $quantity
 * @property float $price
 * @property MarketplaceOrder $order
 * @property MarketplaceProduct|null $product
 */
class MarketplaceOrderItem extends Model
{
    protected $table = 'marketplace_order_items';
    protected $fillable = ['order_id', 'product_id', 'quantity', 'price'];

    public function order(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(MarketplaceOrder::class, 'order_id');
    }

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(MarketplaceProduct::class, 'product_id');
    }
}
