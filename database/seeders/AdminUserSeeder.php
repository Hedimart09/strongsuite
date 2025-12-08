<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create or update the admin user
        $admin = User::updateOrCreate(
            ['email' => 'aaritsolution@gmail.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'), // Change this password
                'email_verified_at' => now(),
            ]
        );

        // Assign Admin role
        if (! $admin->hasRole('Admin')) {
            $admin->assignRole('Admin');
        }

        $this->command->info('Admin user created/updated: aaritsolution@gmail.com');
        $this->command->info('Password: password (please change this!)');
    }
}
