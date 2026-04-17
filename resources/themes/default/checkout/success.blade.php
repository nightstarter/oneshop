@extends('theme::layouts.app')

@section('title', __('checkout.order_received') . ' - ' . config('app.name'))

@section('content')
    @php $transaction = $order->paymentTransactions->last(); @endphp
    @php $txPayload = $transaction?->response_payload_json ?? []; @endphp

    <div class="text-center py-4">
        <i class="bi bi-check-circle-fill display-1 text-success"></i>
        <h1 class="h2 mt-3">{{ __('checkout.thank_you') }}</h1>
        <p class="text-muted fs-5 mb-1">{{ __('checkout.order_number') }}: <strong>{{ $order->number }}</strong></p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-3">
                <div class="card-header fw-bold">{{ __('checkout.shipping_recap') }}</div>
                <div class="card-body">
                    <p class="mb-1"><strong>{{ __('checkout.shipping') }}:</strong> {{ $order->shipping_name ?? '-' }} ({{ number_format((float) $order->shipping_price_gross, 2, ',', ' ') }} {{ $order->currency }})</p>
                    <p class="mb-1"><strong>{{ __('checkout.payment') }}:</strong> {{ $order->payment_name ?? '-' }} ({{ number_format((float) $order->payment_price_gross, 2, ',', ' ') }} {{ $order->currency }})</p>
                    @if ($order->pickup_point_name)
                        <p class="mb-0"><strong>{{ __('checkout.pickup_point') }}:</strong> {{ $order->pickup_point_name }} ({{ $order->pickup_point_address }})</p>
                    @endif
                </div>
            </div>

            @if ($transaction)
                <div class="card shadow-sm mb-3">
                    <div class="card-header fw-bold">{{ __('checkout.transaction') }}</div>
                    <div class="card-body">
                        <p class="mb-1"><strong>{{ __('checkout.provider') }}:</strong> {{ $transaction->provider_code }}</p>
                        <p class="mb-1"><strong>{{ __('checkout.status') }}:</strong> {{ $transaction->status }}</p>
                        @if ($transaction->external_id)
                            <p class="mb-0"><strong>{{ __('checkout.external_id') }}:</strong> {{ $transaction->external_id }}</p>
                        @endif
                    </div>
                </div>

                @if ($transaction->provider_code === 'bank_transfer')
                    <div class="card shadow-sm mb-3">
                        <div class="card-header fw-bold">{{ __('checkout.bank_transfer_instructions') }}</div>
                        <div class="card-body">
                            <div class="row g-3 align-items-center">
                                <div class="col-md-8">
                                    <p class="mb-1"><strong>{{ __('checkout.amount') }}:</strong> {{ $txPayload['amount'] ?? number_format((float) $transaction->amount_gross, 2, '.', '') }} {{ $txPayload['currency'] ?? $transaction->currency }}</p>
                                    <p class="mb-1"><strong>{{ __('checkout.account_number') }}:</strong> {{ ($txPayload['account_number'] ?? '') }}@if(!empty($txPayload['bank_code'])) / {{ $txPayload['bank_code'] }} @endif</p>
                                    @if (!empty($txPayload['iban']))
                                        <p class="mb-1"><strong>{{ __('checkout.iban') }}:</strong> {{ $txPayload['iban'] }}</p>
                                    @endif
                                    @if (!empty($txPayload['bic']))
                                        <p class="mb-1"><strong>{{ __('checkout.bic') }}:</strong> {{ $txPayload['bic'] }}</p>
                                    @endif
                                    <p class="mb-0"><strong>{{ __('checkout.variable_symbol') }}:</strong> {{ $txPayload['variable_symbol'] ?? '' }}</p>
                                </div>
                                <div class="col-md-4 text-center">
                                    @if (!empty($txPayload['qr_url']))
                                        <img src="{{ $txPayload['qr_url'] }}" alt="QR platba" class="img-fluid rounded border" style="max-width:220px;">
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            <div class="text-center">
                @auth
                    <a href="{{ route('account.orders') }}" class="btn btn-primary mt-2 me-2">
                        <i class="bi bi-list-ul me-1"></i>{{ __('buttons.my_orders') }}
                    </a>
                @endauth
                <a href="{{ route('home') }}" class="btn btn-outline-secondary mt-2">
                    <i class="bi bi-house me-1"></i>{{ __('buttons.go_home') }}
                </a>
            </div>
        </div>
    </div>
@endsection