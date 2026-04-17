@extends('admin.layout')

@section('title', __('shop.admin.payment_transactions') . ' #' . $transaction->id)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">{{ __('shop.admin.payment_transactions') }} #{{ $transaction->id }}</h1>
    <a href="{{ route('admin.payment-transactions.index') }}" class="btn btn-outline-secondary">{{ __('buttons.back') }}</a>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">{{ __('shop.admin.basic_data') }}</div>
            <div class="card-body">
                <p class="mb-1"><strong>{{ __('shop.admin.order') }}:</strong>
                    @if($transaction->order)
                        <a href="{{ route('admin.orders.show', $transaction->order) }}">{{ $transaction->order->number }}</a>
                    @else
                        -
                    @endif
                </p>
                <p class="mb-1"><strong>{{ __('shop.admin.payment_method') }}:</strong> {{ $transaction->paymentMethod?->name ?? '-' }}</p>
                <p class="mb-1"><strong>{{ __('shop.admin.provider') }}:</strong> {{ $transaction->provider_code }}</p>
                <p class="mb-1"><strong>{{ __('shop.admin.type') }}:</strong> {{ $transaction->type }}</p>
                <p class="mb-1"><strong>{{ __('forms.status') }}:</strong> {{ $transaction->status }}</p>
                <p class="mb-1"><strong>{{ __('shop.admin.amount') }}:</strong> {{ number_format((float) $transaction->amount_gross, 2, ',', ' ') }} {{ $transaction->currency }}</p>
                <p class="mb-1"><strong>{{ __('shop.admin.external_id') }}:</strong> {{ $transaction->external_id ?? '-' }}</p>
                <p class="mb-0"><strong>{{ __('shop.date') }}:</strong> {{ $transaction->paid_at?->format('d.m.Y H:i') ?? '-' }}</p>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card mb-3">
            <div class="card-header">{{ __('shop.admin.request_payload') }}</div>
            <div class="card-body">
                <pre class="small mb-0">{{ json_encode($transaction->request_payload_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        </div>
        <div class="card">
            <div class="card-header">{{ __('shop.admin.response_payload') }}</div>
            <div class="card-body">
                <pre class="small mb-0">{{ json_encode($transaction->response_payload_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        </div>
    </div>
</div>
@endsection
