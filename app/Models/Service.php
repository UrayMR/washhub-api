<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'unit',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];


    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
