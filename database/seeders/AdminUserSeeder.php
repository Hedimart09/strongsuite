<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create or update the first admin user
        $admin1 = User::updateOrCreate(
            ['email' => 'aaritsolution@gmail.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'), // Change this password
                'email_verified_at' => now(),
            ]
        );

        // Assign Admin role
        if (! $admin1->hasRole('Admin')) {
            $admin1->assignRole('Admin');
        }

        // Create or update the second admin user
        $admin2 = User::updateOrCreate(
            ['email' => 'martinsonhenry09@gmail.com'],
            [
                'name' => 'Henry Martinson',
                'password' => Hash::make('password'), // Change this password
                'email_verified_at' => now(),
            ]
        );

        // Assign Admin role
        if (! $admin2->hasRole('Admin')) {
            $admin2->assignRole('Admin');
        }

        $this->command->info('Admin users created/updated:');
        $this->command->info('- aaritsolution@gmail.com');
        $this->command->info('- martinsonhenry09@gmail.com');
        $this->command->info('Password for both: password (please change this!)');
    }
}
