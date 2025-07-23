<?php

namespace App\Models;

use App\Traits\AutoGenerateNumber;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $transaction_number
 * @property int $invoice_id
 * @property string $payment_method
 * @property numeric $paid_amount
 * @property \Illuminate\Support\Carbon|null $paid_at
 * @property string|null $reference_number
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Invoice $invoice
 * @method static \Database\Factories\TransactionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction wherePaidAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction wherePaidAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereReferenceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereTransactionNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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

    protected string $number_prefix = 'TRX';
    protected string $number_field = 'transaction_number';

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
