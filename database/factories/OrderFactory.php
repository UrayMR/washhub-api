<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $customer = Customer::inRandomOrder()->first() ?? Customer::factory()->create();
        $user = User::inRandomOrder()->first() ?? User::factory()->create();

        return [
            'customer_id' => $customer->id,
            'user_id'     => $user->id,
            'order_status' => $this->faker->randomElement(['pending', 'processing', 'done', 'canceled']),
            'total_price' => $this->faker->randomFloat(2, 10000, 500000),
            'notes'       => $this->faker->optional()->sentence(),
            'pickup_date' => $this->faker->optional()->dateTimeBetween('now', '+5 days'),
        ];
    }
}
