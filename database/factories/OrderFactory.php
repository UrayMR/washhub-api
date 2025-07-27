<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_id'  => Customer::factory(),
            'user_id'      => User::factory(),
            'order_status' => $this->faker->randomElement(OrderStatus::cases()),
            'total_price'  => 0,
            'note'         => $this->faker->optional()->sentence(),
            'pickup_date'  => $this->faker->optional()->dateTimeBetween('now', '+5 days'),
        ];
    }

    public function withItems(int $count = 2): static
    {
        return $this->afterCreating(function (Order $order) use ($count) {
            $items = OrderItem::factory()->count($count)->forOrder($order)->make();

            $order->items()->saveMany($items);

            $total = $order->items->sum(
                fn($item) =>
                $item->quantity * $item->service->price
            );

            $order->update(['total_price' => round($total, 2)]);
        });
    }

    public function forCustomer(Customer $customer): static
    {
        return $this->state(fn() => [
            'customer_id' => $customer->id,
        ]);
    }

    public function forUser(User $user): static
    {
        return $this->state(fn() => [
            'user_id' => $user->id,
        ]);
    }
}
