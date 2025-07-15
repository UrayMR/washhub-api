<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'orderNumber' => $this->order_number,
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'user' => new UserResource($this->whenLoaded('user')),
            'orderStatus' => $this->order_status,
            'status' => $this->status,
            'notes' => $this->notes,
            'pickupDate' => $this->pickup_date,
            'createdAt' => $this->created_at,
        ];
    }
}
