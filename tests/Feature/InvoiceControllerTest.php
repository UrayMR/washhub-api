<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_invoices()
    {
        $response = $this->getJson('/api/invoices');
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_list_invoices()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
        Invoice::factory()->count(3)->create();
        $response = $this->getJson('/api/invoices');
        $response->assertOk()->assertJsonStructure(['data']);
    }

    public function test_user_can_create_invoice()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
        $invoiceData = Invoice::factory()->make()->toArray();
        $response = $this->postJson('/api/invoices', $invoiceData);
        $response->assertCreated();
        $this->assertDatabaseHas('invoices', ['invoice_number' => $invoiceData['invoice_number']]);
    }

    public function test_user_can_view_invoice()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
        $invoice = Invoice::factory()->create();
        $response = $this->getJson('/api/invoices/' . $invoice->id);
        $response->assertOk()->assertJsonStructure(['data']);
    }

    public function test_user_can_update_invoice()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
        $invoice = Invoice::factory()->create();
        $update = ['status' => 'paid'];
        $response = $this->putJson('/api/invoices/' . $invoice->id, $update);
        $response->assertOk();
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'status' => 'paid']);
    }

    public function test_user_can_delete_invoice()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
        $invoice = Invoice::factory()->create();
        $response = $this->deleteJson('/api/invoices/' . $invoice->id);
        $response->assertNoContent();
        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
    }

    public function test_policy_prevents_unauthorized_actions()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $invoice = Invoice::factory()->for($otherUser)->create();
        $this->actingAs($user, 'sanctum');
        $response = $this->deleteJson('/api/invoices/' . $invoice->id);
        $response->assertForbidden();
    }
}
