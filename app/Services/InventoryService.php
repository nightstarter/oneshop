<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\StockItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InventoryService
{
    public function availableQuantityForProduct(Product $product): int
    {
        return (int) ($product->stockItem?->availableQuantityForSale() ?? 0);
    }

    public function isProductAvailable(Product $product, int $requiredQty = 1): bool
    {
        $requiredQty = max(1, $requiredQty);

        $stockItem = $product->stockItem;
        if ($stockItem === null || !$stockItem->active) {
            return false;
        }

        return $stockItem->availableQuantityForSale() >= $requiredQty;
    }

    /**
     * Deducts stock from physical stock_items based on the finalized order items.
     */
    public function deductForOrder(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            $requiredByStockItem = $this->buildRequiredByStockItem($order);

            foreach ($requiredByStockItem as $stockItemId => $requiredQty) {
                /** @var StockItem|null $stockItem */
                $stockItem = StockItem::query()->lockForUpdate()->find($stockItemId);

                if ($stockItem === null || !$stockItem->active) {
                    throw new RuntimeException('Stock item is missing or inactive.');
                }

                if ($stockItem->quantity < $requiredQty) {
                    throw new RuntimeException('Insufficient stock for SKU ' . $stockItem->sku . '.');
                }

                $stockItem->decrement('quantity', $requiredQty);
            }
        });
    }

    /**
     * @return Collection<int, int>
     */
    private function buildRequiredByStockItem(Order $order): Collection
    {
        $items = $order->items()->with([
            'product:id,stock_item_id',
            'product.stockItem:id,product_type,active',
            'product.stockItem.kitComponents:id,stock_item_id,component_stock_item_id,quantity',
        ])->get();

        $requiredByStockItem = collect();

        foreach ($items as $item) {
            $orderedQty = (int) $item->quantity;
            $stockItem = $item->product?->stockItem;

            if ($stockItem === null) {
                continue;
            }

            // Always decrement the stock card directly assigned to product.
            $stockItemId = (int) $stockItem->id;
            $requiredByStockItem[$stockItemId] = (int) ($requiredByStockItem[$stockItemId] ?? 0) + $orderedQty;

            if (!$stockItem->isKit()) {
                continue;
            }

            // For kits, decrement each component by its composition ratio * ordered qty.
            foreach ($stockItem->kitComponents as $component) {
                $componentStockItemId = (int) $component->component_stock_item_id;
                $componentQty = (int) $component->quantity;

                if ($componentStockItemId <= 0 || $componentQty <= 0) {
                    continue;
                }

                $requiredByStockItem[$componentStockItemId] = (int) ($requiredByStockItem[$componentStockItemId] ?? 0)
                    + ($componentQty * $orderedQty);
            }
        }

        return $requiredByStockItem;
    }
}
