<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockItemComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_item_id',
        'component_stock_item_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function stockItem(): BelongsTo
    {
        return $this->belongsTo(StockItem::class);
    }

    public function componentStockItem(): BelongsTo
    {
        return $this->belongsTo(StockItem::class, 'component_stock_item_id');
    }
}
