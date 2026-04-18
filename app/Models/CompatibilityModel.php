<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompatibilityModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand',
        'model_name',
        'model_code',
        'slug',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_compatibilities')
            ->withTimestamps();
    }

    public function productCompatibilities(): HasMany
    {
        return $this->hasMany(ProductCompatibility::class);
    }
}
