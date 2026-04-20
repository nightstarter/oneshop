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
        'product_type_id',
        'parent_product_id',
        'linked_device_model_id',
        'sku',
        'legacy_item_code',
        'legacy_group_id',
        'legacy_sphinx_id',
        'name',
        'slug',
        'description',
        'legacy_payload',
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
        'legacy_payload' => 'array',
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

    // ── SEO / Carrier product relations ────────────────────────────────────

    /**
     * Nosný (carrier) produkt pro tento SEO produkt.
     * NULL pokud je tento produkt sám nosným produktem.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'parent_product_id');
    }

    /**
     * SEO produkty odvozené od tohoto nosného produktu.
     */
    public function seoVariants(): HasMany
    {
        return $this->hasMany(Product::class, 'parent_product_id');
    }

    /**
     * Konkrétní model zařízení, na který je tento SEO produkt navázán (volitelné).
     */
    public function linkedDeviceModel(): BelongsTo
    {
        return $this->belongsTo(DeviceModel::class, 'linked_device_model_id');
    }

    // ── Catalog compatibility relations ────────────────────────────────────

    /**
     * Kompatibilní modely zařízení. Platí pouze pro nosný produkt.
     */
    public function deviceModels(): BelongsToMany
    {
        return $this->belongsToMany(DeviceModel::class, 'catalog_product_device_models')
            ->withTimestamps()
            ->orderBy('brand')
            ->orderBy('model_name');
    }

    /**
     * Typová označení / part numbers. Platí pouze pro nosný produkt.
     */
    public function partNumbers(): BelongsToMany
    {
        return $this->belongsToMany(PartNumber::class, 'catalog_product_part_numbers')
            ->withTimestamps()
            ->orderBy('value');
    }

    // ── SEO product helpers ────────────────────────────────────────────────

    public function isCarrier(): bool
    {
        return $this->parent_product_id === null;
    }

    public function isSeoProduct(): bool
    {
        return $this->parent_product_id !== null;
    }

    /**
     * Returns this product's carrier (self if already a carrier).
     * Useful when you need compatibility data regardless of product type.
     */
    public function carrierProduct(): Product
    {
        return $this->isSeoProduct()
            ? ($this->parent ?? $this)
            : $this;
    }

    public function productType(): BelongsTo
    {
        return $this->belongsTo(ProductType::class);
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

    public function attributeValues(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class)
            ->with('attribute')
            ->orderBy('id');
    }

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(ProductAttribute::class, 'product_attribute_values', 'product_id', 'attribute_id')
            ->withPivot(['value_text', 'value_number', 'value_boolean', 'value_json', 'value_unit'])
            ->withTimestamps();
    }

    public function productPrices(): HasMany
    {
        return $this->hasMany(ProductPrice::class)
            ->orderByDesc('valid_from')
            ->orderByDesc('id');
    }

    public function productStocks(): HasMany
    {
        return $this->hasMany(ProductStock::class)
            ->with('warehouse')
            ->orderByDesc('id');
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
        if ($this->relationLoaded('productStocks') ? $this->productStocks->isNotEmpty() : $this->productStocks()->exists()) {
            return (int) $this->productStocks()
                ->selectRaw('SUM(GREATEST(quantity_on_hand - quantity_reserved, 0)) as available_total')
                ->value('available_total');
        }

        return (int) ($this->stockItem?->availableQuantityForSale() ?? 0);
    }

    public function hasStock(int $requiredQty = 1): bool
    {
        $requiredQty = max(1, $requiredQty);

        if ($this->relationLoaded('productStocks') ? $this->productStocks->isNotEmpty() : $this->productStocks()->exists()) {
            return $this->availableQuantity() >= $requiredQty;
        }

        return $this->stockItem !== null
            && $this->stockItem->active
            && $this->stockItem->availableQuantityForSale() >= $requiredQty;
    }
}