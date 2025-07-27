<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
{
  use RefreshDatabase;

  protected User $superAdmin;

  protected function setUp(): void
  {
    parent::setUp();
    $this->superAdmin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
    $this->actingAs($this->superAdmin, 'sanctum');
  }

  public function test_can_index_transaction_as_super_admin(): void
  {
    Transaction::factory(3)->create();

    $response = $this->getJson('/api/transactions');

    $response->assertOk();
    $this->assertCount(3, $response->json('data'));
  }

  public function test_can_show_transaction_as_super_admin(): void
  {
    $transaction = Transaction::factory()->create();

    $response = $this->getJson("/api/transactions/{$transaction->id}");

    $response->assertOk()
      ->assertJsonFragment([
        'id' => $transaction->id,
      ]);
  }

  public function test_can_create_transaction_as_super_admin(): void
  {
    $order = Order::factory()->withItems(3)->create();

    $invoice = Invoice::factory()->create([
      'order_id' => $order->id,
      'amount' => $order->total_price,
    ]);

    $payload = [
      'invoice_id' => $invoice->id,
      'payment_method' => 'cash',
      'paid_at' => now()->toISOString(),
      'reference_number' => 'TXN-REF-001',
    ];

    $response = $this->postJson('/api/transactions', $payload);

    $response->assertCreated()
      ->assertJsonFragment([
        'paymentMethod' => 'cash',
        'paidAmount' => $invoice->amount,
      ]);

    $this->assertDatabaseHas('transactions', [
      'invoice_id' => $invoice->id,
      'payment_method' => 'cash',
      'paid_amount' => $invoice->amount,
    ]);
  }

  public function test_cannot_create_duplicate_transaction_for_same_invoice(): void
  {
    $transaction = Transaction::factory()->create();

    $payload = [
      'invoice_id' => $transaction->invoice_id,
      'payment_method' => 'cash',
      'paid_at' => now()->toISOString(),
    ];

    $response = $this->postJson('/api/transactions', $payload);

    $response->assertUnprocessable()
      ->assertJsonFragment([
        'message' => 'Transaction for this invoice already exists.',
      ]);
  }

  public function test_can_update_transaction_as_super_admin(): void
  {
    $transaction = Transaction::factory()->create([
      'payment_method' => 'cash',
    ]);

    $update = [
      'payment_method' => 'transfer',
      'paid_at' => now()->addDay()->toISOString(),
      'reference_number' => 'UPDATED-REF-123',
    ];

    $response = $this->putJson("/api/transactions/{$transaction->id}", $update);

    $response->assertOk()
      ->assertJsonFragment([
        'paymentMethod' => 'transfer',
        'referenceNumber' => 'UPDATED-REF-123',
      ]);

    $this->assertDatabaseHas('transactions', [
      'id' => $transaction->id,
      'payment_method' => 'transfer',
      'reference_number' => 'UPDATED-REF-123',
    ]);
  }

  public function test_can_delete_transaction_as_super_admin(): void
  {
    $transaction = Transaction::factory()->create();

    $response = $this->deleteJson("/api/transactions/{$transaction->id}");

    $response->assertOk()
      ->assertJsonFragment([
        'message' => 'Transaction deleted.',
      ]);

    $this->assertDatabaseMissing('transactions', [
      'id' => $transaction->id,
    ]);
  }
}
