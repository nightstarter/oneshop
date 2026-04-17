<?php

namespace App\Services\ShippingProviders;

use App\Contracts\ShippingProviderInterface;
use App\Models\Customer;
use App\Models\ShippingMethod;
use Illuminate\Support\Collection;

class GlsAddressShippingProvider implements ShippingProviderInterface
{
    public function code(): string
    {
        return 'gls_address';
    }

    public function isAvailable(ShippingMethod $method, Collection $cartItems, ?Customer $customer): bool
    {
        return true;
    }

    public function validateSelection(ShippingMethod $method, array $input): array
    {
        return [
            'pickup_point_id' => null,
            'pickup_point_name' => null,
            'pickup_point_address' => null,
            'shipping_payload_json' => [
                'provider' => $this->code(),
                'delivery_type' => 'address',
            ],
        ];
    }
}
