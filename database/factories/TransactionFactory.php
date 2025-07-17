<?php

namespace Database\Factories;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $invoice = Invoice::inRandomOrder()->first() ?? Invoice::factory()->create();

        return [
            'invoice_id' => $invoice->id,
            'payment_method' => $this->faker->randomElement(['cash', 'transfer', 'qris']),
            'paid_amount' => $invoice->amount,
            'paid_at' => now(),
            'reference_number' => $this->faker->optional()->regexify('[A-Z0-9]{10}'),
        ];
    }
}
