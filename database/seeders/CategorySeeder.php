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
            ['name' => 'Fashion', 'slug' => 'fashion'],
            ['name' => 'Home & Garden', 'slug' => 'home-garden'],
            ['name' => 'Books', 'slug' => 'books'],
            ['name' => 'Toys', 'slug' => 'toys'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
            ['slug' => $category['slug']],   // Unique check
            ['name' => $category['name']]
            );
        }
    }
}
