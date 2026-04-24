<?php

namespace Tests\Feature;

use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Models\StockItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutInventoryFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_creates_order_and_deducts_stock(): void
    {
        $stockItem = StockItem::query()->create([
            'sku' => 'CHECKOUT-STOCK',
            'name' => 'Checkout Stock',
            'product_type' => 'generic',
            'quantity' => 10,
            'active' => true,
        ]);

        $product = Product::query()->create([
            'sku' => 'CHECKOUT-PRODUCT',
            'name' => 'Checkout Product',
            'slug' => 'checkout-product',
            'price' => 100,
            'active' => true,
            'stock_item_id' => $stockItem->id,
            'visibility' => 'public',
        ]);

        $shipping = ShippingMethod::query()->create([
            'code' => 'pickup',
            'name' => 'Pickup',
            'provider_code' => 'personal_pickup',
            'type' => 'pickup',
            'is_active' => true,
            'price_net' => 0,
            'price_gross' => 0,
        ]);

        $payment = PaymentMethod::query()->create([
            'code' => 'bank',
            'name' => 'Bank Transfer',
            'provider_code' => 'bank_transfer',
            'type' => 'offline',
            'is_active' => true,
            'price_net' => 0,
            'price_gross' => 0,
        ]);

        $shipping->paymentMethods()->sync([$payment->id]);

        $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 2,
        ])->assertRedirect();

        $response = $this->post(route('checkout.store'), [
            'shipping_method_id' => $shipping->id,
            'payment_method_id' => $payment->id,
            'billing_first_name' => 'John',
            'billing_last_name' => 'Doe',
            'billing_street' => 'Main 1',
            'billing_city' => 'Prague',
            'billing_zip' => '11000',
            'billing_country' => 'CZ',
            'use_billing_as_shipping' => 1,
            'email' => 'john@example.test',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseHas('order_items', [
            'sku' => 'CHECKOUT-PRODUCT',
            'quantity' => 2,
        ]);
        $this->assertSame(8, $stockItem->fresh()->quantity);
    }
}
