<?php

namespace Tests\Unit;

use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_number_is_auto_generated_on_create()
    {
        $invoice = Invoice::factory()->create();
        $this->assertNotEmpty($invoice->invoice_number);
        $this->assertMatchesRegularExpression('/^INV-\d{8}-\d{5}$/', $invoice->invoice_number);
    }
}
