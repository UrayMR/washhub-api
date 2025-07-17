<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $service = Service::inRandomOrder()->first() ?? Service::factory()->create();

        // Qty value by their unit
        $quantity = $service->unit === 'pcs'
            ? $this->faker->numberBetween(1, 20) // pcs
            : $this->faker->randomFloat(1, 0.5, 10); // kg

        // Counting service price times the qty
        $totalPrice = round($service->price * $quantity, 2);

        return [
            'order_id' => Order::factory(),
            'service_id' => $service->id,
            'name' => $service->name,
            'quantity' => $quantity,
            'price' => $totalPrice,
        ];
    }
}
