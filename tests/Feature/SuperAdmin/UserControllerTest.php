<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_index_user_as_super_admin()
    {
        $superAdmin = User::factory()->create(['role' => 'super-admin']);
        $this->actingAs($superAdmin, 'sanctum');

        $adminDatas = User::factory(5)->create(['role' => 'admin']);

        $response = $this->getJson('/api/users');

        $response->assertOk();

        foreach ($adminDatas as $admin) {
            $response->assertJsonFragment([
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => 'admin',
            ]);
        }
    }

    public function test_can_show_user_as_super_admin()
    {
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

    public function test_can_create_user_as_super_admin()
    {
        $superAdmin = User::factory()->create(['role' => 'super-admin']);
        $this->actingAs($superAdmin, 'sanctum');

        $payload = [
            'name' => 'test',
            'email' => 'test@example.com',
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
                    'name' => 'test',
                    'email' => 'test@example.com',
                    'role' => 'admin',
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'adminbaru@example.com',
            'role' => 'admin',
        ]);
    }

    public function test_can_update_and_delete_user_as_super_admin()
    {
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
