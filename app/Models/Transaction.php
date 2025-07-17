<?php

namespace App\Models;

use App\Traits\AutoGenerateNumber;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory, AutoGenerateNumber;

    protected $fillable = [
        // Auto generated transaction number
        // 'transaction_number',
        'invoice_id',
        'payment_method',
        'paid_amount',
        'paid_at',
        'reference_number',
    ];

    protected $casts = [
        'paid_amount' => 'decimal:2',
        'paid_at' => 'datetime'
    ];

    protected string $code_prefix = 'TRX';
    protected string $code_field = 'transaction_number';

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
