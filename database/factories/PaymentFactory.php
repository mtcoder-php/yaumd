<?php

namespace Database\Factories;

use App\Models\Contract;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        $paidAt = now()->subDays(fake()->numberBetween(0, 250));

        return [
            'contract_id'        => Contract::factory(),
            'user_id'            => null,
            'amount'             => fake()->randomElement([1000000, 1500000, 2000000, 3000000, 4000000, 5000000]),
            'provider'           => fake()->randomElement(['cash', 'cash', 'click', 'payme']),
            'transaction_id'     => null,
            'status'             => 'paid',
            'provider_data'      => null,
            'paid_at'            => $paidAt,
            'cancelled_at'       => null,
            'cancel_reason'      => null,
            'payme_create_time'  => null,
            'payme_perform_time' => null,
            'payme_cancel_time'  => null,
        ];
    }

    public function forContract(int $contractId): static
    {
        return $this->state(fn () => ['contract_id' => $contractId]);
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => 'pending', 'paid_at' => null]);
    }

    public function cash(): static
    {
        return $this->state(fn () => ['provider' => 'cash']);
    }
}
