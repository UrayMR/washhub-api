<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;


class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function super_admin_can_create_user()
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
            ]);
    }

    #[Test]
    public function admin_cannot_create_user()
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

    #[Test]
    public function admin_cannot_update_or_delete_user()
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        /** @var \App\Models\User $target */
        $target = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin, 'sanctum');

        // Update attempt
        $update = $this->putJson("/api/users/{$target->id}", [
            'name' => 'Edited',
            'email' => 'edited@example.com',
        ]);

        $update->assertForbidden();

        // Delete attempt
        $delete = $this->deleteJson("/api/users/{$target->id}");
        $delete->assertForbidden();
    }

    #[Test]
    public function super_admin_can_update_and_delete_user()
    {
        /** @var \App\Models\User $superAdmin */
        $superAdmin = User::factory()->create(['role' => 'super-admin']);

        /** @var \App\Models\User $target */
        $target = User::factory()->create(['role' => 'admin']);

        $this->actingAs($superAdmin, 'sanctum');

        $update = $this->putJson("/api/users/{$target->id}", [
            'name' => 'Updated Name',
        ]);

        $update->assertOk()
            ->assertJson([
                'status' => true,
                'message' => 'User updated.',
            ]);

        $delete = $this->deleteJson("/api/users/{$target->id}");
        $delete->assertOk()
            ->assertJson([
                'status' => true,
                'message' => 'User deleted.',
            ]);
    }
}
