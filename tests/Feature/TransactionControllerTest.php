<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_transactions()
    {
        $response = $this->getJson('/api/transactions');
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_list_transactions()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
        Transaction::factory()->count(3)->create();
        $response = $this->getJson('/api/transactions');
        $response->assertOk()->assertJsonStructure(['data']);
    }

    public function test_user_can_create_transaction()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
        $transactionData = Transaction::factory()->make()->toArray();
        $response = $this->postJson('/api/transactions', $transactionData);
        $response->assertCreated();
        $this->assertDatabaseHas('transactions', ['transaction_number' => $transactionData['transaction_number']]);
    }

    public function test_user_can_view_transaction()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
        $transaction = Transaction::factory()->create();
        $response = $this->getJson('/api/transactions/' . $transaction->id);
        $response->assertOk()->assertJsonStructure(['data']);
    }

    public function test_user_can_update_transaction()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
        $transaction = Transaction::factory()->create();
        $update = ['status' => 'completed'];
        $response = $this->putJson('/api/transactions/' . $transaction->id, $update);
        $response->assertOk();
        $this->assertDatabaseHas('transactions', ['id' => $transaction->id, 'status' => 'completed']);
    }

    public function test_user_can_delete_transaction()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
        $transaction = Transaction::factory()->create();
        $response = $this->deleteJson('/api/transactions/' . $transaction->id);
        $response->assertNoContent();
        $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
    }

    public function test_policy_prevents_unauthorized_actions()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $transaction = Transaction::factory()->for($otherUser)->create();
        $this->actingAs($user, 'sanctum');
        $response = $this->deleteJson('/api/transactions/' . $transaction->id);
        $response->assertForbidden();
    }
}
