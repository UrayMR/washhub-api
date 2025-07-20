<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Transaction */
class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'transactionNumber' => $this->transaction_number,
            'invoice' => new InvoiceResource($this->whenLoaded('invoice')),
            'paymentMethod' => $this->payment_method,
            'paidAmount' => $this->paid_amount,
            'paidAt' => $this->paid_at,
            'referenceNumber' => $this->reference_number,
        ];
    }
}
