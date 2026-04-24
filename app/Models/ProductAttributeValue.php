<?php

namespace App\Models;

use Illuminate\Support\Arr;
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

    public function getDisplayValueAttribute(): string
    {
        if ($this->value_json !== null) {
            $values = Arr::flatten($this->value_json);
            $values = array_values(array_filter(array_map(
                static fn (mixed $value): string => trim((string) $value),
                $values,
            ), static fn (string $value): bool => $value !== ''));

            return implode(', ', $values);
        }

        if ($this->value_boolean !== null) {
            return $this->value_boolean ? (string) __('shop.yes') : (string) __('shop.no');
        }

        if ($this->value_number !== null) {
            $formatted = rtrim(rtrim(number_format((float) $this->value_number, 4, '.', ''), '0'), '.');

            return $formatted . $this->displayUnit();
        }

        $value = trim((string) $this->value_text);

        return $value === '' ? '' : $value . $this->displayUnit();
    }

    private function displayUnit(): string
    {
        $unit = trim((string) ($this->value_unit ?: $this->attribute?->unit ?: ''));

        return $unit === '' ? '' : ' ' . $unit;
    }
}
