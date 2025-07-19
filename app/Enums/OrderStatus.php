<?php

namespace App\Enums;

enum OrderStatus: string
{
    case pending = 'pending';
    case processing = 'processing';
    case completed = 'completed';
    case delivering = 'delivering';
    case delivered = 'delivered';
    case cancelled = 'cancelled';
    case picked = 'picked';
}
