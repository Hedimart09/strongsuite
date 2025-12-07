<?php

namespace Database\Seeders;

use App\Models\MembershipPlan;
use Illuminate\Database\Seeder;

class MembershipPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Daily Pass',
                'description' => 'Perfect for drop-ins and visitors. Full gym access for one day.',
                'price' => 2000,
                'currency' => 'GHS',
                'duration_in_days' => 1,
                'is_active' => true,
                'features' => ['Gym access', 'Locker access'],
            ],
            [
                'name' => 'Weekly Pass',
                'description' => 'Great for short-term commitments. One week of unlimited access.',
                'price' => 12000,
                'currency' => 'GHS',
                'duration_in_days' => 7,
                'is_active' => true,
                'features' => ['Gym access', 'Locker access', 'Free WiFi'],
            ],
            [
                'name' => 'Monthly Membership',
                'description' => 'Our most popular plan. Full access for 30 days with flexible renewal.',
                'price' => 40000,
                'currency' => 'GHS',
                'duration_in_days' => 30,
                'is_active' => true,
                'features' => ['Gym access', 'Locker access', 'Free WiFi', '1 free class per week'],
            ],
            [
                'name' => 'Quarterly Membership',
                'description' => 'Commit for 3 months and save! Best value for regular gym-goers.',
                'price' => 110000,
                'currency' => 'GHS',
                'duration_in_days' => 90,
                'is_active' => true,
                'features' => ['Gym access', 'Locker access', 'Free WiFi', 'Unlimited classes', 'Free towel service'],
            ],
            [
                'name' => 'Annual Membership',
                'description' => 'The ultimate commitment to your fitness. One full year of access with maximum savings.',
                'price' => 400000,
                'currency' => 'GHS',
                'duration_in_days' => 365,
                'is_active' => true,
                'features' => [
                    'Gym access',
                    'Locker access',
                    'Free WiFi',
                    'Unlimited classes',
                    'Free towel service',
                    '2 guest passes per month',
                    'Priority booking',
                ],
            ],
        ];

        foreach ($plans as $plan) {
            MembershipPlan::create($plan);
        }
    }
}
