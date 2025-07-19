<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Service;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Store a new Order along with its Customer and OrderItems.
     */
    public function store(array $validated, $user): Order
    {
        return DB::transaction(function () use ($validated, $user) {
            // Create or find customer
            $customer = Customer::firstOrCreate(
                ['phone_number' => $validated['customer']['phone_number']],
                [
                    'name' => $validated['customer']['name'],
                    'address' => $validated['customer']['address'] ?? null,
                ]
            );

            // Create order
            $order = Order::create([
                'customer_id' => $customer->id,
                'user_id' => $user->id,
                'order_status' => $validated['order_status'] ?? OrderStatus::pending,
                'notes' => $validated['notes'] ?? null,
                'pickup_date' => $validated['pickup_date'] ?? null,
                'total_price' => 0, // placeholder
            ]);

            // Process items
            $total = 0;
            foreach ($validated['items'] as $item) {
                $service = Service::findOrFail($item['service_id']);
                $subtotal = $service->price * $item['quantity'];

                $order->items()->create([
                    'service_id' => $service->id,
                    'name'       => $item['name'],
                    'quantity'   => $item['quantity'],
                    'subtotal'   => $subtotal,
                ]);

                $total += $subtotal;
            }

            $order->update(['total_price' => $total]);

            return $order;
        });
    }

    /**
     * Update an existing order, its customer, and its order items.
     */
    public function update(Order $order, array $validated): Order
    {
        return DB::transaction(function () use ($order, $validated) {
            // Update customer if present
            if (isset($validated['customer'])) {
                $order->customer->update([
                    'name'         => $validated['customer']['name'],
                    'phone_number' => $validated['customer']['phone_number'],
                    'address'      => $validated['customer']['address'] ?? null,
                ]);
            }

            // Update order fields
            $order->update([
                'order_status' => $validated['order_status'] ?? $order->order_status,
                'notes'        => $validated['notes'] ?? $order->notes,
                'pickup_date'  => $validated['pickup_date'] ?? $order->pickup_date,
            ]);

            // Update, Create, Delete Order Items & Counting Total Price
            if (isset($validated['items'])) {
                $existingIds = [];

                $total = 0;

                foreach ($validated['items'] as $item) {
                    $service = Service::findOrFail($item['service_id']);
                    $subtotal = $service->price * $item['quantity'];

                    $orderItem = $order->items()->updateOrCreate(
                        ['id' => $item['id'] ?? null],
                        [
                            'service_id' => $service->id,
                            'name'       => $item['name'],
                            'quantity'   => $item['quantity'],
                            'subtotal'   => $subtotal,
                        ]
                    );

                    $existingIds[] = $orderItem->id;
                    $total += $subtotal;
                }

                // Delete items that were removed on the client side
                $order->items()->whereNotIn('id', $existingIds)->delete();

                $order->update(['total_price' => $total]);
            }

            return $order;
        });
    }
}
