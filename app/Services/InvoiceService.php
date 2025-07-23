<?php

namespace App\Services;

use App\Models\Order;

class InvoiceService
{
    /**
     * Calculate the invoice amount from an order.
     */
    public function calculateAmount(Order $order): int
    {
        return $order->items->sum(function ($item) {
            return $item->quantity * $item->service->price;
        });
    }
}
