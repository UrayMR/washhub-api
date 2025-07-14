<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_user_as_super_admin()
    {
        /** @var \App\Models\User $superAdmin */
        $superAdmin = User::factory()->create(['role' => 'super-admin']);
        $this->actingAs($superAdmin, 'sanctum');

        $adminDatas = User::factory(5)->create(['role' => 'admin']);

        $response = $this->getJson('/api/users');

        $response->assertSuccessful();

        foreach ($adminDatas as $admin) {
            $response->assertJsonFragment([
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => 'admin',
            ]);
        }
    }

    public function test_index_user_as_admin_should_be_forbidden()
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin, 'sanctum');

        $response = $this->getJson('/api/users');

        $response->assertForbidden()
            ->assertJson([
                'status' => false,
                'message' => 'Unauthorized.',
            ]);
    }

    public function test_show_user_as_super_admin()
    {
        /** @var \App\Models\User $superAdmin */
        $superAdmin = User::factory()->create(['role' => 'super-admin']);
        $this->actingAs($superAdmin, 'sanctum');

        $adminData = User::factory()->create(['role' => 'admin']);

        $response = $this->getJson("/api/users/{$adminData->id}");

        $response->assertSuccessful()
            ->assertJsonFragment([
                'id' => $adminData->id,
                'name' => $adminData->name,
                'email' => $adminData->email,
                'role' => 'admin',
            ]);
    }

    public function test_show_user_as_admin_should_be_forbidden()
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin, 'sanctum');

        $target = User::factory()->create(['role' => 'admin']);

        $response = $this->getJson("/api/users/{$target->id}");

        $response->assertForbidden()
            ->assertJson([
                'status' => false,
                'message' => 'Unauthorized.',
            ]);
    }

    public function test_super_admin_can_create_user()
    {
        /** @var \App\Models\User $superAdmin */
        $superAdmin = User::factory()->create(['role' => 'super-admin']);
        $this->actingAs($superAdmin, 'sanctum');

        $payload = [
            'name' => 'Admin Baru',
            'email' => 'adminbaru@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ];

        $response = $this->postJson('/api/users', $payload);

        $response->assertCreated()
            ->assertJson([
                'status' => true,
                'message' => 'User created.',
                'data' => [
                    'name' => 'Admin Baru',
                    'email' => 'adminbaru@example.com',
                    'role' => 'admin',
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'adminbaru@example.com',
            'role' => 'admin',
        ]);
    }

    public function test_admin_cannot_create_user()
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin, 'sanctum');

        $payload = [
            'name' => 'Dilarang',
            'email' => 'nope@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ];

        $response = $this->postJson('/api/users', $payload);

        $response->assertForbidden()
            ->assertJson([
                'status' => false,
                'message' => 'Unauthorized.',
            ]);
    }

    public function test_admin_cannot_update_or_delete_user()
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create(['role' => 'admin']);
        $target = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin, 'sanctum');

        // Update
        $update = $this->putJson("/api/users/{$target->id}", [
            'name' => 'Tidak Boleh',
        ]);

        $update->assertForbidden()
            ->assertJson([
                'status' => false,
                'message' => 'Unauthorized.',
            ]);

        // Delete
        $delete = $this->deleteJson("/api/users/{$target->id}");

        $delete->assertForbidden()
            ->assertJson([
                'status' => false,
                'message' => 'Unauthorized.',
            ]);
    }

    public function test_super_admin_can_update_and_delete_user()
    {
        /** @var \App\Models\User $superAdmin */
        $superAdmin = User::factory()->create(['role' => 'super-admin']);
        $target = User::factory()->create(['role' => 'admin']);

        $this->actingAs($superAdmin, 'sanctum');

        // Update
        $update = $this->putJson("/api/users/{$target->id}", [
            'name' => 'Updated Name',
        ]);

        $update->assertOk()
            ->assertJson([
                'status' => true,
                'message' => 'User updated.',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'name' => 'Updated Name',
        ]);

        // Delete
        $delete = $this->deleteJson("/api/users/{$target->id}");

        $delete->assertOk()
            ->assertJson([
                'status' => true,
                'message' => 'User deleted.',
            ]);

        $this->assertDatabaseMissing('users', [
            'id' => $target->id,
        ]);
    }
}
