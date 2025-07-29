<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceControllerTest extends TestCase
{
  use RefreshDatabase;

  protected User $superAdmin;

  protected function setUp(): void
  {
    parent::setUp();
    $this->superAdmin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
    $this->actingAs($this->superAdmin, 'sanctum');
  }

  public function test_can_index_invoices_as_super_admin(): void
  {
    $invoices = Invoice::factory()->count(3)->create();

    $response = $this->getJson('/api/invoices');
    $response->assertOk();

    foreach ($invoices as $invoice) {
      $response->assertJsonFragment([
        'id' => $invoice->id,
      ]);
    }
  }

  public function test_can_show_invoices_as_super_admin(): void
  {
    $invoice = Invoice::factory()->create();

    $response = $this->getJson("/api/invoices/{$invoice->id}");
    $response->assertOk()
      ->assertJsonFragment([
        'id' => $invoice->id,
      ]);
  }

  public function test_can_create_invoice_with_order_as_super_admin(): void
  {
    $order = Order::factory()->withItems(3)->create();

    $invoiceData = [
      'order_id' => $order->id,
      'issued_at' => now()->toISOString(),
      'status' => 'unpaid',
    ];

    $response = $this->postJson('/api/invoices', $invoiceData);
    $response->assertCreated();

    $this->assertDatabaseHas('invoices', [
      'order_id' => $order->id,
    ]);
  }

  public function test_can_update_invoice_as_super_admin(): void
  {
    $invoice = Invoice::factory()->create();

    $response = $this->putJson("/api/invoices/{$invoice->id}", [
      'status' => 'paid',
    ]);

    $response->assertOk();

    $this->assertDatabaseHas('invoices', [
      'id' => $invoice->id,
      'status' => 'paid',
    ]);
  }

  public function test_can_delete_invoice_as_super_admin(): void
  {
    $invoice = Invoice::factory()->create();

    $response = $this->deleteJson("/api/invoices/{$invoice->id}");
    $response->assertOk();

    $this->assertDatabaseMissing('invoices', [
      'id' => $invoice->id,
    ]);
  }
}
