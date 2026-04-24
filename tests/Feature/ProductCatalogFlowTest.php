<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\StockItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCatalogFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_hides_inactive_products(): void
    {
        $active = $this->createProduct('catalog-active', true, 5);
        $inactive = $this->createProduct('catalog-inactive', false, 5);

        $response = $this->get(route('products.index'));

        $response->assertOk();
        $response->assertSee($active->name);
        $response->assertDontSee($inactive->name);
    }

    public function test_cart_flow_uses_final_product_schema(): void
    {
        $product = $this->createProduct('cart-product', true, 5, 100);

        $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 2,
        ])->assertRedirect();

        $response = $this->get(route('cart.index'));

        $response->assertOk();
        $response->assertSee($product->name);
        $response->assertSee('242');
    }

    private function createProduct(string $slug, bool $active, int $quantity, float $price = 100): Product
    {
        $stockItem = StockItem::query()->create([
            'sku' => strtoupper($slug) . '-STOCK',
            'name' => ucfirst($slug) . ' stock',
            'product_type' => 'generic',
            'quantity' => $quantity,
            'active' => true,
        ]);

        return Product::query()->create([
            'sku' => strtoupper($slug),
            'name' => ucfirst(str_replace('-', ' ', $slug)),
            'slug' => $slug,
            'price' => $price,
            'active' => $active,
            'stock_item_id' => $stockItem->id,
            'visibility' => 'public',
        ]);
    }
}
