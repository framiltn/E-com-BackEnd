<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\User;
use App\Models\Category;
use App\Models\StoreSettings;
use Illuminate\Support\Facades\DB;

class RealProductSeeder extends Seeder
{
    public function run()
    {
        // cleanup existing products if desired? For now, we append.
        // DB::table('products')->truncate();
        // DB::table('categories')->truncate(); // Don't truncate if referenced elsewhere lightly.
        
        $seller = User::whereHas('roles', function($q) {
            $q->where('name', 'seller');
        })->first();

        if (!$seller) {
            $seller = User::factory()->create(['name' => 'ProSeller', 'email' => 'seller@example.com', 'password' => bcrypt('password')]);
            $seller->assignRole('seller');
            StoreSettings::create([
                'seller_id' => $seller->id,
                'store_name' => 'Premium Store',
                'brand_story' => 'Your one stop shop for premium goods.',
                'shipping_type' => 'self',
                'flat_shipping_rate' => 0.00,
            ]);
        }

        // Ensure categories exist
        $this->call(CategorySeeder::class);
        
        // Helper to get cat ID
        $getCat = fn($slug) => Category::where('slug', $slug)->first()->id ?? Category::first()->id;

        $products = [
            [
                'name' => 'iPhone 15 Pro Titanium',
                'description' => 'The ultimate iPhone. Forged in titanium. Features the A17 Pro chip, a customizable Action button, and a more versatile Pro camera system.',
                'price' => 134900.00,
                'stock' => 50,
                'category_id' => $getCat('mobiles'),
                'image' => '/images/products/electronics_iphone_1767595208932.png',
                'brand' => 'Apple'
            ],
            [
                'name' => 'Samsung Galaxy S24 Ultra',
                'description' => 'Unleash your creativity, productivity, and possibility with the Samsung Galaxy S24 Ultra. Powered by Galaxy AI.',
                'price' => 129999.00,
                'stock' => 45,
                'category_id' => $getCat('mobiles'),
                'image' => '/images/products/electronics_samsung_1767595225967.png',
                'brand' => 'Samsung'
            ],
             [
                'name' => 'Men\'s Denim Jacket',
                'description' => 'Classic denim jacket with a modern twig. Durable, stylish and comfortable for everyday wear.',
                'price' => 2499.00,
                'stock' => 100,
                'category_id' => $getCat('fashion'),
                'image' => '/images/products/fashion_jacket_1767595243244.png',
                'brand' => 'Levis'
            ],
            [
                'name' => 'Floral Summer Dress',
                'description' => 'Elegant floral print dress, perfect for summer outings. Breathable fabric and flattering fit.',
                'price' => 1899.00,
                'stock' => 80,
                'category_id' => $getCat('fashion'),
                'image' => '/images/products/fashion_dress_1767595276450.png',
                'brand' => 'Zara'
            ],
            [
                'name' => 'Premium Red Lipstick',
                'description' => 'Long-lasting matte finish red lipstick. Intense color payoff and moisturizing formula.',
                'price' => 1250.00, // Meets min price > 1200 check
                'stock' => 200,
                'category_id' => $getCat('beauty'),
                'image' => '/images/products/beauty_lipstick_1767595300474.png',
                'brand' => 'MAC'
            ],
             [
                'name' => 'Lego Technic Race Car',
                'description' => 'Build your own race car with this detailed Lego Technic set. Features moving pistons and steering.',
                'price' => 4999.00,
                'stock' => 30,
                'category_id' => $getCat('toys'),
                'image' => '/images/products/toys_lego_1767595320317.png',
                'brand' => 'Lego'
            ],
            [
                'name' => 'Front Load Washing Machine',
                'description' => 'High efficiency front load washing machine with steam cycle and smart diagnosis.',
                'price' => 32990.00,
                'stock' => 15,
                'category_id' => $getCat('appliances'),
                'image' => '/images/products/appliances_washing_machine_1767595334415.png',
                'brand' => 'LG'
            ],
            [
                'name' => 'Abstract Wall Monitor Frames',
                'description' => 'Set of 3 minimalist wall frames. Perfect for modern living rooms.',
                'price' => 2999.00,
                'stock' => 60,
                'category_id' => $getCat('home'),
                'image' => '/images/products/wall_decor_frames_1767593765624.png',
                'brand' => 'DecorNation'
            ],
            [
                'name' => 'Golden Geometric Showpiece',
                'description' => 'Luxurious golden geometric sculpture. Adds a touch of sophistication to any space.',
                'price' => 1499.00,
                'stock' => 40,
                'category_id' => $getCat('home'),
                'image' => '/images/products/modern_showpiece_1767593780024.png',
                'brand' => 'Home Centre'
            ],
            // Gaming
            [
                'name' => 'Pro RGB Gaming Headset',
                'description' => 'Immersive 7.1 surround sound with noise-cancelling microphone and RGB lighting.',
                'price' => 3499.00,
                'stock' => 50,
                'category_id' => $getCat('gaming'),
                'image' => '/images/products/gaming_headset_1767596753281.png',
                'brand' => 'Razer'
            ],
            [
                'name' => 'Mechanical Gaming Keyboard',
                'description' => 'Tactile mechanical switches with customizable RGB backlight.',
                'price' => 4999.00,
                'stock' => 30,
                'category_id' => $getCat('gaming'),
                'image' => '/images/products/gaming_keyboard_1767596771921.png',
                'brand' => 'Logitech'
            ],
            [
                'name' => 'Wireless Gaming Mouse',
                'description' => 'Ultra-lightweight gaming mouse with high precision sensor.',
                'price' => 2999.00,
                'stock' => 45,
                'category_id' => $getCat('gaming'),
                'image' => '/images/products/gaming_mouse_1767596787709.png',
                'brand' => 'SteelSeries'
            ],
            [
                'name' => 'High Performance Gaming Laptop',
                'description' => 'Powered by latest RTX graphics and high refresh rate screen.',
                'price' => 115000.00,
                'stock' => 10,
                'category_id' => $getCat('gaming'),
                'image' => '/images/products/gaming_laptop_1767596805317.png',
                'brand' => 'ASUS'
            ],
            // Electronics/Accessories
            [
                'name' => 'Fitness Smartwatch',
                'description' => 'Track your health, workouts and sleep with this advanced smartwatch.',
                'price' => 3999.00,
                'stock' => 100,
                'category_id' => $getCat('electronics'),
                'image' => '/images/products/tech_smartwatch_1767596830463.png',
                'brand' => 'Noise'
            ],
            [
                'name' => 'Premium Wireless Earbuds',
                'description' => 'Active Noise Cancellation and crystal clear audio.',
                'price' => 7999.00,
                'stock' => 60,
                'category_id' => $getCat('electronics'),
                'image' => '/images/products/tech_earbuds_1767596847065.png',
                'brand' => 'Sony'
            ],
            [
                'name' => 'Slim Power Bank 20000mAh',
                'description' => 'Fast charging portable power bank for all your devices.',
                'price' => 1599.00,
                'stock' => 150,
                'category_id' => $getCat('electronics'),
                'image' => '/images/products/tech_powerbank_1767596862741.png',
                'brand' => 'Anker'
            ],
            // Appliances
            [
                'name' => '55" 4K Smart TV',
                'description' => 'Ultra HD Smart LED TV with vivid colors and smart apps.',
                'price' => 42990.00,
                'stock' => 20,
                'category_id' => $getCat('appliances'),
                'image' => '/images/products/appliance_tv_1767596880575.png',
                'brand' => 'Samsung'
            ],
            [
                'name' => 'Double Door Refrigerator',
                'description' => 'Frost-free double door fridge with energy saving mode.',
                'price' => 26990.00,
                'stock' => 15,
                'category_id' => $getCat('appliances'),
                'image' => '/images/products/appliance_fridge_1767596896709.png',
                'brand' => 'Whirlpool'
            ],
            // Travel
            [
                'name' => 'Waterproof Hiking Backpack',
                'description' => 'Durable 45L backpack for trekking and travel.',
                'price' => 2499.00,
                'stock' => 40,
                'category_id' => $getCat('travel'),
                'image' => '/images/products/travel_backpack_1767596927458.png',
                'brand' => 'Wildcraft'
            ],
            [
                'name' => 'Cabin Luggage Suitcase',
                'description' => 'Hard-shell spinner suitcase, cabin size approved.',
                'price' => 4499.00,
                'stock' => 30,
                'category_id' => $getCat('travel'),
                'image' => '/images/products/travel_luggage_1767596944338.png',
                'brand' => 'American Tourister'
            ],
            // Home
            [
                'name' => 'Modern Fabric Sofa',
                'description' => 'Comfortable 3-seater sofa with premium grey fabric.',
                'price' => 22999.00,
                'stock' => 10,
                'category_id' => $getCat('home'),
                'image' => '/images/products/home_sofa_1767596963468.png',
                'brand' => 'IKEA'
            ],
            [
                'name' => 'King Size Solid Wood Bed',
                'description' => 'Premium teak wood king size bed with storage.',
                'price' => 35999.00,
                'stock' => 5,
                'category_id' => $getCat('home'),
                'image' => '/images/products/home_sofa_1767596963468.png', // Fallback to Sofa
                'brand' => 'Wakefit'
            ],
             [
                'name' => 'Indoor Snake Plant',
                'description' => 'Air purifying indoor snake plant with ceramic pot.',
                'price' => 499.00,
                'stock' => 50,
                'category_id' => $getCat('home'),
                'image' => '/images/products/home_plant_real.jpg',
                'brand' => 'Ugaoo'
            ],
            // Grocery
            [
                'name' => 'California Almonds 500g',
                'description' => 'Premium quality crunchy almonds, high in protein.',
                'price' => 650.00,
                'stock' => 200,
                'category_id' => $getCat('grocery'),
                'image' => '/images/products/grocery_almonds_real_v2.jpg',
                'brand' => 'Happilo'
            ],
            [
                'name' => 'Basmati Rice 5kg',
                'description' => 'Long grain aromatic basmati rice for daily use.',
                'price' => 850.00,
                'stock' => 100,
                'category_id' => $getCat('grocery'),
                'image' => '/images/products/grocery_rice_real.jpg',
                'brand' => 'India Gate'
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
        }
    }
}
