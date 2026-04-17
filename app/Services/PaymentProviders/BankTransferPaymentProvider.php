<?php

namespace App\Services\PaymentProviders;

use App\Contracts\PaymentProviderInterface;
use App\Models\Customer;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\PaymentTransaction;
use App\Models\ShippingMethod;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BankTransferPaymentProvider implements PaymentProviderInterface
{
    public function code(): string
    {
        return 'bank_transfer';
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
        $accountNumber = (string) config('payments.bank_transfer.account_number', '');
        $bankCode = (string) config('payments.bank_transfer.bank_code', '');
        $iban = (string) config('payments.bank_transfer.iban', '');
        $bic = (string) config('payments.bank_transfer.bic', '');
        $message = (string) config('payments.bank_transfer.message', 'Platba za objednavku');

        $amount = number_format((float) $order->price_gross, 2, '.', '');
        $variableSymbol = preg_replace('/\D+/', '', $order->number) ?: (string) $order->id;

        $spdParts = [
            'SPD*1.0',
            'ACC:' . ($iban !== '' ? $iban : ($accountNumber . '/' . $bankCode)),
            'AM:' . $amount,
            'CC:' . $order->currency,
            'X-VS:' . $variableSymbol,
            'MSG:' . Str::limit($message . ' ' . $order->number, 60, ''),
        ];

        if ($bic !== '') {
            $spdParts[] = 'X-SW:' . $bic;
        }

        $qrString = implode('*', $spdParts);
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=280x280&data=' . urlencode($qrString);

        return [
            'status' => 'pending',
            'redirect_url' => null,
            'external_id' => null,
            'response_payload_json' => [
                'provider' => $this->code(),
                'instruction' => 'Zaplatte prevodem na ucet s variabilnim symbolem objednavky.',
                'account_number' => $accountNumber,
                'bank_code' => $bankCode,
                'iban' => $iban,
                'bic' => $bic,
                'amount' => $amount,
                'currency' => $order->currency,
                'variable_symbol' => $variableSymbol,
                'qr_string' => $qrString,
                'qr_url' => $qrUrl,
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
