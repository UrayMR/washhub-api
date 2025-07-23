<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_orders()
    {
        $response = $this->getJson('/api/orders');
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_list_orders()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
        Order::factory()->count(3)->create();
        $response = $this->getJson('/api/orders');
        $response->assertOk()->assertJsonStructure(['data']);
    }

    public function test_user_can_create_order()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
        $orderData = Order::factory()->make()->toArray();
        $response = $this->postJson('/api/orders', $orderData);
        $response->assertCreated();
        $this->assertDatabaseHas('orders', ['order_number' => $orderData['order_number']]);
    }

    public function test_user_can_view_order()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
        $order = Order::factory()->create();
        $response = $this->getJson('/api/orders/' . $order->id);
        $response->assertOk()->assertJsonStructure(['data']);
    }

    public function test_user_can_update_order()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
        $order = Order::factory()->create();
        $update = ['status' => 'completed'];
        $response = $this->putJson('/api/orders/' . $order->id, $update);
        $response->assertOk();
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'completed']);
    }

    public function test_user_can_delete_order()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
        $order = Order::factory()->create();
        $response = $this->deleteJson('/api/orders/' . $order->id);
        $response->assertNoContent();
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }

    public function test_policy_prevents_unauthorized_actions()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $order = Order::factory()->for($otherUser)->create();
        $this->actingAs($user, 'sanctum');
        $response = $this->deleteJson('/api/orders/' . $order->id);
        $response->assertForbidden();
    }
}
