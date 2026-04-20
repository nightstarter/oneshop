<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeviceModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand',
        'model_name',
        'model_normalized',
        'slug',
        'legacy_ex_id',
        'legacy_art_id',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    // ── Relations ──────────────────────────────────────────────────────────

    /** Carrier products compatible with this device model. */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'catalog_product_device_models')
            ->withTimestamps();
    }

    /** SEO products pinned specifically to this device model. */
    public function seoProducts(): HasMany
    {
        return $this->hasMany(Product::class, 'linked_device_model_id');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function scopeSearch(Builder $query, string $normalized): Builder
    {
        return $query->where('model_normalized', 'like', '%' . $normalized . '%');
    }
}
