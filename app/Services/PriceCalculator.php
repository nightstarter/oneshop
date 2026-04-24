<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Product;

class PriceCalculator
{
    public function calculate(Product $product, ?Customer $customer = null, int $quantity = 1): array
    {
        $unitNet = $this->resolveUnitNetPrice($product, $customer);
        $vatRate = (float) config('shop.vat_rate', 21.0);

        $unitVat = round($unitNet * ($vatRate / 100), 2);
        $unitGross = round($unitNet + $unitVat, 2);

        $totalNet = round($unitNet * $quantity, 2);
        $totalVat = round($unitVat * $quantity, 2);
        $totalGross = round($unitGross * $quantity, 2);

        return [
            'vat_rate' => $vatRate,
            'unit_net' => $unitNet,
            'unit_vat' => $unitVat,
            'unit_gross' => $unitGross,
            'total_net' => $totalNet,
            'total_vat' => $totalVat,
            'total_gross' => $totalGross,
        ];
    }

    public function resolveUnitNetPrice(Product $product, ?Customer $customer = null): float
    {
        if (! $customer?->price_list_id) {
            return (float) $product->price;
        }

        $priceListEntry = $product->priceLists()
            ->whereKey($customer->price_list_id)
            ->first();

        if ($priceListEntry?->pivot?->price_net !== null) {
            return (float) $priceListEntry->pivot->price_net;
        }

        return (float) $product->price;
    }
}
