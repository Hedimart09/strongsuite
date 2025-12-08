<?php

namespace Database\Seeders;

use App\Models\GymSettings;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles and permissions first
        $this->call([
            RolesAndPermissionsSeeder::class,
            AdminUserSeeder::class,
        ]);

        GymSettings::create([
            'gym_name' => 'Strongsuite Fitness',
            'email' => 'info@strongsuite.com',
            'phone' => '+233 XX XXX XXXX',
            'address' => 'Accra, Ghana',
            'timezone' => 'Africa/Accra',
            'currency' => 'GHS',
            'language' => 'en',
            'tax_rate' => 15.00,
            'payment_gateways' => [],
            'features' => [],
        ]);

        $this->call([
            MembershipPlanSeeder::class,
            MemberSeeder::class,
        ]);
    }
}
