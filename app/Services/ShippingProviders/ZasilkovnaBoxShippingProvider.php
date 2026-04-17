<?php

namespace App\Services\ShippingProviders;

use App\Contracts\ShippingProviderInterface;
use App\Models\Customer;
use App\Models\ShippingMethod;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ZasilkovnaBoxShippingProvider implements ShippingProviderInterface
{
    public function code(): string
    {
        return 'zasilkovna_box';
    }

    public function isAvailable(ShippingMethod $method, Collection $cartItems, ?Customer $customer): bool
    {
        return true;
    }

    public function validateSelection(ShippingMethod $method, array $input): array
    {
        $pointId = trim((string) ($input['pickup_point_id'] ?? ''));
        $pointName = trim((string) ($input['pickup_point_name'] ?? ''));
        $pointAddress = trim((string) ($input['pickup_point_address'] ?? ''));

        if ($pointId === '' || $pointName === '' || $pointAddress === '') {
            throw ValidationException::withMessages([
                'pickup_point_id' => __('messages.pickup_point_required'),
            ]);
        }

        return [
            'pickup_point_id' => $pointId,
            'pickup_point_name' => $pointName,
            'pickup_point_address' => $pointAddress,
            'shipping_payload_json' => [
                'provider' => $this->code(),
                'pickup_point' => [
                    'id' => $pointId,
                    'name' => $pointName,
                    'address' => $pointAddress,
                ],
            ],
        ];
    }
}
