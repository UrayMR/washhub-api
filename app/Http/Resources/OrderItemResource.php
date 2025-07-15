<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
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
            'order' => new OrderResource($this->whenLoaded('order')),
            'service' => new ServiceResource($this->whenLoaded('service')),
            'name' => $this->name,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'created_at' => $this->created_at,
        ];
    }
}
