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
            'invoice' => $this->whenLoaded('invoice', function () {
                $order = $this->invoice->order;
                $customer = $order?->customer;
                return [
                    'id' => $this->invoice->id,
                    'invoiceNumber' => $this->invoice->invoice_number,
                    'amount' => number_format($this->invoice->amount, 2, '.', ''),
                    'status' => $this->invoice->status,
                    'pickupDate' => $order?->pickup_date,
                    'customerName' => $customer?->name,
                    'customerAddress' => $customer?->address,
                ];
            }),
            'paymentMethod' => $this->payment_method,
            'paidAmount' => number_format($this->paid_amount, 2, '.', ''),
            'paidAt' => $this->paid_at,
            'referenceNumber' => $this->reference_number,
        ];
    }
}
