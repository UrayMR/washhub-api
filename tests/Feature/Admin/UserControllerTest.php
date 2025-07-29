<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $this->actingAs($this->admin, 'sanctum');
    }

    public function test_cannot_index_user_as_admin()
    {
        User::factory(5)->create(['role' => User::ROLE_ADMIN]);

        $response = $this->getJson('/api/users');

        $response->assertForbidden();
    }

    public function test_cannot_show_user_as_admin()
    {
        $otherAdmin = User::factory()->create(['role' => 'admin']);

        $response = $this->getJson("/api/users/{$otherAdmin->id}");

        $response->assertForbidden();
    }

    public function test_cannot_create_user_as_admin()
    {
        $payload = [
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => User::ROLE_ADMIN,
        ];

        $response = $this->postJson('/api/users', $payload);

        $response->assertForbidden();
    }

    public function test_cannot_update_and_delete_user_as_admin()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $target = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin, 'sanctum');

        // Update
        $update = $this->putJson("/api/users/{$target->id}", [
            'name' => 'Updated Name',
        ]);

        $update->assertForbidden();

        // Delete
        $delete = $this->deleteJson("/api/users/{$target->id}");

        $delete->assertForbidden();
    }
}
