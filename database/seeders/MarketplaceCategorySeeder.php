<?php

namespace Database\Seeders;

use App\Models\MarketplaceCategory;
use Illuminate\Database\Seeder;

class MarketplaceCategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            [
                'name' => 'Electronics',
                'icon' => 'devices',
                'image' => 'https://images.unsplash.com/photo-1498049794561-7780e7231661?auto=format&fit=crop&q=80&w=2070',
                'subcategories' => ['Mobiles', 'Laptops', 'Audio']
            ],
            [
                'name' => 'Clothing',
                'icon' => 'checkroom',
                'image' => 'https://images.unsplash.com/photo-1445205170230-053b83016050?auto=format&fit=crop&q=80&w=2071',
                'subcategories' => ['Men', 'Women', 'Kids']
            ],
            [
                'name' => 'Beauty',
                'icon' => 'face',
                'image' => 'https://images.unsplash.com/photo-1596462502278-27bfdc4033c8?auto=format&fit=crop&q=80&w=2070',
                'subcategories' => ['Skincare', 'Makeup']
            ],
            [
                'name' => 'Furniture',
                'icon' => 'chair',
                'image' => 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&q=80&w=2070',
                'subcategories' => ['Living Room', 'Bedroom']
            ],
            [
                'name' => 'Books',
                'icon' => 'menu_book',
                'image' => 'https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&fit=crop&q=80&w=2070',
                'subcategories' => ['Fiction', 'Academic']
            ]
        ];

        foreach ($categories as $catData) {
            $parent = MarketplaceCategory::create([
                'name' => $catData['name'],
                'icon' => $catData['icon'],
                'image' => $catData['image']
            ]);

            foreach ($catData['subcategories'] as $subName) {
                MarketplaceCategory::create([
                    'name' => $subName,
                    'parent_id' => $parent->id
                ]);
            }
        }
    }
}
