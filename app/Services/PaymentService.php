<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\PaymentTransaction;
use App\Models\ShippingMethod;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function __construct(
        private readonly PaymentProviderResolver $providers,
    ) {}

    public function availableMethodsForShipping(
        ShippingMethod $shippingMethod,
        Collection $cartItems,
        ?Customer $customer,
    ): Collection {
        return $shippingMethod->paymentMethods()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->filter(function (PaymentMethod $method) use ($shippingMethod, $cartItems, $customer) {
                return $this->providers->resolveForMethod($method)->isAvailable($method, $shippingMethod, $cartItems, $customer);
            })
            ->values();
    }

    public function resolveSelectedMethod(
        int $paymentMethodId,
        ShippingMethod $shippingMethod,
        Collection $cartItems,
        ?Customer $customer,
    ): PaymentMethod {
        $method = PaymentMethod::query()
            ->where('is_active', true)
            ->findOrFail($paymentMethodId);

        $allowed = $this->availableMethodsForShipping($shippingMethod, $cartItems, $customer)->contains('id', $method->id);

        if (! $allowed) {
            throw ValidationException::withMessages([
                'payment_method_id' => __('messages.payment_incompatible'),
            ]);
        }

        return $method;
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function validateSelection(PaymentMethod $method, array $input): array
    {
        return $this->providers->resolveForMethod($method)->validateSelection($method, $input);
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array{transaction: PaymentTransaction, is_redirect: bool, redirect_url: string|null}
     */
    public function initiate(Order $order, PaymentMethod $method, array $context = []): array
    {
        $transaction = PaymentTransaction::create([
            'order_id' => $order->id,
            'payment_method_id' => $method->id,
            'provider_code' => $method->provider_code,
            'type' => $method->type,
            'status' => 'pending',
            'currency' => $order->currency,
            'amount_gross' => $order->price_gross,
            'request_payload_json' => $context,
        ]);

        $result = $this->providers->resolveForMethod($method)->initiate($method, $order, $transaction, $context);

        $transaction->update([
            'status' => $result['status'] ?? 'pending',
            'external_id' => $result['external_id'] ?? null,
            'redirect_url' => $result['redirect_url'] ?? null,
            'response_payload_json' => $result['response_payload_json'] ?? null,
        ]);

        return [
            'transaction' => $transaction->fresh(),
            'is_redirect' => ! empty($result['redirect_url']),
            'redirect_url' => $result['redirect_url'] ?? null,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handleCallback(string $providerCode, array $payload): ?PaymentTransaction
    {
        $transaction = $this->providers->resolveByCode($providerCode)->handleCallback($payload);

        return $this->applyPaymentResult($transaction, $payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handleReturn(string $providerCode, array $payload): ?PaymentTransaction
    {
        $transaction = $this->providers->resolveByCode($providerCode)->handleReturn($payload);

        return $this->applyPaymentResult($transaction, $payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function applyPaymentResult(?PaymentTransaction $transaction, array $payload): ?PaymentTransaction
    {
        if (! $transaction) {
            return null;
        }

        $statusRaw = strtoupper((string) ($payload['status'] ?? 'PENDING'));
        $isPaid = in_array($statusRaw, ['PAID', 'SUCCESS', 'OK', 'AUTHORIZED'], true);

        $transaction->update([
            'status' => $isPaid ? 'paid' : 'failed',
            'paid_at' => $isPaid ? now() : null,
            'response_payload_json' => $payload,
        ]);

        $transaction->order->update([
            'status' => $isPaid ? 'confirmed' : 'cancelled',
        ]);

        return $transaction->fresh();
    }
}
