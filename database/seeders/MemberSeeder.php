<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Database\Seeder;

class MemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = MembershipPlan::all();

        if ($plans->isEmpty()) {
            $this->command->warn('No membership plans found. Run MembershipPlanSeeder first.');

            return;
        }

        Member::factory(50)->create()->each(function ($member) use ($plans) {
            $plan = $plans->random();

            $subscription = Subscription::factory()
                ->for($member)
                ->for($plan, 'membershipPlan')
                ->active()
                ->create();

            Payment::factory()
                ->for($member)
                ->for($subscription)
                ->completed()
                ->create([
                    'amount' => $plan->price,
                    'currency' => $plan->currency,
                ]);

            Attendance::factory(rand(5, 20))
                ->for($member)
                ->create();
        });

        Member::factory(10)->inactive()->create();
    }
}
