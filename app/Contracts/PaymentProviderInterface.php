<?php

namespace App\Contracts;

use App\Models\Customer;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\PaymentTransaction;
use App\Models\ShippingMethod;
use Illuminate\Support\Collection;

interface PaymentProviderInterface
{
    public function code(): string;

    public function isAvailable(
        PaymentMethod $method,
        ShippingMethod $shippingMethod,
        Collection $cartItems,
        ?Customer $customer,
    ): bool;

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function validateSelection(PaymentMethod $method, array $input): array;

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function initiate(PaymentMethod $method, Order $order, PaymentTransaction $transaction, array $context = []): array;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handleCallback(array $payload): ?PaymentTransaction;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handleReturn(array $payload): ?PaymentTransaction;
}
