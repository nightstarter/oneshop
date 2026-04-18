<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class Product extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = [
        'sku',
        'name',
        'slug',
        'description',
        'stock_item_id',
        'price',
        'active',
        'visibility',
        'base_price_net',
        'stock_qty',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'active' => 'boolean',
        'base_price_net' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function searchableAs(): string
    {
        return 'products';
    }

    public function shouldBeSearchable(): bool
    {
        return $this->isActiveForSale();
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => (string) $this->getScoutKey(),
            'sku' => $this->sku,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description ?? '',
            'price' => (float) ($this->price ?? $this->base_price_net),
            'base_price_net' => (float) $this->base_price_net,
            'is_active' => $this->isActiveForSale(),
            'categories' => $this->categories->pluck('name')->values()->all(),
            'created_at_timestamp' => $this->created_at?->timestamp ?? now()->timestamp,
        ];
    }

    public function typesenseCollectionSchema(): array
    {
        return [
            'name' => $this->searchableAs(),
            'fields' => [
                ['name' => 'id', 'type' => 'string'],
                ['name' => 'sku', 'type' => 'string'],
                ['name' => 'name', 'type' => 'string'],
                ['name' => 'slug', 'type' => 'string'],
                ['name' => 'description', 'type' => 'string', 'optional' => true],
                ['name' => 'base_price_net', 'type' => 'float'],
                ['name' => 'is_active', 'type' => 'bool'],
                ['name' => 'categories', 'type' => 'string[]', 'optional' => true],
                ['name' => 'created_at_timestamp', 'type' => 'int64'],
            ],
            'default_sorting_field' => 'created_at_timestamp',
        ];
    }

    public function typesenseSearchParameters(): array
    {
        return [
            'query_by' => 'name,sku,description,categories',
        ];
    }

    protected function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->with('categories');
    }

    public function stockItem(): BelongsTo
    {
        return $this->belongsTo(StockItem::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function priceLists(): BelongsToMany
    {
        return $this->belongsToMany(PriceList::class)
            ->withPivot('price_net')
            ->withTimestamps();
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function productImages(): HasMany
    {
        return $this->hasMany(ProductImage::class)
            ->orderByDesc('is_primary')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function compatibilityModels(): BelongsToMany
    {
        return $this->belongsToMany(CompatibilityModel::class, 'product_compatibilities')
            ->withTimestamps();
    }

    public function productCompatibilities(): HasMany
    {
        return $this->hasMany(ProductCompatibility::class);
    }

    public function images(): BelongsToMany
    {
        return $this->belongsToMany(MediaFile::class, 'product_images')
            ->withPivot(['sort_order', 'alt', 'is_primary'])
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    public function isActiveForSale(): bool
    {
        if ($this->active !== null) {
            return (bool) $this->active;
        }

        return (bool) $this->is_active;
    }

    public function availableQuantity(): int
    {
        return (int) ($this->stockItem?->availableQuantityForSale() ?? 0);
    }

    public function hasStock(int $requiredQty = 1): bool
    {
        $requiredQty = max(1, $requiredQty);

        return $this->stockItem !== null
            && $this->stockItem->active
            && $this->stockItem->availableQuantityForSale() >= $requiredQty;
    }
}