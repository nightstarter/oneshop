<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\PriceList;
use App\Models\Product;
use App\Services\PriceCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PriceCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_calculates_default_product_price(): void
    {
        $product = Product::query()->create([
            'sku' => 'SKU-001',
            'name' => 'Default Product',
            'slug' => 'default-product',
            'base_price_net' => 100,
            'stock_qty' => 10,
            'is_active' => true,
        ]);

        $pricing = app(PriceCalculator::class)->calculate($product, null, 2);

        $this->assertSame(100.0, $pricing['unit_net']);
        $this->assertSame(21.0, $pricing['unit_vat']);
        $this->assertSame(121.0, $pricing['unit_gross']);
        $this->assertSame(200.0, $pricing['total_net']);
        $this->assertSame(42.0, $pricing['total_vat']);
        $this->assertSame(242.0, $pricing['total_gross']);
    }

    public function test_it_uses_b2b_price_list_when_available(): void
    {
        $product = Product::query()->create([
            'sku' => 'SKU-002',
            'name' => 'B2B Product',
            'slug' => 'b2b-product',
            'base_price_net' => 100,
            'stock_qty' => 10,
            'is_active' => true,
        ]);

        $priceList = PriceList::query()->create([
            'name' => 'VIP',
            'code' => 'vip',
            'currency' => 'CZK',
            'is_active' => true,
        ]);

        $priceList->products()->attach($product->id, ['price_net' => 80]);

        $customer = Customer::query()->create([
            'price_list_id' => $priceList->id,
            'type' => 'company',
            'company_name' => 'ACME',
            'email' => 'buyer@example.test',
            'is_active' => true,
        ]);

        $pricing = app(PriceCalculator::class)->calculate($product, $customer, 1);

        $this->assertSame(80.0, $pricing['unit_net']);
        $this->assertSame(16.8, $pricing['unit_vat']);
        $this->assertSame(96.8, $pricing['unit_gross']);
    }
}