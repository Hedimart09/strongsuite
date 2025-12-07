<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MembershipPlan>
 */
class MembershipPlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $plans = [
            ['name' => 'Daily Pass', 'duration' => 1, 'price' => 2000],
            ['name' => 'Weekly Pass', 'duration' => 7, 'price' => 12000],
            ['name' => 'Monthly Membership', 'duration' => 30, 'price' => 40000],
            ['name' => 'Quarterly Membership', 'duration' => 90, 'price' => 110000],
            ['name' => 'Annual Membership', 'duration' => 365, 'price' => 400000],
        ];

        $plan = fake()->randomElement($plans);

        return [
            'name' => $plan['name'],
            'description' => fake()->sentence(),
            'price' => $plan['price'],
            'currency' => 'GHS',
            'duration_in_days' => $plan['duration'],
            'is_active' => true,
            'features' => [
                'Gym access',
                'Locker access',
                'Free WiFi',
            ],
        ];
    }

    public function monthly(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Monthly Membership',
            'duration_in_days' => 30,
            'price' => 40000,
        ]);
    }

    public function yearly(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Annual Membership',
            'duration_in_days' => 365,
            'price' => 400000,
        ]);
    }
}
