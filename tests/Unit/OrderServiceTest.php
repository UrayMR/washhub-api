<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Service;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_number_is_auto_generated_on_create()
    {
        $order = Order::factory()->create();
        $this->assertNotEmpty($order->order_number);
        $this->assertMatchesRegularExpression('/^ORD-\d{8}-\d{5}$/', $order->order_number);
    }

    public function test_calculate_total_price()
    {
        $service = Service::factory()->create(['price' => 10000, 'unit' => 'pcs']);
        $order = Order::factory()->create();
        $item1 = OrderItem::factory()->create([
            'order_id' => $order->id,
            'service_id' => $service->id,
            'name' => $service->name,
            'quantity' => 2,
        ]);
        $item2 = OrderItem::factory()->create([
            'order_id' => $order->id,
            'service_id' => $service->id,
            'name' => $service->name,
            'quantity' => 3,
        ]);
        $order->refresh();
        $orderService = new OrderService();
        $total = $orderService->calculateTotalPrice($order);
        $expected = $order->items->sum('subtotal');
        $this->assertEquals($expected, $total);
    }
}
