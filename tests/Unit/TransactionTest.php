<?php

namespace Tests\Unit;

use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_transaction_number_is_auto_generated_on_create()
    {
        $transaction = Transaction::factory()->create();
        $this->assertNotEmpty($transaction->transaction_number);
        $this->assertMatchesRegularExpression('/^TRX-\d{8}-\d{5}$/', $transaction->transaction_number);
    }
}
