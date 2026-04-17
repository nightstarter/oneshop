<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\ShippingMethod;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ShippingService
{
    public function __construct(
        private readonly ShippingProviderResolver $providers,
    ) {}

    public function availableMethods(Collection $cartItems, ?Customer $customer): Collection
    {
        return ShippingMethod::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->filter(function (ShippingMethod $method) use ($cartItems, $customer) {
                return $this->providers->resolveForMethod($method)->isAvailable($method, $cartItems, $customer);
            })
            ->values();
    }

    public function resolveSelectedMethod(int $shippingMethodId, Collection $cartItems, ?Customer $customer): ShippingMethod
    {
        $method = ShippingMethod::query()
            ->where('is_active', true)
            ->findOrFail($shippingMethodId);

        if (! $this->providers->resolveForMethod($method)->isAvailable($method, $cartItems, $customer)) {
            throw ValidationException::withMessages([
                'shipping_method_id' => __('messages.shipping_unavailable'),
            ]);
        }

        return $method;
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function validateSelection(ShippingMethod $method, array $input): array
    {
        return $this->providers->resolveForMethod($method)->validateSelection($method, $input);
    }
}
