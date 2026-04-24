<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\StockItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_uses_active_flag_without_legacy_fallback(): void
    {
        $product = Product::query()->create([
            'sku' => 'UNIT-001',
            'name' => 'Unit Product',
            'slug' => 'unit-product',
            'price' => 100,
            'active' => true,
        ]);

        $this->assertTrue($product->isActiveForSale());

        $product->forceFill(['active' => false])->save();

        $this->assertFalse($product->fresh()->isActiveForSale());
    }

    public function test_it_derives_available_quantity_from_stock_item(): void
    {
        $stockItem = StockItem::query()->create([
            'sku' => 'UNIT-STOCK-001',
            'name' => 'Unit Stock',
            'product_type' => 'generic',
            'quantity' => 7,
            'active' => true,
        ]);

        $product = Product::query()->create([
            'sku' => 'UNIT-002',
            'name' => 'Stocked Product',
            'slug' => 'stocked-product',
            'price' => 100,
            'active' => true,
            'stock_item_id' => $stockItem->id,
        ]);

        $this->assertSame(7, $product->availableQuantity());
        $this->assertTrue($product->hasStock(3));
        $this->assertFalse($product->hasStock(8));
        $this->assertSame(7, $product->available_quantity);
    }
}
