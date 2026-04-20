<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductAttribute extends Model
{
    use HasFactory;

    protected $table = 'attributes';

    protected $fillable = [
        'code',
        'name',
        'data_type',
        'unit',
        'is_filterable',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_filterable' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function productTypes(): BelongsToMany
    {
        return $this->belongsToMany(ProductType::class, 'attribute_product_type', 'attribute_id', 'product_type_id')
            ->using(AttributeProductType::class)
            ->withPivot(['is_required', 'is_filterable', 'sort_order'])
            ->withTimestamps();
    }

    public function values(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class, 'attribute_id');
    }
}
