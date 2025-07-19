<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'order_number' => $this->order_number,
            'order_status' => $this->order_status,
            'notes'        => $this->notes,
            'pickup_date'  => $this->pickup_date?->format('Y-m-d'),
            'total_price'  => number_format($this->total_price, 2, '.', ''),

            // Nested resources
            'customer'     => new CustomerResource($this->whenLoaded('customer')),
            'items'        => OrderItemResource::collection($this->whenLoaded('items')),

            'created_at'   => $this->created_at?->toDateTimeString(),
            'updated_at'   => $this->updated_at?->toDateTimeString(),
        ];
    }
}
