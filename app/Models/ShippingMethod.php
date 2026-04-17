<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ShippingMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'provider_code',
        'type',
        'is_active',
        'price_net',
        'price_gross',
        'sort_order',
        'payload_json',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price_net' => 'decimal:2',
        'price_gross' => 'decimal:2',
        'payload_json' => 'array',
    ];

    public function paymentMethods(): BelongsToMany
    {
        return $this->belongsToMany(PaymentMethod::class, 'shipping_payment_method')->withTimestamps();
    }
}
