<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\FAQ;
use App\Models\Page;
use Illuminate\Database\Seeder;

class CMSSeeder extends Seeder
{
    public function run(): void
    {
        // Banners
        Banner::create([
            'title' => 'Big Sale Alert',
            'image_url' => '/images/banner1.jpg',
            'link' => '/products?category=electronics',
            'order' => 1,
            'is_active' => 'true',
        ]);

        Banner::create([
            'title' => 'New Arrivals',
            'image_url' => '/images/banner2.jpg',
            'link' => '/products?category=fashion',
            'order' => 2,
            'is_active' => 'true',
        ]);

        // Pages
        Page::create([
            'title' => 'About Us',
            'slug' => 'about-us',
            'content' => '<p>Welcome to our Marketplace. We are dedicated to providing the best products.</p>',
            'meta_description' => 'About our company',
            'is_published' => 'true',
        ]);

        Page::create([
            'title' => 'Privacy Policy',
            'slug' => 'privacy-policy',
            'content' => '<p>Your privacy is important to us...</p>',
            'meta_description' => 'Privacy Policy',
            'is_published' => 'true',
        ]);

        // FAQs
        FAQ::create([
            'question' => 'How can I track my order?',
            'answer' => 'You can track your order from the "My Orders" section in your profile.',
            'category' => 'Orders',
            'order' => 1,
            'is_active' => 'true',
        ]);
        
        FAQ::create([
            'question' => 'What is the return policy?',
            'answer' => 'We accept returns within 7 days of delivery.',
            'category' => 'Returns',
            'order' => 2,
            'is_active' => 'true',
        ]);
    }
}
