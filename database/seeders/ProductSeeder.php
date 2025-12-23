<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\User;
use App\Models\Category;
use App\Models\StoreSettings;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $seller = User::whereHas('roles', function($q) {
            $q->where('name', 'seller');
        })->first();

        if (!$seller) {
            $seller = User::factory()->create();
            $seller->assignRole('seller');
            StoreSettings::create([
                'seller_id' => $seller->id,
                'store_name' => 'Demo Store',
                'brand_story' => 'We sell the best demo products.',
                'shipping_type' => 'self',
                'flat_shipping_rate' => 50.00,
            ]);
        }

        $categories = Category::all();
        
        if ($categories->isEmpty()) {
            $this->call(CategorySeeder::class);
            $categories = Category::all();
        }

        $products = [
            [
                'name' => 'Premium Wireless Headphones',
                'description' => 'Experience high-fidelity sound with our premium wireless headphones. Noise-cancelling technology and 30-hour battery life.',
                'price' => 2499.00,
                'stock' => 50,
                'category_id' => $categories->first()->id,
                'image' => '/images/products/electronics.png'
            ],
            [
                'name' => 'Classic Denim Jacket',
                'description' => 'A timeless classic. This denim jacket features a comfortable fit and durable material, perfect for any season.',
                'price' => 1299.00,
                'stock' => 100,
                'category_id' => $categories->skip(1)->first()->id ?? $categories->first()->id,
                'image' => '/images/products/fashion.png'
            ],
            [
                'name' => 'Modern Ceramic Vase',
                'description' => 'Add a touch of elegance to your home with this modern ceramic vase. Perfect for fresh or dried flowers.',
                'price' => 1250.00,
                'stock' => 25,
                'category_id' => $categories->skip(2)->first()->id ?? $categories->first()->id,
                'image' => '/images/products/home.png'
            ]
        ];

        foreach ($products as $data) {
            $image = $data['image'];
            unset($data['image']);
            
            $product = Product::create(array_merge($data, [
                'seller_id' => $seller->id,
                'status' => 'approved',
                'commission_level' => '6-4-2',
                'images' => [$image]
            ]));
            
            // Note: We're using the JSON images column in products table
            // ProductImage table is optional and can cause type issues with PostgreSQL
            // Uncomment below if you need to use the product_images relationship table
            /*
            try {
                \App\Models\ProductImage::create([
                    'product_id' => $product->id,
                    'url' => $image,
                    'is_primary' => true
                ]);
            } catch (\Exception $e) {
                // Ignore if table or model doesn't exist/match
            }
            */
        }
    }
}
