<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->superAdmin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $this->actingAs($this->superAdmin, 'sanctum');
    }

    public function test_can_index_user_as_super_admin()
    {
        $admins = User::factory(5)->create(['role' => User::ROLE_ADMIN]);

        $response = $this->getJson('/api/users');
        $response->assertOk();

        foreach ($admins as $admin) {
            $response->assertJsonFragment([
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => User::ROLE_ADMIN,
            ]);
        }
    }

    public function test_can_show_user_as_super_admin()
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->getJson("/api/users/{$admin->id}");
        $response->assertOk()
            ->assertJsonFragment([
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => User::ROLE_ADMIN,
            ]);
    }

    public function test_can_create_user_as_super_admin()
    {
        $payload = [
            'name' => 'Test User',
            'email' => 'adminbaru@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => User::ROLE_ADMIN,
        ];

        $response = $this->postJson('/api/users', $payload);

        $response->assertCreated()
            ->assertJson([
                'status' => true,
                'message' => 'User created.',
                'data' => [
                    'name' => $payload['name'],
                    'email' => $payload['email'],
                    'role' => $payload['role'],
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => $payload['email'],
            'role' => $payload['role'],
        ]);
    }

    public function test_can_update_and_delete_user_as_super_admin()
    {
        $targetUser = User::factory()->create(['role' => User::ROLE_ADMIN]);

        // UPDATE
        $updatePayload = ['name' => 'Updated Name'];

        $update = $this->putJson("/api/users/{$targetUser->id}", $updatePayload);
        $update->assertOk()
            ->assertJson([
                'status' => true,
                'message' => 'User updated.',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'name' => $updatePayload['name'],
        ]);

        // DELETE
        $delete = $this->deleteJson("/api/users/{$targetUser->id}");
        $delete->assertOk()
            ->assertJson([
                'status' => true,
                'message' => 'User deleted.',
            ]);

        $this->assertDatabaseMissing('users', [
            'id' => $targetUser->id,
        ]);
    }
}
