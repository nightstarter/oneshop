<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAttributeValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'attribute_id',
        'value_text',
        'value_number',
        'value_boolean',
        'value_json',
        'value_unit',
    ];

    protected $casts = [
        'value_number' => 'decimal:4',
        'value_boolean' => 'boolean',
        'value_json' => 'array',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(ProductAttribute::class, 'attribute_id');
    }

    public function getTypedValueAttribute(): mixed
    {
        if ($this->value_json !== null) {
            return $this->value_json;
        }

        if ($this->value_boolean !== null) {
            return (bool) $this->value_boolean;
        }

        if ($this->value_number !== null) {
            return (float) $this->value_number;
        }

        return $this->value_text;
    }
}
