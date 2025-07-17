<?php

namespace App\Traits;

use Number;

trait AutoGenerateNumber
{
    public static function bootAutoGenerateCode(): void
    {
        static::creating(function ($model) {
            // Takes prefix and column target from model variable definitions
            $prefix = $model->number_prefix ?? 'ORD';
            $field = $model->number_field ?? 'order_number';

            $date = now()->format('Ymd');
            $count = static::whereDate('created_at', today())->count() + 1;

            $increment_digit = 4;
            $increment_number = str_pad($count, $increment_digit, '0', STR_PAD_LEFT);

            $model->{$field} = "{$prefix}-{$date}-{$increment_number}";
        });
    }
}
