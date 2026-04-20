<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PartNumber extends Model
{
    use HasFactory;

    protected $fillable = [
        'value',
        'value_normalized',
        'legacy_ex_id',
        'legacy_art_id',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    // ── Relations ──────────────────────────────────────────────────────────

    /** Carrier products that carry / replace this part number. */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'catalog_product_part_numbers')
            ->withTimestamps();
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function scopeSearch(Builder $query, string $normalized): Builder
    {
        return $query->where('value_normalized', 'like', '%' . $normalized . '%');
    }
}
