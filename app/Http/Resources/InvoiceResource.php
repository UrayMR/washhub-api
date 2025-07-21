<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Invoice */
class InvoiceResource extends JsonResource
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
            'invoiceNumber' => $this->invoice_number,
            'order' => $this->whenLoaded('order', function () {
                $customer = $this->order->customer;
                return [
                    'id' => $this->order->id,
                    'orderNumber' => $this->order->order_number,
                    'customerName' => $customer?->name,
                    'customerAddress' => $customer?->address,
                    'pickupDate' => $this->order->pickup_date,
                ];
            }),
            'amount' => number_format($this->amount, 2, '.', ''),,
            'status' => $this->status,
            'issuedAt' => $this->issued_at,
        ];
    }
}
