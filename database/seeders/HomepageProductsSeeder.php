<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class HomepageProductsSeeder extends Seeder
{
    public function run()
    {
        // 1. Get existing Admin User
        $seller = User::where('email', 'admin@marketplace.com')->first();
        if (!$seller) {
            // Fallback if AdminSeeder hasn't run or email differs
            $seller = User::firstOrCreate(
                ['email' => 'admin@gadgettrust.com'],
                [
                    'name' => 'Gadget Trust Official',
                    'password' => Hash::make('password'),
                ]
            );
        }

        // 2. Truncate Products and Product Images to prevent duplicates
        // Use Laravel's Schema builder for DB-agnostic FK handling
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        \App\Models\ProductImage::truncate();
        \App\Models\Product::truncate();
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        // 3. Create Categories
        $categories = [
            'Smart Tech' => 'smart-tech',
            'Gaming' => 'gaming',
            'Travel' => 'travel',
            'Top Gear' => 'gear',
            'Electronics' => 'electronics',
            'Mobiles' => 'mobiles',
            'Fashion' => 'fashion',
            'Home' => 'home',
            'Appliances' => 'appliances',
            'Grocery' => 'grocery',
            'Beauty' => 'beauty',
            'Toys' => 'toys',
        ];

        $catIds = [];
        foreach ($categories as $name => $slug) {
            $cat = Category::firstOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'description' => "$name Category"]
            );
            $catIds[$slug] = $cat->id;
        }

        // 3. Create Products (Matching Homepage)
        $products = [
            // Smart Tech
            [
                'name' => 'Smart Watches',
                'description' => 'Premium smart watch with health tracking.',
                'price' => 2999.00,
                'category_id' => $catIds['smart-tech'],
                'images' => ['http://localhost:3000/images/prod_ext_watch_real.webp'],
                'brand' => 'Apple',
            ],
            [
                'name' => 'Wireless Earbuds',
                'description' => 'Noise cancelling wireless earbuds.',
                'price' => 1499.00,
                'category_id' => $catIds['smart-tech'],
                'images' => ['http://localhost:3000/images/prod_ext_earbuds.webp'],
                'brand' => 'Sony',
            ],
            [
                'name' => 'Smart Bands',
                'description' => 'Fitness tracker with heart rate monitor.',
                'price' => 999.00,
                'category_id' => $catIds['smart-tech'],
                'images' => ['http://localhost:3000/images/prod_ext_band_final.png'],
                'brand' => 'Fitbit',
            ],
            [
                'name' => 'Smart Speakers',
                'description' => 'Voice controlled smart assistant.',
                'price' => 3499.00,
                'category_id' => $catIds['smart-tech'],
                'images' => ['http://localhost:3000/images/prod_ext_speaker.png'],
                'brand' => 'Google',
            ],

            // Gaming
            [
                'name' => 'Gaming Keyboards',
                'description' => 'RGB Mechanical Gaming Keyboard.',
                'price' => 4500.00,
                'category_id' => $catIds['gaming'],
                'images' => ['http://localhost:3000/images/prod_ext_keyboard_final.png'],
                'brand' => 'Razer',
            ],
            [
                'name' => 'Gaming Mouse',
                'description' => 'High precision gaming mouse with RGB.',
                'price' => 2499.00,
                'category_id' => $catIds['gaming'],
                'images' => ['http://localhost:3000/images/prod_ext_mouse_final.png'],
                'brand' => 'Logitech',
            ],
            [
                'name' => 'Gaming Laptops',
                'description' => 'High performance gaming laptop.',
                'price' => 85000.00,
                'category_id' => $catIds['gaming'],
                'images' => ['http://localhost:3000/images/prod_ext_gaming_laptop.webp'],
                'brand' => 'ASUS',
            ],
            [
                'name' => 'Gaming Headsets',
                'description' => 'Surround sound gaming headset.',
                'price' => 3999.00,
                'category_id' => $catIds['gaming'],
                'images' => ['http://localhost:3000/images/prod_ext_headphones.png'],
                'brand' => 'HyperX',
            ],

            // Top Gear
            [
                'name' => 'Premium Trimmer',
                'description' => 'Cordless beard trimmer.',
                'price' => 1299.00,
                'category_id' => $catIds['gear'],
                'images' => ['http://localhost:3000/images/prod_ext_trimmer_final.png'],
                'brand' => 'Philips',
            ],
            [
                'name' => 'Power Bank 20000mAh',
                'description' => 'Fast charging power bank.',
                'price' => 1599.00,
                'category_id' => $catIds['gear'],
                'images' => ['http://localhost:3000/images/prod_ext_powerbank.png'],
                'brand' => 'Anker',
            ],
            [
                'name' => 'USB-C Cable',
                'description' => 'Braided durable charging cable.',
                'price' => 499.00,
                'category_id' => $catIds['gear'],
                'images' => ['http://localhost:3000/images/prod_ext_charger.png'],
                'brand' => 'Boat',
            ],
            [
                'name' => 'Selfie Stick',
                'description' => 'Bluetooth selfie stick with tripod.',
                'price' => 799.00,
                'category_id' => $catIds['gear'],
                'images' => ['http://localhost:3000/images/prod_ext_stick.png'],
                'brand' => 'Realme',
            ],

            // Electronics (General) - keeping these for the generic category
            [
                 'name' => 'LED TV',
                 'description' => '4K Ultra HD Smart TV.',
                 'price' => 34999.00,
                 'category_id' => $catIds['electronics'],
                 'images' => ['http://localhost:3000/images/prod_appliance_tv.webp'], // Assuming exists or fallback
                 'brand' => 'Sony',
            ],

            // Mobiles
            [
                'name' => 'iPhone 15 Pro',
                'description' => 'Titanium design, A17 Pro chip.',
                'price' => 134900.00,
                'category_id' => $catIds['mobiles'],
                'images' => ['http://localhost:3000/images/prod_mobile_iphone.png'],
                'brand' => 'Apple',
            ],
            [
                'name' => 'Samsung Galaxy S24',
                'description' => 'Galaxy AI is here.',
                'price' => 79999.00,
                'category_id' => $catIds['mobiles'],
                'images' => ['http://localhost:3000/images/prod_mobile_samsung.png'],
                'brand' => 'Samsung',
            ],

            // Fashion
            [
                'name' => 'Men Slim Fit Shirt',
                'description' => '100% Cotton casual shirt.',
                'price' => 899.00,
                'category_id' => $catIds['fashion'],
                'images' => ['http://localhost:3000/images/prod_fashion_shirt.png'],
                'brand' => 'Roadster',
            ],
            [
                'name' => 'Women Floral Dress',
                'description' => 'Summer collection 2024.',
                'price' => 1299.00,
                'category_id' => $catIds['fashion'],
                'images' => ['http://localhost:3000/images/prod_fashion_dress.png'],
                'brand' => 'Zara',
            ],

            // Appliances
            [
                'name' => 'Double Door Fridge',
                'description' => '253L 3 Star Refrigerator.',
                'price' => 24990.00,
                'category_id' => $catIds['appliances'],
                'images' => ['http://localhost:3000/images/prod_appliance_fridge.png'],
                'brand' => 'Samsung',
            ],
             [
                'name' => 'Washing Machine',
                'description' => '7kg Fully Automatic.',
                'price' => 16990.00,
                'category_id' => $catIds['appliances'],
                'images' => ['http://localhost:3000/images/prod_appliance_washing_machine.png'],
                'brand' => 'Whirlpool',
            ],

            // Grocery
            [
                'name' => 'Almonds 1kg',
                'description' => 'Premium California Almonds.',
                'price' => 850.00,
                'category_id' => $catIds['grocery'],
                'images' => ['http://localhost:3000/images/prod_grocery_almonds.png'],
                'brand' => 'Happilo',
            ],
            [
                'name' => 'Basmati Rice',
                'description' => 'Extra Long Grain Rice 5kg.',
                'price' => 650.00,
                'category_id' => $catIds['grocery'],
                'images' => ['http://localhost:3000/images/prod_grocery_rice.png'],
                'brand' => 'India Gate',
            ],

            // Beauty
            [
                'name' => 'Mascara',
                'description' => 'Volumizing Mascara.',
                'price' => 399.00,
                'category_id' => $catIds['beauty'],
                'images' => ['http://localhost:3000/images/prod_beauty_mascara.webp'],
                'brand' => 'Maybelline',
            ],
            [
                'name' => 'Eyeshadow Palette',
                'description' => 'Colorful eyeshadow palette.',
                'price' => 899.00,
                'category_id' => $catIds['beauty'],
                'images' => ['http://localhost:3000/images/prod_beauty_palette.webp'],
                'brand' => 'Swiss Beauty',
            ],
            [
                'name' => 'Soft Toys',
                'description' => 'Cute teddy bear soft toy.',
                'price' => 499.00,
                'category_id' => $catIds['toys'], 
                'images' => ['http://localhost:3000/images/prod_toys_soft.png'],
                'brand' => 'Disney',
            ],

            // Home
            [
                'name' => 'King Size Bed',
                'description' => 'Solid wood king size bed.',
                'price' => 25000.00,
                'category_id' => $catIds['home'],
                'images' => ['http://localhost:3000/images/prod_home_bed.webp'],
                'brand' => 'Wakefit',
            ],
            [
                'name' => 'Luxury Sofa',
                'description' => '3 Seater luxury sofa.',
                'price' => 35000.00,
                'category_id' => $catIds['home'],
                'images' => ['http://localhost:3000/images/prod_home_sofa.webp'],
                'brand' => 'IKEA',
            ],
             [
                'name' => 'Indoor Plants',
                'description' => 'Air purifying indoor plant.',
                'price' => 499.00,
                'category_id' => $catIds['home'],
                'images' => ['http://localhost:3000/images/prod_home_plant.webp'],
                'brand' => 'Ugaoo',
            ],
            [
                'name' => 'Hanging Swing',
                'description' => 'Comfortable balcony swing.',
                'price' => 6500.00,
                'category_id' => $catIds['home'],
                'images' => ['http://localhost:3000/images/prod_home_swing.png'],
                'brand' => 'DecorNation',
            ],
            [
                'name' => 'Wall Decor Frames',
                'description' => 'Set of 3 modern art frames.',
                'price' => 1299.00,
                'category_id' => $catIds['home'],
                'images' => ['http://localhost:3000/images/prod_home_plant.webp'], // Fallback image used
                'brand' => 'Best Deal',
            ],
            [
                'name' => 'Modern Showpiece',
                'description' => 'Abstract art showpiece.',
                'price' => 899.00,
                'category_id' => $catIds['home'],
                'images' => ['http://localhost:3000/images/prod_home_plant.webp'], // Fallback image used
                'brand' => 'Home Centre',
            ],

            // Travel
            [
                'name' => 'Travel Backpack',
                'description' => 'Waterproof hiking backpack.',
                'price' => 2499.00,
                'category_id' => $catIds['travel'],
                'images' => ['http://localhost:3000/images/prod_travel_backpack.png'],
                'brand' => 'Wildcraft',
            ],
            [
                'name' => 'Cabin Luggage',
                'description' => 'Hard shell trolley bag.',
                'price' => 4500.00,
                'category_id' => $catIds['travel'],
                'images' => ['http://localhost:3000/images/prod_travel_luggage.png'],
                'brand' => 'American Tourister',
            ],
        ];

        foreach ($products as $prodData) {
            // Extract images from the data array
            $images = $prodData['images'] ?? [];
            unset($prodData['images']);

            $product = Product::create(array_merge($prodData, [
                'seller_id' => $seller->id,
                'stock' => 50,
                'commission_level' => '6-4-2',
                'status' => 'approved',
            ]));

            // Create ProductImage records
            foreach ($images as $index => $imageUrl) {
                // Ensure the URL is clean (remove double slashes if any, though the input seems static)
                \App\Models\ProductImage::create([
                    'product_id' => $product->id,
                    'url' => $imageUrl,
                    'is_primary' => $index === 0 ? 'true' : 'false', // Use string literals for Postgres compatibility
                    'order' => $index,
                    'alt_text' => $product->name
                ]);
            }
        }
    }
}
