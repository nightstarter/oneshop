<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'sku',
        'product_name',
        'quantity',
        'unit_price_net',
        'unit_vat_amount',
        'unit_price_gross',
        'total_price_net',
        'total_vat_amount',
        'total_price_gross',
    ];

    protected $casts = [
        'unit_price_net' => 'decimal:2',
        'unit_vat_amount' => 'decimal:2',
        'unit_price_gross' => 'decimal:2',
        'total_price_net' => 'decimal:2',
        'total_vat_amount' => 'decimal:2',
        'total_price_gross' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}