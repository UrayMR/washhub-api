<?php

namespace Tests\Feature\Admin;

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

  public function test_can_index_their_order_and_cannot_index_other_user_order_as_admin(): void
  {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $this->actingAs($admin, 'sanctum');

    $ownOrders = Order::factory(3)->create(['user_id' => $admin->id]);

    $otherUser = User::factory()->create();
    $otherOrders = Order::factory(2)->create(['user_id' => $otherUser->id]);

    $response = $this->getJson('/api/orders');
    $response->assertOk();

    $data = $response->json('data');

    // Assert all own orders are present
    foreach ($ownOrders as $order) {
      $this->assertTrue(
        collect($data)->contains(fn($item) => $item['id'] === $order->id),
        "Expected order ID {$order->id} to be in response"
      );
    }

    // Assert other orders are not present
    foreach ($otherOrders as $order) {
      $this->assertFalse(
        collect($data)->contains(fn($item) => $item['id'] === $order->id),
        "Did not expect order ID {$order->id} in response"
      );
    }
  }

  public function test_can_show_their_order_and_cannot_show_other_user_order_as_admin()
  {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $this->actingAs($admin, 'sanctum');

    $ownOrder = Order::factory()->create(['user_id' => $admin->id]);

    $ownResponse = $this->getJson('/api/orders/' . $ownOrder->id);
    $ownResponse->assertOk()->assertJsonFragment([
      'id' => $ownOrder->id,
      'order_number' => $ownOrder->order_number,
    ]);

    $otherUser = User::factory()->create();
    $otherOrder = Order::factory()->create(['user_id' => $otherUser->id]);

    $otherResponse = $this->getJson('/api/orders/' . $otherOrder->id);
    $otherResponse->assertForbidden();
  }

  public function test_can_create_order_with_customer_and_items_as_admin(): void
  {
    // Authenticate as super admin
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $this->actingAs($admin, 'sanctum');

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


  public function test_can_update_their_order_with_items_and_customer_as_admin(): void
  {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $this->actingAs($admin, 'sanctum');

    $order = Order::factory()->create(['user_id' => $admin->id]);
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

  public function test_cannot_update_other_user_order_with_items_and_customer_as_admin(): void
  {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $this->actingAs($admin, 'sanctum');

    $otherUser = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $otherUser->id]);
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
    $response->assertForbidden();
  }


  public function test_can_delete_their_order_as_admin(): void
  {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $this->actingAs($admin, 'sanctum');

    $order = Order::factory()->create(['user_id' => $admin->id]);

    $response = $this->deleteJson("/api/orders/{$order->id}");

    $response->assertOk();

    $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    $this->assertDatabaseMissing('order_items', ['order_id' => $order->id]);
  }

  public function test_cannot_delete_other_user_order_as_admin(): void
  {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $this->actingAs($admin, 'sanctum');

    $otherUser = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->deleteJson("/api/orders/{$order->id}");

    $response->assertForbidden();
  }
}
