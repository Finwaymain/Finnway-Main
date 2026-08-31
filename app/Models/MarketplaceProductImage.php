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

    /**
     * Accessor to guarantee absolute HTTPS image URLs for all clients.
     */
    public function getImagePathAttribute($value): string
    {
        if (empty($value)) {
            return 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=600&q=80';
        }

        $baseAppUrl = rtrim(config('app.url') ?: 'https://api.fiinway.com', '/');
        if (str_contains($baseAppUrl, 'localhost') || str_contains($baseAppUrl, '127.0.0.1')) {
            $baseAppUrl = 'https://api.fiinway.com';
        }

        if (str_starts_with($value, 'data:image') || str_starts_with($value, 'blob:')) {
            return $value;
        }

        // Fix any marketplace product image filenames
        if (str_contains($value, 'product_')) {
            $parsed = parse_url($value, PHP_URL_PATH);
            $filename = basename($parsed ?: $value);
            if (str_starts_with($filename, 'product_')) {
                return $baseAppUrl . '/api/v1/marketplace/image/' . $filename;
            }
        }

        // Fix relative assets paths
        if (str_starts_with($value, 'assets/') || str_starts_with($value, '/assets/') || str_starts_with($value, 'public/')) {
            $cleanPath = '/' . ltrim(str_replace('public/', '', $value), '/');
            return $baseAppUrl . $cleanPath;
        }

        // Upgrade HTTP to HTTPS for API domain
        if (str_starts_with($value, 'http://api.fiinway.com')) {
            return str_replace('http://api.fiinway.com', 'https://api.fiinway.com', $value);
        }

        return $value;
    }
}
