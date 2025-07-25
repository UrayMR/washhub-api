<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_index_customer_as_super_admin()
{
    $superAdmin = User::factory()->create(['role' => 'super-admin']);
    $this->actingAs($superAdmin, 'sanctum');

    $customers = Customer::factory()->count(3)->make();

    foreach ($customers as $customer) {
        $customer->save();

        Order::factory()->create([
            'user_id' => User::factory()->create()->id,
            'customer_id' => $customer->id,
        ]);
    }

    $response = $this->getJson('/api/customers');
    $response->assertOk();

    foreach ($customers as $customer) {
        $response->assertJsonFragment(['id' => $customer->id]);
    }
}


    public function test_can_show_customer_as_super_admin()
    {
        $superAdmin = User::factory()->create(['role' => 'super-admin']);
        $this->actingAs($superAdmin, 'sanctum');

        $customer = Customer::factory()->create();
        Order::factory()->create([
            'customer_id' => $customer->id,
            'user_id' => User::factory()->create()->id,
        ]);

        $response = $this->getJson("/api/customers/{$customer->id}");
        $response->assertOk()->assertJsonFragment(['id' => $customer->id]);
    }
    
    public function test_cannot_create_customer_as_super_admin()
    {
        /** @var \App\Models\User $superAdmin */
        $superAdmin = User::factory()->create(['role' => 'super-admin']);
        $this->actingAs($superAdmin, 'sanctum');

        $payload = [
            'name' => 'Super Pelanggan',
            'phone_number' => '089812345678',
            'address' => 'Jl. Contoh',
        ];

        $response = $this->postJson('/api/customers', $payload);
        $response->assertStatus(501);
    }

    public function test_can_update_and_delete_customer_as_super_admin()
    {
        /** @var \App\Models\User $superAdmin */
        $superAdmin = User::factory()->create(['role' => 'super-admin']);
        $this->actingAs($superAdmin, 'sanctum');

        $customer = Customer::factory()->create();

        $update = $this->putJson("/api/customers/{$customer->id}", [
            'name' => 'Diubah Super',
            'phone_number' => '0899000111',
            'address' => 'Jl. Diubah',
        ]);

        $update->assertOk()->assertJsonFragment(['name' => 'Diubah Super']);
        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'name' => 'Diubah Super']);

        $delete = $this->deleteJson("/api/customers/{$customer->id}");
        $delete->assertOk();
        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }
}
