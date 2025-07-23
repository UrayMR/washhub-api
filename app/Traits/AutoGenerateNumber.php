<?php

namespace App\Traits;

trait AutoGenerateNumber
{
    public static function bootAutoGenerateNumber(): void
    {
        static::creating(function ($model) {
            // Takes number prefix and field from model that has been defined
            $prefix = $model->number_prefix ?? 'ORD';
            $field  = $model->number_field  ?? 'order_number';

            $now = now();
            $date = $now->format('Ymd');
            $count = static::whereDate('created_at', $now->toDateString())->count() + 1;

            // Set increment digits or can be defined on models 
            $digits = $model->number_digits ?? 5;
            $increment = str_pad($count, $digits, '0', STR_PAD_LEFT);

            $model->{$field} = "{$prefix}-{$date}-{$increment}";
        });
    }
}
