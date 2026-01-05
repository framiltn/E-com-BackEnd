<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Electronics', 'slug' => 'electronics'],
            ['name' => 'Mobiles', 'slug' => 'mobiles'],
            ['name' => 'Fashion', 'slug' => 'fashion'],
            ['name' => 'Home', 'slug' => 'home'],
            ['name' => 'Appliances', 'slug' => 'appliances'],
            ['name' => 'Beauty', 'slug' => 'beauty'],
            ['name' => 'Sports', 'slug' => 'sports'],
            ['name' => 'Toys', 'slug' => 'toys'],
            ['name' => 'Books', 'slug' => 'books'],
            ['name' => 'Grocery', 'slug' => 'grocery'],
            ['name' => 'Travel', 'slug' => 'travel'],
            ['name' => 'Gaming', 'slug' => 'gaming'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
            ['slug' => $category['slug']],   // Unique check
            ['name' => $category['name']]
            );
        }
    }
}
