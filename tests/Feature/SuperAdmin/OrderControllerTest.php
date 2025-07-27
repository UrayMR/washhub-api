<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
  use RefreshDatabase;

  protected User $superAdmin;

  protected function setUp(): void
  {
    parent::setUp();
    $this->superAdmin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
    $this->actingAs($this->superAdmin, 'sanctum');
  }

  public function test_can_index_order_as_super_admin(): void
  {
    $orders = Order::factory()->count(3)->create();

    $response = $this->getJson('/api/orders');
    $response->assertOk();

    foreach ($orders as $order) {
      $response->assertJsonFragment(['id' => $order->id]);
    }
  }

  public function test_can_show_order_as_super_admin(): void
  {
    $order = Order::factory()->create();

    $response = $this->getJson("/api/orders/{$order->id}");
    $response->assertOk()->assertJsonFragment([
      'id' => $order->id,
      'order_number' => $order->order_number,
    ]);
  }

  public function test_can_create_order_with_customer_and_items_as_super_admin(): void
  {
    $customerData = Customer::factory()->make()->toArray();
    $service1 = Service::factory()->create();
    $service2 = Service::factory()->create();

    $orderItems = [
      ['service_id' => $service1->id, 'name' => 'Bed Sheet', 'quantity' => 2],
      ['service_id' => $service2->id, 'name' => 'Towel', 'quantity' => 1],
    ];

    $orderData = Order::factory()->make()->toArray();
    $payload = array_merge($orderData, [
      'customer' => $customerData,
      'items' => $orderItems,
    ]);

    $response = $this->postJson('/api/orders', $payload);
    $response->assertCreated();

    $orderId = $response->json('data.id');
    $orderNumber = $response->json('data.order_number');

    $this->assertDatabaseHas('customers', [
      'name' => $customerData['name'],
      'phone_number' => $customerData['phone_number'],
    ]);

    $this->assertDatabaseHas('orders', [
      'order_number' => $orderNumber,
    ]);

    foreach ($orderItems as $item) {
      $this->assertDatabaseHas('order_items', [
        'order_id' => $orderId,
        'service_id' => $item['service_id'],
        'name' => $item['name'],
        'quantity' => $item['quantity'],
      ]);
    }
  }

  public function test_can_update_order_with_customer_and_items_as_super_admin(): void
  {
    $order = Order::factory()->create();
    $customer = $order->customer;

    $newCustomerData = [
      'name' => 'Updated Customer',
      'phone_number' => '081234567890',
      'address' => 'Updated Address',
    ];

    $service = Service::factory()->create();
    $updatedItems = [
      ['service_id' => $service->id, 'name' => 'Updated Item', 'quantity' => 3],
    ];

    $payload = [
      'order_status' => 'completed',
      'pickup_date' => now()->addDays(2)->toDateString(),
      'customer' => $newCustomerData,
      'items' => $updatedItems,
    ];

    $response = $this->putJson("/api/orders/{$order->id}", $payload);
    $response->assertOk();

    $this->assertDatabaseHas('orders', [
      'id' => $order->id,
      'order_status' => 'completed',
    ]);

    $this->assertDatabaseHas('customers', [
      'id' => $customer->id,
      'name' => $newCustomerData['name'],
      'phone_number' => $newCustomerData['phone_number'],
    ]);

    foreach ($updatedItems as $item) {
      $this->assertDatabaseHas('order_items', [
        'order_id' => $order->id,
        'service_id' => $item['service_id'],
        'name' => $item['name'],
        'quantity' => $item['quantity'],
      ]);
    }
  }

  public function test_can_delete_order_as_super_admin(): void
  {
    $order = Order::factory()->withItems(2)->create();

    $response = $this->deleteJson("/api/orders/{$order->id}");
    $response->assertOk();

    $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    $this->assertDatabaseMissing('order_items', ['order_id' => $order->id]);
  }
}
