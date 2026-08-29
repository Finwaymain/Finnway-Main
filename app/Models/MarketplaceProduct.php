<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class MarketplaceProduct
 *
 * @property int $id
 * @property string $title
 * @property string $description
 * @property float|string $price
 * @property int $stock_quantity
 * @property int $user_id
 * @property int $category_id
 * @property int|null $subcategory_id
 * @property string $status
 * @property string $condition
 * @property string $delivery_type
 * @property UserApp $seller
 * @property MarketplaceCategory $category
 * @property MarketplaceCategory|null $subcategory
 * @property \Illuminate\Database\Eloquent\Collection<int, MarketplaceProductImage> $images
 * @property MarketplaceProductImage|null $primaryImage
 */
class MarketplaceProduct extends Model
{
    protected $table = 'marketplace_products';
    protected $fillable = [
        'title',
        'brand_name',
        'description',
        'price',
        'original_price',
        'discount_percentage',
        'stock_quantity',
        'user_id',
        'category_id',
        'subcategory_id',
        'status',
        'condition',
        'condition_detail',
        'delivery_type',
        'seller_city',
        'specifications',
    ];

    public function seller(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(UserApp::class, 'user_id');
    }

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(MarketplaceCategory::class, 'category_id');
    }

    public function subcategory(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(MarketplaceCategory::class, 'subcategory_id');
    }

    public function images(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MarketplaceProductImage::class, 'product_id');
    }

    public function primaryImage(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(MarketplaceProductImage::class, 'product_id')->where('is_primary', true);
    }
}
