<?php

namespace App\Services\PaymentProviders;

use App\Contracts\PaymentProviderInterface;
use App\Models\Customer;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\PaymentTransaction;
use App\Models\ShippingMethod;
use Illuminate\Support\Collection;

class CashOnDeliveryPaymentProvider implements PaymentProviderInterface
{
    public function code(): string
    {
        return 'cash_on_delivery';
    }

    public function isAvailable(
        PaymentMethod $method,
        ShippingMethod $shippingMethod,
        Collection $cartItems,
        ?Customer $customer,
    ): bool {
        return true;
    }

    public function validateSelection(PaymentMethod $method, array $input): array
    {
        return [
            'payment_payload_json' => [
                'provider' => $this->code(),
            ],
        ];
    }

    public function initiate(PaymentMethod $method, Order $order, PaymentTransaction $transaction, array $context = []): array
    {
        return [
            'status' => 'pending',
            'redirect_url' => null,
            'external_id' => null,
            'response_payload_json' => [
                'provider' => $this->code(),
                'instruction' => 'Payment on delivery.',
            ],
        ];
    }

    public function handleCallback(array $payload): ?PaymentTransaction
    {
        return null;
    }

    public function handleReturn(array $payload): ?PaymentTransaction
    {
        return null;
    }
}
