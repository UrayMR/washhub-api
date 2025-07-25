<?php

namespace Tests\Feature\Admin;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceControllerTest extends TestCase
{
  use RefreshDatabase;

  public function test_can_index_service_as_admin()
  {
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin, 'sanctum');

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

  public function test_cannot_create_service_as_admin()
  {
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin, 'sanctum');

    $payload = [
      'name' => 'test',
      'description' => 'test123',
      'price' => '15000.00',
      'unit' => 'kg',
      'status' => 'active',
    ];

    $response = $this->postJson('/api/services', $payload);

    $response->assertForbidden();
  }

  public function test_cannot_update_and_delete_service_as_admin()
  {
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin, 'sanctum');

    $service = Service::factory()->create();

    // UPDATE
    $update = $this->putJson("/api/services/{$service->id}", [
      'name' => 'test',
      'price' => '18000.00',
    ]);

    $update->assertForbidden();

    // DELETE
    $delete = $this->deleteJson("/api/services/{$service->id}");

    $delete->assertForbidden();
  }
}
