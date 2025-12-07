<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $paymentMethods = ['stripe', 'paystack', 'flutterwave', 'cash', 'mobile_money'];
        $paymentMethod = fake()->randomElement($paymentMethods);

        return [
            'amount' => fake()->numberBetween(2000, 50000),
            'currency' => 'GHS',
            'payment_method' => $paymentMethod,
            'payment_gateway' => in_array($paymentMethod, ['stripe', 'paystack', 'flutterwave']) ? $paymentMethod : null,
            'transaction_id' => in_array($paymentMethod, ['stripe', 'paystack', 'flutterwave'])
                ? 'TXN-'.strtoupper(fake()->unique()->bothify('????????????'))
                : null,
            'status' => fake()->randomElement(['pending', 'completed', 'failed']),
            'payment_date' => fake()->dateTimeBetween('-6 months', 'now'),
            'metadata' => [],
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'payment_date' => now(),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'payment_date' => null,
        ]);
    }
}
