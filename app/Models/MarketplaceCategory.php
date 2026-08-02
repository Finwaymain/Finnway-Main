<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class MarketplaceCategory
 *
 * @property int $id
 * @property string $name
 * @property string|null $icon
 * @property string|null $image
 * @property int|null $parent_id
 * @property MarketplaceCategory|null $parent
 * @property \Illuminate\Database\Eloquent\Collection<int, MarketplaceCategory> $subcategories
 * @property \Illuminate\Database\Eloquent\Collection<int, MarketplaceProduct> $products
 */
class MarketplaceCategory extends Model
{
    protected $table = 'marketplace_categories';
    protected $fillable = ['name', 'icon', 'image', 'parent_id'];

    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(MarketplaceCategory::class, 'parent_id');
    }

    public function subcategories(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MarketplaceCategory::class, 'parent_id');
    }

    public function products(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MarketplaceProduct::class, 'category_id');
    }
}
