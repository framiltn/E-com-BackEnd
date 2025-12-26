<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create roles first
        $this->call(RoleSeeder::class);
        $this->call(CategorySeeder::class);
        
        // Create admin user
        $this->call(AdminSeeder::class);

        // Optionally create a test user
        // Then assign roles as needed
    }
}
