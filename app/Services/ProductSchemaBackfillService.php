<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockItem;

class ProductSchemaBackfillService
{
    public function run(): void
    {
        Product::query()
            ->with(['parent:id,stock_item_id,price,active'])
            ->orderBy('id')
            ->chunkById(100, function ($products): void {
                foreach ($products as $product) {
                    $updates = [];

                    if ($product->price === null) {
                        $updates['price'] = $product->getAttribute('base_price_net')
                            ?? $product->parent?->price
                            ?? 0;
                    }

                    if ($product->active === null) {
                        $updates['active'] = (bool) (
                            $product->getAttribute('is_active')
                            ?? $product->parent?->active
                            ?? true
                        );
                    }

                    if ($product->stock_item_id === null) {
                        $parentStockItemId = null;

                        if ($product->parent_product_id !== null) {
                            $parentStockItemId = Product::query()
                                ->whereKey($product->parent_product_id)
                                ->value('stock_item_id');
                        }

                        $updates['stock_item_id'] = $parentStockItemId
                            ?? $this->createStockItemFor($product)->id;
                    }

                    if ($updates !== []) {
                        $product->forceFill($updates)->save();
                    }
                }
            });
    }

    private function createStockItemFor(Product $product): StockItem
    {
        return StockItem::query()->create([
            'sku' => $product->sku,
            'name' => $product->name,
            'product_type' => optional($product->productType)->code ?? 'generic',
            'quantity' => (int) ($product->getAttribute('stock_qty') ?? 0),
            'sale_price' => $product->price ?? $product->getAttribute('base_price_net'),
            'active' => true,
        ]);
    }
}
