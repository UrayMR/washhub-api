<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'order' => new OrderResource($this->whenLoaded('order')),
            'amount' => $this->amount,
            'status' => $this->status,
            'issuedAt' => $this->issuedAt,
            'createdAt' => $this->created_at,
        ];
    }
}
