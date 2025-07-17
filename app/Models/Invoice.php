<?php

namespace App\Models;

use App\Traits\AutoGenerateNumber;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory, AutoGenerateNumber;

    protected $fillable = [
        // Auto generated Invoice Number
        // 'invoice_number',
        'order_id',
        'amount',
        'issued_at',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'issued_at' => 'date'
    ];

    protected string $code_prefix = 'INV';
    protected string $code_field = 'invoice_number';

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class);
    }
}
