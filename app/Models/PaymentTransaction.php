<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'payment_method_id',
        'provider_code',
        'type',
        'status',
        'external_id',
        'redirect_url',
        'currency',
        'amount_gross',
        'request_payload_json',
        'response_payload_json',
        'paid_at',
    ];

    protected $casts = [
        'amount_gross' => 'decimal:2',
        'request_payload_json' => 'array',
        'response_payload_json' => 'array',
        'paid_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}
