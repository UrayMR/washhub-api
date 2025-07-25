<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_customer_as_super_admin()
    {
        /** @var \App\Models\User $superAdmin */
        $superAdmin = User::factory()->create(['role' => 'super-admin']);
        $this->actingAs($superAdmin, 'sanctum');

        $customers = Customer::factory(5)->create();

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

    public function test_index_customer_as_admin_returns_all_customers()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin, 'sanctum');

        $customers = Customer::factory(5)->create();

        $response = $this->getJson('/api/customers');
        $response->assertOk();

        foreach ($customers as $customer) {
            $response->assertJsonFragment([
                'id' => (string) $customer->id,
            ]);
        }
    }

    public function test_show_customer_as_super_admin()
    {
        /** @var \App\Models\User $superAdmin */
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

    public function test_show_customer_as_admin_handled_customer()
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin, 'sanctum');

        $customer = Customer::factory()->create();
        Order::factory()->create([
            'customer_id' => $customer->id,
            'user_id' => $admin->id,
        ]);

        $response = $this->getJson("/api/customers/{$customer->id}");
        $response->assertOk()->assertJsonFragment(['id' => $customer->id]);
    }

    public function test_show_customer_as_admin_not_handled_customer()
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin, 'sanctum');

        $customer = Customer::factory()->create();

        $response = $this->getJson("/api/customers/{$customer->id}");
        $response->assertOk()->assertJsonFragment(['id' => $customer->id]);
    }

    public function test_super_admin_can_create_customer()
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
        $response->assertCreated()->assertJsonFragment(['name' => 'Super Pelanggan']);
        $this->assertDatabaseHas('customers', ['name' => 'Super Pelanggan']);
    }

    public function test_admin_can_create_customer()
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin, 'sanctum');

        $payload = [
            'name' => 'Pelanggan Admin',
            'phone_number' => '081234567899',
            'address' => 'Jl. Admin',
        ];

        $response = $this->postJson('/api/customers', $payload);
        $response->assertCreated()->assertJsonFragment(['name' => 'Pelanggan Admin']);
        $this->assertDatabaseHas('customers', ['name' => 'Pelanggan Admin']);
    }

    public function test_super_admin_can_update_and_delete_customer()
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
        $delete->assertOk()->assertJsonFragment(['id' => $customer->id]);
        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }

    public function test_admin_can_update_and_delete_only_handled_customer()
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin, 'sanctum');

        $customer = Customer::factory()->create();
        Order::factory()->create(['customer_id' => $customer->id, 'user_id' => $admin->id]);

        $update = $this->putJson("/api/customers/{$customer->id}", [
            'name' => 'Edit Admin',
        ]);

        $update->assertOk()->assertJsonFragment(['name' => 'Edit Admin']);

        $delete = $this->deleteJson("/api/customers/{$customer->id}");
        $delete->assertOk();
        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }

    public function test_admin_cannot_update_customer_of_another_admin()
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin, 'sanctum');

        $otherAdmin = User::factory()->create(['role' => 'admin']);
        $customer = Customer::factory()->create();
        Order::factory()->create(['customer_id' => $customer->id, 'user_id' => $otherAdmin->id]);

        $response = $this->putJson("/api/customers/{$customer->id}", ['name' => 'Gagal']);
        $response->assertForbidden()->assertJson(['status' => false, 'message' => 'Unauthorized.']);
    }

    public function test_admin_cannot_delete_customer_of_another_admin()
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin, 'sanctum');

        $otherAdmin = User::factory()->create(['role' => 'admin']);
        $customer = Customer::factory()->create();
        Order::factory()->create(['customer_id' => $customer->id, 'user_id' => $otherAdmin->id]);

        $response = $this->deleteJson("/api/customers/{$customer->id}");
        $response->assertForbidden()->assertJson(['status' => false, 'message' => 'Unauthorized.']);
    }
}
