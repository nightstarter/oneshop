<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        protected PriceCalculator $priceCalculator,
        protected InventoryService $inventoryService,
    ) {
    }

    /**
     * @param  array<int, array{product: Product, quantity: int}>  $items
     * @param  array<string, mixed>|null  $billingAddress
     * @param  array<string, mixed>|null  $shippingAddress
     * @param  array<string, mixed>|null  $checkoutSnapshot
     */
    public function create(
        array $items,
        ?Customer $customer = null,
        ?User $user = null,
        ?array $billingAddress = null,
        ?array $shippingAddress = null,
        ?string $note = null,
        ?array $checkoutSnapshot = null,
    ): Order {
        return DB::transaction(function () use ($items, $customer, $user, $billingAddress, $shippingAddress, $note, $checkoutSnapshot) {
            $totals = [
                'net' => 0.0,
                'vat' => 0.0,
                'gross' => 0.0,
            ];

            $vatRate = (float) config('shop.vat_rate', 21.0);

            $order = Order::create([
                'number' => $this->generateNumber(),
                'customer_id' => $customer?->id,
                'user_id' => $user?->id,
                'status' => 'pending',
                'currency' => config('shop.currency', 'CZK'),
                'vat_rate' => $vatRate,
                'billing_address_json' => $billingAddress,
                'shipping_address_json' => $shippingAddress,
                'note' => $note,
                'placed_at' => now(),
                'shipping_method_id' => $checkoutSnapshot['shipping_method_id'] ?? null,
                'shipping_code' => $checkoutSnapshot['shipping_code'] ?? null,
                'shipping_name' => $checkoutSnapshot['shipping_name'] ?? null,
                'shipping_price_net' => $checkoutSnapshot['shipping_price_net'] ?? 0,
                'shipping_price_gross' => $checkoutSnapshot['shipping_price_gross'] ?? 0,
                'payment_method_id' => $checkoutSnapshot['payment_method_id'] ?? null,
                'payment_code' => $checkoutSnapshot['payment_code'] ?? null,
                'payment_name' => $checkoutSnapshot['payment_name'] ?? null,
                'payment_price_net' => $checkoutSnapshot['payment_price_net'] ?? 0,
                'payment_price_gross' => $checkoutSnapshot['payment_price_gross'] ?? 0,
                'pickup_point_id' => $checkoutSnapshot['pickup_point_id'] ?? null,
                'pickup_point_name' => $checkoutSnapshot['pickup_point_name'] ?? null,
                'pickup_point_address' => $checkoutSnapshot['pickup_point_address'] ?? null,
                'shipping_payload_json' => $checkoutSnapshot['shipping_payload_json'] ?? null,
                'payment_payload_json' => $checkoutSnapshot['payment_payload_json'] ?? null,
            ]);

            foreach ($items as $item) {
                $pricing = $this->priceCalculator->calculate($item['product'], $customer, $item['quantity']);

                $order->items()->create([
                    'product_id' => $item['product']->id,
                    'sku' => $item['product']->sku,
                    'product_name' => $item['product']->name,
                    'quantity' => $item['quantity'],
                    'unit_price_net' => $pricing['unit_net'],
                    'unit_vat_amount' => $pricing['unit_vat'],
                    'unit_price_gross' => $pricing['unit_gross'],
                    'total_price_net' => $pricing['total_net'],
                    'total_vat_amount' => $pricing['total_vat'],
                    'total_price_gross' => $pricing['total_gross'],
                ]);

                $totals['net'] += $pricing['total_net'];
                $totals['vat'] += $pricing['total_vat'];
                $totals['gross'] += $pricing['total_gross'];
            }

            $shippingNet = (float) ($checkoutSnapshot['shipping_price_net'] ?? 0);
            $shippingGross = (float) ($checkoutSnapshot['shipping_price_gross'] ?? 0);
            $paymentNet = (float) ($checkoutSnapshot['payment_price_net'] ?? 0);
            $paymentGross = (float) ($checkoutSnapshot['payment_price_gross'] ?? 0);

            $totals['net'] += $shippingNet + $paymentNet;
            $totals['gross'] += $shippingGross + $paymentGross;
            $totals['vat'] += ($shippingGross - $shippingNet) + ($paymentGross - $paymentNet);

            $order->update([
                'price_net' => round($totals['net'], 2),
                'price_vat' => round($totals['vat'], 2),
                'price_gross' => round($totals['gross'], 2),
            ]);

            $this->inventoryService->deductForOrder($order);

            return $order->fresh('items');
        });
    }

    protected function generateNumber(): string
    {
        $year = now()->format('Y');
        $sequence = str_pad((string) (Order::count() + 1), 6, '0', STR_PAD_LEFT);

        return sprintf('ORD-%s-%s', $year, $sequence);
    }
}