<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PriceList extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'currency',
        'is_active',
        'valid_from',
        'valid_to',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'valid_from' => 'date',
        'valid_to' => 'date',
    ];

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)
            ->withPivot('price_net')
            ->withTimestamps();
    }
}