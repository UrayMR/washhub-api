<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_service_as_super_admin()
    {
        /** @var \App\Models\User $superAdmin */
        $superAdmin = User::factory()->create(['role' => 'super-admin']);
        $this->actingAs($superAdmin, 'sanctum');

        $serviceDatas = Service::factory(5)->create();

        $response = $this->getJson('/api/services');

        $response->assertSuccessful();

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

    public function test_show_service_as_admin()
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin, 'sanctum');

        $service = Service::factory()->create();

        $response = $this->getJson('/api/services/' . $service->id);

        $response->assertSuccessful()->assertJsonFragment([
            'id' => $service->id,
            'name' => $service->name,
            'description' => $service->description,
            'price' => $service->price,
            'unit' => $service->unit,
            'status' => $service->status,
        ]);
    }

    public function test_super_admin_can_create_service()
    {
        /** @var \App\Models\User $superAdmin */
        $superAdmin = User::factory()->create(['role' => 'super-admin']);
        $this->actingAs($superAdmin, 'sanctum');

        $payload = [
            'name' => 'Cuci Kering',
            'description' => 'Layanan laundry kering',
            'price' => '15000.00',
            'unit' => 'kg',
            'status' => 'active',
        ];

        $response = $this->postJson('/api/services', $payload);

        $response->assertCreated()
            ->assertJsonFragment([
                'name' => 'Cuci Kering',
                'price' => '15000.00',
                'unit' => 'kg',
                'status' => 'active',
            ]);

        $this->assertDatabaseHas('services', [
            'name' => 'Cuci Kering',
        ]);
    }

    public function test_admin_cannot_create_service()
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin, 'sanctum');

        $payload = [
            'name' => 'Tidak Boleh',
            'description' => 'Coba-coba',
            'price' => '20000.00',
            'unit' => 'pcs',
            'status' => 'inactive',
        ];

        $response = $this->postJson('/api/services', $payload);

        $response->assertForbidden()
            ->assertJson([
                'status' => false,
                'message' => 'Unauthorized.',
            ]);
    }

    public function test_admin_cannot_update_or_delete_service()
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create(['role' => 'admin']);
        $service = Service::factory()->create();

        $this->actingAs($admin, 'sanctum');

        $update = $this->putJson("/api/services/{$service->id}", [
            'name' => 'Gagal Update',
        ]);

        $update->assertForbidden();

        $delete = $this->deleteJson("/api/services/{$service->id}");
        $delete->assertForbidden();
    }

    public function test_super_admin_can_update_and_delete_service()
    {
        /** @var \App\Models\User $superAdmin */
        $superAdmin = User::factory()->create(['role' => 'super-admin']);
        $service = Service::factory()->create();

        $this->actingAs($superAdmin, 'sanctum');

        // UPDATE
        $update = $this->putJson("/api/services/{$service->id}", [
            'name' => 'Cuci Basah',
            'price' => '18000.00',
        ]);

        $update->assertOk()
            ->assertJson([
                'status' => true,
                'message' => 'Service updated.',
            ]);

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'name' => 'Cuci Basah',
            'price' => '18000.00',
        ]);

        // DELETE
        $delete = $this->deleteJson("/api/services/{$service->id}");

        $delete->assertOk()
            ->assertJson([
                'status' => true,
                'message' => 'Service deleted.',
            ]);

        $this->assertDatabaseMissing('services', [
            'id' => $service->id,
        ]);
    }
}
