<?php

namespace App\Contracts;

use App\Models\Customer;
use App\Models\ShippingMethod;
use Illuminate\Support\Collection;

interface ShippingProviderInterface
{
    public function code(): string;

    public function isAvailable(ShippingMethod $method, Collection $cartItems, ?Customer $customer): bool;

    /**
     * Validate shipping-specific checkout payload and return normalized snapshot.
     *
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public function validateSelection(ShippingMethod $method, array $input): array;
}
