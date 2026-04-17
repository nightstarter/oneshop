<?php

namespace App\Services\PaymentProviders;

use App\Contracts\PaymentProviderInterface;
use App\Models\Customer;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\PaymentTransaction;
use App\Models\ShippingMethod;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use RuntimeException;

use Illuminate\Support\Facades\Log;


class ComgatePaymentProvider implements PaymentProviderInterface
{
    public function code(): string
    {
        return 'comgate';
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
        $merchant = (string) config('payments.comgate.merchant');
        $secret = (string) config('payments.comgate.secret');

        if ($merchant === '' || $secret === '') {
            throw new RuntimeException('Comgate credentials are missing. Set COMGATE_MERCHANT and COMGATE_SECRET.');
        }

        $createUrl = (string) config('payments.comgate.create_url');
        $timeout = (int) config('payments.comgate.timeout_seconds', 15);

        $externalId = (string) $transaction->id;

        $request = [
            'merchant' => $merchant,
            'secret' => $secret,
            'price' => (int) round((float) $order->price_gross * 100),
            'curr' => strtoupper($order->currency),
            'label' => 'Objednavka ' . $order->number,
            'refId' => $externalId,
            'test' => config('payments.comgate.test') ? 'true' : 'false',
            'method' => 'ALL',
            'email' => Arr::get($order->billing_address_json ?? [], 'email', ''),
            'lang' => 'cs',
            'prepareOnly' => 'true',
            'url_paid' => route('payments.return', ['provider' => $this->code()]),
            'url_cancelled' => route('payments.return', ['provider' => $this->code()]),
            'url_pending' => route('payments.return', ['provider' => $this->code()]),
            'url_result' => route('payments.callback', ['provider' => $this->code()]),
        ];

      $http = Http::asForm()
        ->accept('application/x-www-form-urlencoded')
    ->timeout($timeout);

if (!config('payments.comgate.verify_ssl', true)) {
    $http = $http->withoutVerifying();
}

$response = $http->post($createUrl, $request);

Log::debug('Comgate create response', [
    'status' => $response->status(),
    'headers' => $response->headers(),
    'body' => $response->body(),
]);

        if (! $response->ok()) {
            throw new RuntimeException('Comgate create call failed with HTTP ' . $response->status());
        }

        $parsed = $this->parseResponseBody($response->body());

        if (($parsed['code'] ?? null) !== '0') {
            $message = $parsed['message'] ?? 'Unknown Comgate error';
            throw new RuntimeException('Comgate create failed: ' . $message);
        }

        $redirectUrl = $parsed['redirect'] ?? null;
        $transId = $parsed['transId'] ?? null;

        if (! $redirectUrl || ! $transId) {
            throw new RuntimeException('Comgate response missing redirect/transId');
        }

        return [
            'status' => 'pending',
            'redirect_url' => $redirectUrl,
            'external_id' => (string) $transId,
            'response_payload_json' => [
                'provider' => $this->code(),
                'gateway' => 'comgate',
                'redirect_url' => $redirectUrl,
                'comgate' => $parsed,
            ],
        ];
    }

    public function handleCallback(array $payload): ?PaymentTransaction
    {
        $transaction = $this->resolveTransaction($payload);

        if (! $transaction) {
            return null;
        }

        if (! isset($payload['status'])) {
            $payload['status'] = $this->fetchComgateStatus((string) $transaction->external_id) ?? 'PENDING';
        }

        $transaction->update([
            'response_payload_json' => $payload,
        ]);

        return $transaction->fresh();
    }

    public function handleReturn(array $payload): ?PaymentTransaction
    {
        $transaction = $this->resolveTransaction($payload);

        if (! $transaction) {
            return null;
        }

        if (! isset($payload['status'])) {
            $payload['status'] = $this->fetchComgateStatus((string) $transaction->external_id) ?? 'PENDING';
        }

        $transaction->update([
            'response_payload_json' => $payload,
        ]);

        return $transaction->fresh();
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function resolveTransaction(array $payload): ?PaymentTransaction
    {
        $externalId = (string) ($payload['external_id'] ?? $payload['transId'] ?? '');

        if ($externalId === '') {
            return null;
        }

        return PaymentTransaction::query()->where('external_id', $externalId)->first();
    }

    private function fetchComgateStatus(string $transId): ?string
    {
        $merchant = (string) config('payments.comgate.merchant');
        $secret = (string) config('payments.comgate.secret');

        if ($merchant === '' || $secret === '') {
            return null;
        }

        $statusUrl = (string) config('payments.comgate.status_url');
        $timeout = (int) config('payments.comgate.timeout_seconds', 15);

        $response = Http::asForm()->timeout($timeout)->post($statusUrl, [
            'merchant' => $merchant,
            'secret' => $secret,
            'transId' => $transId,
            'test' => config('payments.comgate.test') ? 'true' : 'false',
        ]);

        if (! $response->ok()) {
            return null;
        }

        $parsed = $this->parseResponseBody($response->body());

        return $parsed['status'] ?? null;
    }

    /**
     * Comgate returns query-string-like response in plain text.
     *
     * @return array<string, string>
     */
    private function parseResponseBody(string $body): array
    {

      $result = [];
    parse_str($body, $result);
    return $result;

    
        $trimmed = trim($body);

        if ($trimmed === '') {
            return [];
        }

        if ($trimmed[0] === '{') {
            $json = json_decode($trimmed, true);

            return is_array($json) ? $json : [];
        }

        parse_str(str_replace("\n", '&', $trimmed), $parsed);

        return is_array($parsed) ? $parsed : [];
    }
}
