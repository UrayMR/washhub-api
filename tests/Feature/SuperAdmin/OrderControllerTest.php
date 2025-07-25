<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
  use RefreshDatabase;

  public function test_can_index_order_as_super_admin()
  {
    $superAdmin = User::factory()->create(['role' => 'super-admin']);
    $this->actingAs($superAdmin, 'sanctum');

    $orders = Order::factory(3)->create();
    $response = $this->getJson('/api/orders');

    $response->assertOk();

    foreach ($orders as $order) {
      $response->assertJsonFragment([
        'id' => $order->id,
      ]);
    }
  }

  public function test_can_show_order_as_super_admin()
  {
    $superAdmin = User::factory()->create(['role' => 'super-admin']);
    $this->actingAs($superAdmin, 'sanctum');

    $order = Order::factory()->create();
    $response = $this->getJson('/api/orders/' . $order->id);
    $response->assertOk()->assertJsonFragment([
      'id' => $order->id,
      'order_number' => $order->order_number,
    ]);
  }

  public function test_can_create_order_with_customer_and_items_as_super_admin(): void
  {
    // Authenticate as super admin
    $superAdmin = User::factory()->create(['role' => 'super-admin']);
    $this->actingAs($superAdmin, 'sanctum');

    // Prepare customer data without saving to the database
    $customerData = Customer::factory()->make()->toArray();

    // Prepare services and item data
    $service1 = Service::factory()->create();
    $service2 = Service::factory()->create();

    $orderItems = [
      [
        'service_id' => $service1->id,
        'name' => 'Bed Sheet',
        'quantity' => 2,
      ],
      [
        'service_id' => $service2->id,
        'name' => 'Towel',
        'quantity' => 1,
      ],
    ];

    // Prepare order data
    $orderData = Order::factory()->make()->toArray();
    $payload = array_merge(
      $orderData,
      [
        'customer' => $customerData,
        'items' => $orderItems,
      ]
    );

    // Send POST request to create the order
    $response = $this->postJson('/api/orders', $payload);

    // Assert successful creation (201 status code)
    $response->assertCreated();

    $orderNumber = $response->json('data.order_number');

    // Assert customer was created in the database
    $this->assertDatabaseHas('customers', [
      'name' => $customerData['name'],
      'phone_number' => $customerData['phone_number'],
    ]);

    // Assert order was created in the database
    $this->assertDatabaseHas('orders', [
      'order_number' => $orderNumber,
    ]);

    // Assert each order item was created in the database
    foreach ($orderItems as $item) {
      $this->assertDatabaseHas('order_items', [
        'order_id' => $response->json('data.id'),
        'service_id' => $item['service_id'],
        'name' => $item['name'],
        'quantity' => $item['quantity'],
      ]);
    }
  }


  public function test_can_update_order_with_items_and_customer_as_super_admin(): void
  {
    $superAdmin = User::factory()->create(['role' => 'super-admin']);
    $this->actingAs($superAdmin, 'sanctum');

    $order = Order::factory()->create();
    $customer = $order->customer;

    $newCustomerData = [
      'name' => 'Updated Customer',
      'phone_number' => '081234567890',
      'address' => 'Updated Address',
    ];

    // Create a new service to update the order items
    $newService = Service::factory()->create();
    $updatedItems = [
      [
        'service_id' => $newService->id,
        'name'       => 'Updated Item',
        'quantity'   => 3,
      ],
    ];

    $payload = [
      'order_status' => 'completed',
      'pickup_date'  => now()->addDays(2)->format('Y-m-d'),
      'customer'     => $newCustomerData,
      'items'        => $updatedItems,
    ];

    $response = $this->putJson("/api/orders/{$order->id}", $payload);
    $response->assertOk();

    $this->assertDatabaseHas('orders', [
      'id' => $order->id,
      'order_status' => 'completed',
    ]);

    $this->assertDatabaseHas('customers', [
      'id' => $customer->id,
      'name' => 'Updated Customer',
      'phone_number' => '081234567890',
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
    $superAdmin = User::factory()->create(['role' => 'super-admin']);
    $this->actingAs($superAdmin, 'sanctum');

    $order = Order::factory()->create();

    $response = $this->deleteJson("/api/orders/{$order->id}");

    $response->assertOk();

    $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    $this->assertDatabaseMissing('order_items', ['order_id' => $order->id]);
  }
}
