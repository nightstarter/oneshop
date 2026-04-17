@extends('theme::layouts.app')

@section('title', __('shop.order_detail', ['number' => $order->number]) . ' - ' . config('app.name'))

@section('content')
    <a href="{{ route('account.orders') }}" class="btn btn-link ps-0 mb-3">
        <i class="bi bi-arrow-left me-1"></i>{{ __('shop.back_to_orders') }}
    </a>

    <h1 class="h3 mb-1">{{ __('shop.order_detail', ['number' => $order->number]) }}</h1>
    <p class="text-muted mb-4">{{ $order->placed_at?->format('d.m.Y H:i') }}</p>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-3">
                <div class="card-header fw-bold">{{ __('shop.order_items') }}</div>
                <div class="card-body p-0">
                    <table class="table mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('shop.product') }}</th>
                                <th class="text-center">{{ __('shop.quantity') }}</th>
                                <th class="text-end">{{ __('shop.price_per_item') }}</th>
                                <th class="text-end">{{ __('shop.total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->items as $item)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $item->product_name }}</div>
                                        <div class="text-muted small">{{ $item->sku }}</div>
                                    </td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-end">{{ number_format($item->unit_price_gross, 2, ',', ' ') }} {{ $order->currency }}</td>
                                    <td class="text-end fw-semibold">{{ number_format($item->total_price_gross, 2, ',', ' ') }} {{ $order->currency }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="3" class="text-end text-muted">{{ __('shop.price_net') }}</td>
                                <td class="text-end">{{ number_format($order->price_net, 2, ',', ' ') }} {{ $order->currency }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end text-muted">{{ __('shop.vat_label', ['rate' => $order->vat_rate]) }}</td>
                                <td class="text-end">{{ number_format($order->price_vat, 2, ',', ' ') }} {{ $order->currency }}</td>
                            </tr>
                            <tr class="fw-bold">
                                <td colspan="3" class="text-end">{{ __('shop.total') }}</td>
                                <td class="text-end text-danger fs-5">{{ number_format($order->price_gross, 2, ',', ' ') }} {{ $order->currency }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-3">
                <div class="card-header fw-bold">{{ __('checkout.billing_address') }}</div>
                <div class="card-body small">
                    @if ($order->billing_address_json)
                        @php $b = $order->billing_address_json; @endphp
                        <p class="mb-0">
                            {{ $b['first_name'] }} {{ $b['last_name'] }}<br>
                            {{ $b['street'] }}<br>
                            {{ $b['zip'] }} {{ $b['city'] }}<br>
                            {{ $b['country'] }}
                        </p>
                    @else
                        <p class="text-muted mb-0">-</p>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header fw-bold">{{ __('checkout.shipping_address') }}</div>
                <div class="card-body small">
                    @if ($order->shipping_address_json)
                        @php $s = $order->shipping_address_json; @endphp
                        <p class="mb-0">
                            {{ $s['first_name'] }} {{ $s['last_name'] }}<br>
                            {{ $s['street'] }}<br>
                            {{ $s['zip'] }} {{ $s['city'] }}<br>
                            {{ $s['country'] }}
                        </p>
                    @else
                        <p class="text-muted mb-0">-</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection