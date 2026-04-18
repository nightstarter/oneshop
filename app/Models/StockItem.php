<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'product_type',
        'quantity',
        'purchase_price',
        'sale_price',
        'ean',
        'active',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'purchase_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'active' => 'boolean',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function kitComponents(): HasMany
    {
        return $this->hasMany(StockItemComponent::class)
            ->with('componentStockItem')
            ->orderBy('id');
    }

    public function componentItems(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'stock_item_components',
            'stock_item_id',
            'component_stock_item_id'
        )->withPivot('quantity')->withTimestamps();
    }

    public function usedInItems(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'stock_item_components',
            'component_stock_item_id',
            'stock_item_id'
        )->withPivot('quantity')->withTimestamps();
    }

    public function hasStock(int $requiredQty = 1): bool
    {
        return $this->active && $this->availableQuantityForSale() >= max(1, $requiredQty);
    }

    public function isKit(): bool
    {
        return $this->product_type === 'battery_kit';
    }

    public function availableQuantityForSale(): int
    {
        if (!$this->isKit()) {
            return (int) $this->quantity;
        }

        $components = $this->kitComponents;
        if ($components->isEmpty()) {
            return 0;
        }

        $maxByComponents = $components
            ->filter(fn (StockItemComponent $item) => $item->quantity > 0 && $item->componentStockItem !== null)
            ->map(function (StockItemComponent $item): int {
                $available = (int) ($item->componentStockItem->quantity ?? 0);

                return (int) floor($available / $item->quantity);
            })
            ->min();

        if ($maxByComponents === null) {
            return 0;
        }

        return min((int) $this->quantity, (int) $maxByComponents);
    }
}
