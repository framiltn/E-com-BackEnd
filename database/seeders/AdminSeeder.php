<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin role if not exists
        if (!Role::where('name', 'admin')->exists()) {
            Role::create(['name' => 'admin']);
        }

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@marketplace.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('admin123'),
            ]
        );

        // Assign admin role
        $admin->syncRoles(['admin']);

        $this->command->info('Admin user created: admin@marketplace.com / admin123');
    }
}
