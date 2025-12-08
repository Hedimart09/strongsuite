<?php

namespace Database\Factories;

use App\Models\Member;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = $this->faker->numberBetween(5000, 50000);
        $taxAmount = (int) ($subtotal * 0.15);
        $totalAmount = $subtotal + $taxAmount;

        $issueDate = $this->faker->dateTimeBetween('-1 month', 'now');
        $dueDate = (clone $issueDate)->modify('+14 days');

        return [
            'invoice_number' => 'INV-'.strtoupper($this->faker->unique()->bothify('########')),
            'member_id' => Member::factory(),
            'subscription_id' => null,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'currency' => 'GHS',
            'issue_date' => $issueDate,
            'due_date' => $dueDate,
            'status' => $this->faker->randomElement(['draft', 'sent', 'paid']),
            'paid_at' => null,
            'notes' => $this->faker->optional()->sentence(),
            'metadata' => null,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'sent',
            'due_date' => now()->subDays(5),
            'paid_at' => null,
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'paid_at' => null,
        ]);
    }

    public function forSubscription(): static
    {
        return $this->state(fn (array $attributes) => [
            'subscription_id' => Subscription::factory(),
        ]);
    }
}
