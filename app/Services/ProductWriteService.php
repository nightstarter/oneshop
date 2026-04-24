<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockItem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ProductWriteService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Product
    {
        return DB::transaction(function () use ($data): Product {
            $product = Product::query()->create($this->productAttributes($data));

            $this->syncStockItem($product, $data);

            return $product->fresh();
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data): Product {
            $product->fill($this->productAttributes($data));
            $product->save();

            $this->syncStockItem($product, $data);

            return $product->fresh();
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function productAttributes(array $data): array
    {
        return Arr::only($data, [
            'product_type_id',
            'parent_product_id',
            'linked_device_model_id',
            'sku',
            'legacy_item_code',
            'legacy_group_id',
            'legacy_sphinx_id',
            'name',
            'slug',
            'description',
            'legacy_payload',
            'stock_item_id',
            'price',
            'active',
            'visibility',
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function syncStockItem(Product $product, array $data): void
    {
        $stockItemIdProvided = array_key_exists('stock_item_id', $data) && $data['stock_item_id'];
        $stockQuantityProvided = array_key_exists('stock_quantity', $data);

        if (! $stockItemIdProvided && ! $stockQuantityProvided) {
            return;
        }

        $stockItem = null;

        if ($stockItemIdProvided) {
            $stockItem = StockItem::query()->findOrFail((int) $data['stock_item_id']);

            if ((int) $product->stock_item_id !== (int) $stockItem->id) {
                $product->forceFill(['stock_item_id' => $stockItem->id])->save();
            }
        } else {
            $stockItem = $product->stockItem;

            if ($stockItem === null) {
                $stockItem = StockItem::query()->create([
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'product_type' => optional($product->productType)->code ?? 'generic',
                    'quantity' => (int) $data['stock_quantity'],
                    'sale_price' => $product->price,
                    'active' => true,
                ]);

                $product->forceFill(['stock_item_id' => $stockItem->id])->save();

                return;
            }
        }

        if ($stockQuantityProvided && $stockItem !== null) {
            $stockItem->forceFill([
                'quantity' => (int) $data['stock_quantity'],
            ])->save();
        }
    }
}
