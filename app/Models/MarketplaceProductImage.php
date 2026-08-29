<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class MarketplaceProductImage
 *
 * @property int $id
 * @property int $product_id
 * @property string $image_path
 * @property bool $is_primary
 * @property MarketplaceProduct $product
 */
class MarketplaceProductImage extends Model
{
    protected $table = 'marketplace_product_images';
    protected $fillable = ['product_id', 'image_path', 'is_primary'];

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(MarketplaceProduct::class, 'product_id');
    }
}
