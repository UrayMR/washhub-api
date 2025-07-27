<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->superAdmin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $this->actingAs($this->superAdmin, 'sanctum');
    }

    public function test_can_index_service_as_super_admin()
    {
        $services = Service::factory(3)->create();

        $response = $this->getJson('/api/services');
        $response->assertOk();

        foreach ($services as $service) {
            $response->assertJsonFragment(['id' => $service->id]);
        }
    }

    public function test_can_show_service_as_super_admin()
    {
        $service = Service::factory()->create();

        $response = $this->getJson("/api/services/{$service->id}");
        $response->assertOk()
            ->assertJsonFragment([
                'id' => $service->id,
                'name' => $service->name,
                'description' => $service->description,
                'price' => $service->price,
                'unit' => $service->unit,
                'status' => $service->status,
            ]);
    }

    public function test_can_create_service_as_super_admin()
    {
        $payload = [
            'name' => 'test',
            'description' => 'test123',
            'price' => '15000.00',
            'unit' => 'kg',
            'status' => 'active',
        ];

        $response = $this->postJson('/api/services', $payload);
        $response->assertCreated()
            ->assertJsonFragment([
                'name' => $payload['name'],
                'description' => $payload['description'],
                'price' => $payload['price'],
                'unit' => $payload['unit'],
                'status' => $payload['status'],
            ]);

        $this->assertDatabaseHas('services', [
            'name' => $payload['name'],
        ]);
    }

    public function test_can_update_and_delete_service_as_super_admin()
    {
        $service = Service::factory()->create();

        // UPDATE
        $updatePayload = [
            'name' => 'test',
            'price' => '18000.00',
        ];

        $update = $this->putJson("/api/services/{$service->id}", $updatePayload);
        $update->assertOk()
            ->assertJson([
                'status' => true,
                'message' => 'Service updated.',
            ]);

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'name' => $updatePayload['name'],
            'price' => $updatePayload['price'],
        ]);

        // DELETE
        $delete = $this->deleteJson("/api/services/{$service->id}");
        $delete->assertOk()
            ->assertJson([
                'status' => true,
                'message' => 'Service deleted.',
                'data' => null,
            ]);

        $this->assertDatabaseMissing('services', [
            'id' => $service->id,
        ]);
    }
}
