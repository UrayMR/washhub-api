<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_fails_when_password_confirmation_is_missing()
    {
        $payload = [
            'name' => 'No Confirm',
            'email' => 'noconfirm@example.com',
            'password' => 'secret123',
            // 'password_confirmation' => ..., //  tidak dikirim
            'role' => 'admin',
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(422)
            ->assertJson([
                'status' => false,
                'message' => 'Validation failed.',
            ])
            ->assertJsonValidationErrors(['password']);
    }

    public function test_registration_fails_when_password_confirmation_does_not_match()
    {
        $payload = [
            'name' => 'Mismatch User',
            'email' => 'mismatch@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'wrong123', // beda
            'role' => 'admin',
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(422)
            ->assertJson([
                'status' => false,
                'message' => 'Validation failed.',
            ])
            ->assertJsonValidationErrors(['password']);
    }

    public function test_user_can_register_successfully()
    {
        $payload = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertCreated()
            ->assertJson([
                'status' => true,
                'message' => 'User registered successfully.',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);
    }

    public function test_user_can_login_successfully()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $payload = [
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/login', $payload);

        $response->assertOk()
            ->assertJson([
                'status' => true,
                'message' => 'Login successful.',
            ])
            ->assertJsonStructure([
                'data' => [
                    'access_token',
                    'user' => [
                        'id',
                        'name',
                        'email'
                    ]
                ]
            ]);
    }

    public function test_login_fails_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'wrong@example.com',
            'password' => Hash::make('password123'),
        ]);

        $payload = [
            'email' => 'wrong@example.com',
            'password' => 'wrongpassword',
        ];

        $response = $this->postJson('/api/login', $payload);

        $response->assertStatus(422)
            ->assertJson([
                'status' => false,
                'message' => 'Validation failed.',
            ])
            ->assertJsonValidationErrors(['email']);
    }
}
