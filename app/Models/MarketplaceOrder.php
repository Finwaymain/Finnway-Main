<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class MarketplaceOrder
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $seller_id
 * @property float $total_amount
 * @property float|null $subtotal
 * @property float $delivery_charge
 * @property string|null $tax_name
 * @property float $tax_rate
 * @property float $tax_amount
 * @property string $payment_method
 * @property string $payment_status
 * @property string|null $txn_id
 * @property string|null $delivery_address
 * @property string|null $pincode
 * @property string|null $city
 * @property string|null $phone
 * @property string|null $contact_name
 * @property string $status
 * @property string|null $courier_name
 * @property string|null $tracking_id
 * @property int|null $delivery_days
 * @property string|null $status_notes
 * @property string $admin_commission_type
 * @property float $admin_commission_rate
 * @property float $admin_commission_amount
 * @property float $seller_payout_amount
 * @property string $payout_status
 * @property string|null $payout_released_at
 * @property int|null $payout_released_by
 * @property \Illuminate\Database\Eloquent\Collection<int, MarketplaceOrderItem> $items
 * @property UserApp $buyer
 */
class MarketplaceOrder extends Model
{
    protected $table = 'marketplace_orders';

    protected $fillable = [
        'user_id',
        'seller_id',
        'total_amount',
        'subtotal',
        'delivery_charge',
        'tax_name',
        'tax_rate',
        'tax_amount',
        'payment_method',
        'payment_status',
        'txn_id',
        'delivery_address',
        'pincode',
        'city',
        'phone',
        'contact_name',
        'status',
        'courier_name',
        'tracking_id',
        'delivery_days',
        'status_notes',
        'admin_commission_type',
        'admin_commission_rate',
        'admin_commission_amount',
        'seller_payout_amount',
        'payout_status',
        'payout_released_at',
        'payout_released_by',
    ];

    protected $casts = [
        'total_amount'            => 'float',
        'subtotal'                => 'float',
        'delivery_charge'         => 'float',
        'tax_rate'                => 'float',
        'tax_amount'              => 'float',
        'admin_commission_rate'   => 'float',
        'admin_commission_amount' => 'float',
        'seller_payout_amount'    => 'float',
    ];

    public function buyer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(UserApp::class, 'user_id');
    }

    public function seller(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(UserApp::class, 'seller_id');
    }

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MarketplaceOrderItem::class, 'order_id');
    }
}
