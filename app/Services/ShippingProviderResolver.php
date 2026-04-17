<?php

namespace App\Services;

use App\Contracts\ShippingProviderInterface;
use App\Models\ShippingMethod;
use InvalidArgumentException;

class ShippingProviderResolver
{
    /** @var array<string, ShippingProviderInterface> */
    private array $providers;

    /**
     * @param  iterable<int, ShippingProviderInterface>  $providers
     */
    public function __construct(iterable $providers)
    {
        $indexed = [];

        foreach ($providers as $provider) {
            $indexed[$provider->code()] = $provider;
        }

        $this->providers = $indexed;
    }

    public function resolveByCode(string $providerCode): ShippingProviderInterface
    {
        if (! isset($this->providers[$providerCode])) {
            throw new InvalidArgumentException("Shipping provider [{$providerCode}] is not registered.");
        }

        return $this->providers[$providerCode];
    }

    public function resolveForMethod(ShippingMethod $method): ShippingProviderInterface
    {
        return $this->resolveByCode($method->provider_code);
    }
}
