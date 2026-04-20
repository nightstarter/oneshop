<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'is_sale',
        'is_active',
        'priority',
    ];

    protected $casts = [
        'is_sale' => 'boolean',
        'is_active' => 'boolean',
        'priority' => 'integer',
    ];

    public function productStocks(): HasMany
    {
        return $this->hasMany(ProductStock::class);
    }
}
