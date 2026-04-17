<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'customer_id',
        'user_id',
        'shipping_method_id',
        'shipping_code',
        'shipping_name',
        'shipping_price_net',
        'shipping_price_gross',
        'payment_method_id',
        'payment_code',
        'payment_name',
        'payment_price_net',
        'payment_price_gross',
        'pickup_point_id',
        'pickup_point_name',
        'pickup_point_address',
        'shipping_payload_json',
        'payment_payload_json',
        'status',
        'currency',
        'vat_rate',
        'price_net',
        'price_vat',
        'price_gross',
        'billing_address_json',
        'shipping_address_json',
        'note',
        'placed_at',
    ];

    protected $casts = [
        'vat_rate' => 'decimal:2',
        'shipping_price_net' => 'decimal:2',
        'shipping_price_gross' => 'decimal:2',
        'payment_price_net' => 'decimal:2',
        'payment_price_gross' => 'decimal:2',
        'price_net' => 'decimal:2',
        'price_vat' => 'decimal:2',
        'price_gross' => 'decimal:2',
        'billing_address_json' => 'array',
        'shipping_address_json' => 'array',
        'shipping_payload_json' => 'array',
        'payment_payload_json' => 'array',
        'placed_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }
}