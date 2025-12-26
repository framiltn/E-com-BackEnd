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
        $admin = User::updateOrCreate(
            ['email' => 'admin@marketplace.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
            ]
        );

        // Assign admin role
        $admin->syncRoles(['admin']);

        $this->command->info('Admin user created: admin@marketplace.com / admin123');
    }
}
