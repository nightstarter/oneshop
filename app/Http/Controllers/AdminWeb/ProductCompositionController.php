<?php

namespace App\Http\Controllers\AdminWeb;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductCompositionComponentRequest;
use App\Http\Requests\Admin\UpdateProductCompositionComponentRequest;
use App\Models\Product;
use App\Models\StockItem;
use App\Models\StockItemComponent;

class ProductCompositionController extends Controller
{
    public function store(StoreProductCompositionComponentRequest $request, Product $product)
    {
        $stockItem = $this->resolveStockItem($product);

        $componentId = (int) $request->validated('component_stock_item_id');
        $quantity = (int) $request->validated('quantity');

        if ($componentId === $stockItem->id) {
            return redirect()
                ->route('admin.products.edit', $product)
                ->withErrors(['composition' => __('messages.product_composition_self_component_forbidden')]);
        }

        $component = StockItem::query()->findOrFail($componentId);

        $stockItem->componentItems()->syncWithoutDetaching([
            $component->id => ['quantity' => $quantity],
        ]);

        StockItemComponent::query()
            ->where('stock_item_id', $stockItem->id)
            ->where('component_stock_item_id', $component->id)
            ->update(['quantity' => $quantity]);

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('success', __('messages.product_composition_component_added'));
    }

    public function update(UpdateProductCompositionComponentRequest $request, Product $product, StockItemComponent $component)
    {
        $stockItem = $this->resolveStockItem($product);
        $this->ensureOwnership($stockItem, $component);

        $component->update([
            'quantity' => (int) $request->validated('quantity'),
        ]);

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('success', __('messages.product_composition_component_updated'));
    }

    public function destroy(Product $product, StockItemComponent $component)
    {
        $stockItem = $this->resolveStockItem($product);
        $this->ensureOwnership($stockItem, $component);

        if ($stockItem->isKit() && $stockItem->kitComponents()->count() <= 1) {
            return redirect()
                ->route('admin.products.edit', $product)
                ->withErrors(['composition' => __('messages.product_composition_kit_requires_components')]);
        }

        $component->delete();

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('success', __('messages.product_composition_component_deleted'));
    }

    private function resolveStockItem(Product $product): StockItem
    {
        $stockItem = $product->stockItem;

        abort_if($stockItem === null, 404);

        return $stockItem;
    }

    private function ensureOwnership(StockItem $stockItem, StockItemComponent $component): void
    {
        abort_unless($component->stock_item_id === $stockItem->id, 404);
    }
}
