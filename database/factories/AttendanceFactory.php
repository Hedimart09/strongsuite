<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $checkIn = fake()->dateTimeBetween('-1 month', 'now');
        $checkOut = fake()->boolean(70)
            ? (clone $checkIn)->modify('+'.fake()->numberBetween(30, 180).' minutes')
            : null;

        return [
            'check_in_time' => $checkIn,
            'check_out_time' => $checkOut,
            'check_in_method' => fake()->randomElement(['qr_code', 'manual']),
            'notes' => fake()->boolean(20) ? fake()->sentence() : null,
        ];
    }

    public function checkedOut(): static
    {
        return $this->state(function (array $attributes) {
            $checkIn = $attributes['check_in_time'] ?? now()->subHours(2);

            return [
                'check_out_time' => (clone $checkIn)->modify('+'.fake()->numberBetween(60, 120).' minutes'),
            ];
        });
    }

    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'check_in_time' => now()->subHours(fake()->numberBetween(1, 8)),
            'check_out_time' => null,
        ]);
    }
}
