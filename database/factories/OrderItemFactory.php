<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id'   => null,
            'service_id' => null,
            'name'       => null,
            'quantity'   => null,
            'subtotal'   => 0,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function ($item) {
            $service = Service::find($item->service_id) ?? Service::inRandomOrder()->first() ?? Service::factory()->create();

            $item->service_id ??= $service->id;
            $item->name       ??= $service->name;

            $item->quantity ??= $service->unit === 'pcs'
                ? rand(1, 20)
                : round(fake()->randomFloat(1, 0.5, 10), 2);

            $item->subtotal = round($item->quantity * $service->price, 2);
        });
    }

    public function forOrder(Order $order): static
    {
        return $this->state(fn() => [
            'order_id' => $order->id,
        ]);
    }

    public function forService(Service $service): static
    {
        return $this->state(fn() => [
            'service_id' => $service->id,
            'name'       => $service->name,
        ]);
    }

    public function withQuantity(float|int $quantity): static
    {
        return $this->state(fn() => [
            'quantity' => $quantity,
        ]);
    }
}
