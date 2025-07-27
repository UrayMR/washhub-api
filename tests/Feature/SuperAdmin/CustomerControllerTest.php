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

    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->superAdmin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $this->actingAs($this->superAdmin, 'sanctum');
    }

    public function test_can_index_customer_as_super_admin()
    {
        $customers = Customer::factory(3)->create();

        foreach ($customers as $customer) {
            Order::factory()->create([
                'customer_id' => $customer->id,
                'user_id' => User::factory()->create()->id,
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
        $customer = Customer::factory()->create();

        Order::factory()->create([
            'customer_id' => $customer->id,
            'user_id' => User::factory()->create()->id,
        ]);

        $response = $this->getJson("/api/customers/{$customer->id}");
        $response->assertOk()
            ->assertJsonFragment(['id' => $customer->id]);
    }

    public function test_cannot_create_customer_as_super_admin()
    {
        $payload = [
            'name' => 'Super Pelanggan',
            'phone_number' => '089812345678',
            'address' => 'Jl. Contoh',
        ];

        $response = $this->postJson('/api/customers', $payload);
        $response->assertStatus(501); // Not Implemented
    }

    public function test_can_update_and_delete_customer_as_super_admin()
    {
        $customer = Customer::factory()->create();

        // Update
        $updatePayload = [
            'name' => 'Diubah Super',
            'phone_number' => '0899000111',
            'address' => 'Jl. Diubah',
        ];

        $update = $this->putJson("/api/customers/{$customer->id}", $updatePayload);

        $update->assertOk()
            ->assertJsonFragment(['name' => $updatePayload['name']]);

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => $updatePayload['name'],
        ]);

        // Delete
        $delete = $this->deleteJson("/api/customers/{$customer->id}");

        $delete->assertOk();
        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }
}
