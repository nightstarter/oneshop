<?php

namespace App\Services;

use App\Contracts\PaymentProviderInterface;
use App\Models\PaymentMethod;
use InvalidArgumentException;

class PaymentProviderResolver
{
    /** @var array<string, PaymentProviderInterface> */
    private array $providers;

    /**
     * @param  iterable<int, PaymentProviderInterface>  $providers
     */
    public function __construct(iterable $providers)
    {
        $indexed = [];

        foreach ($providers as $provider) {
            $indexed[$provider->code()] = $provider;
        }

        $this->providers = $indexed;
    }

    public function resolveByCode(string $providerCode): PaymentProviderInterface
    {
        if (! isset($this->providers[$providerCode])) {
            throw new InvalidArgumentException("Payment provider [{$providerCode}] is not registered.");
        }

        return $this->providers[$providerCode];
    }

    public function resolveForMethod(PaymentMethod $method): PaymentProviderInterface
    {
        return $this->resolveByCode($method->provider_code);
    }
}
