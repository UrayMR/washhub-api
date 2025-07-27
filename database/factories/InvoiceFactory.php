<?php

namespace Database\Factories;

use App\Models\Order;
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
        $order = Order::inRandomOrder()->first() ?? Order::factory()->create();

        return [
            'order_id' => $order->id,
            'amount' => $order->total_price,
            'issued_at' => now(),
            'status' => $this->faker->randomElement(['unpaid', 'paid', 'cancelled']),
        ];
    }
}
