<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_index_service_as_super_admin()
    {
        $superAdmin = User::factory()->create(['role' => 'super-admin']);
        $this->actingAs($superAdmin, 'sanctum');

        $serviceDatas = Service::factory(3)->create();

        $response = $this->getJson('/api/services');

        $response->assertOk();

        foreach ($serviceDatas as $service) {
            $response->assertJsonFragment([
                'id' => $service->id,
                'name' => $service->name,
                'description' => $service->description,
                'price' => $service->price,
                'unit' => $service->unit,
                'status' => $service->status,
            ]);
        }
    }

    public function test_can_show_service_as_admin()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin, 'sanctum');

        $service = Service::factory()->create();

        $response = $this->getJson('/api/services/' . $service->id);

        $response->assertOk()->assertJsonFragment([
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
        $superAdmin = User::factory()->create(['role' => 'super-admin']);
        $this->actingAs($superAdmin, 'sanctum');

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
                'name' => 'test',
                'description' => 'test123',
                'price' => '15000.00',
                'unit' => 'kg',
                'status' => 'active',
            ]);

        $this->assertDatabaseHas('services', [
            'name' => 'test',
        ]);
    }

    public function test_can_update_and_delete_service_as_super_admin()
    {
        $superAdmin = User::factory()->create(['role' => 'super-admin']);
        $this->actingAs($superAdmin, 'sanctum');

        $service = Service::factory()->create();
        
        // UPDATE
        $update = $this->putJson("/api/services/{$service->id}", [
            'name' => 'test',
            'price' => '18000.00',
        ]);

        $update->assertOk()
            ->assertJson([
                'status' => true,
                'message' => 'Service updated.',
            ]);

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'name' => 'test',
            'price' => '18000.00',
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
