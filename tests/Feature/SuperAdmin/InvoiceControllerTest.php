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

  public function test_can_index_invoice_as_super_admin()
  {
    $superAdmin = User::factory()->create(['role' => 'super-admin']);
    $this->actingAs($superAdmin, 'sanctum');

    $invoices = Invoice::factory(3)->create();
    $response = $this->getJson('/api/invoices');

    $response->assertOk();
    foreach ($invoices as $invoice) {
      $response->assertJsonFragment([
        'id' => $invoice->id,
      ]);
    }
  }

  public function test_can_show_invoice_as_super_admin()
  {
    $superAdmin = User::factory()->create(['role' => 'super-admin']);
    $this->actingAs($superAdmin, 'sanctum');

    $invoice = Invoice::factory()->create();
    $response = $this->getJson('/api/invoices/' . $invoice->id);
    $response->assertOk()->assertJsonFragment([
      'id' => $invoice->id,
    ]);
  }

  public function test_can_create_invoice_as_super_admin()
  {
    $superAdmin = User::factory()->create(['role' => 'super-admin']);
    $this->actingAs($superAdmin, 'sanctum');

    // Create order for invoice
    $order = Order::factory()->create();
    $invoiceData = Invoice::factory()->make(['order_id' => $order->id])->toArray();
    // Remove auto fields if any
    unset($invoiceData['id']);

    $response = $this->postJson('/api/invoices', $invoiceData);
    $response->assertCreated();

    $this->assertDatabaseHas('invoices', [
      'order_id' => $order->id,
      'amount' => $invoiceData['amount'],
    ]);
  }

  public function test_can_update_invoice_as_super_admin()
  {
    $superAdmin = User::factory()->create(['role' => 'super-admin']);
    $this->actingAs($superAdmin, 'sanctum');

    $invoice = Invoice::factory()->create();
    $update = [
      'status' => 'paid',
    ];
    $response = $this->putJson('/api/invoices/' . $invoice->id, $update);
    $response->assertOk();
    $this->assertDatabaseHas('invoices', [
      'id' => $invoice->id,
      'status' => 'paid',
    ]);
  }

  public function test_can_delete_invoice_as_super_admin()
  {
    $superAdmin = User::factory()->create(['role' => 'super-admin']);
    $this->actingAs($superAdmin, 'sanctum');

    $invoice = Invoice::factory()->create();
    $response = $this->deleteJson('/api/invoices/' . $invoice->id);
    $response->assertOk();
    $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
  }

  public function test_policy_prevents_unauthorized_invoice_actions()
  {
    $superAdmin = User::factory()->create(['role' => 'super-admin']);
    $otherUser = User::factory()->create();
    $invoice = Invoice::factory()->for($otherUser)->create();
    $this->actingAs($superAdmin, 'sanctum');
    // Simulate forbidden by policy (if any logic applies)
    // For demonstration, try to delete as a different user
    $this->actingAs($otherUser, 'sanctum');
    $response = $this->deleteJson('/api/invoices/' . $invoice->id);
    $response->assertForbidden();
  }
}
