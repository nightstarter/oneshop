@extends('theme::layouts.app')

@section('title', __('shop.orders') . ' - ' . config('app.name'))

@section('content')
    <h1 class="h3 mb-4"><i class="bi bi-list-ul me-2"></i>{{ __('shop.orders') }}</h1>

    @if ($orders->isEmpty())
        <p class="text-muted">{{ __('messages.no_orders') }}</p>
    @else
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <table class="table mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('checkout.order_number') }}</th>
                            <th>{{ __('shop.date') }}</th>
                            <th>{{ __('shop.order_status') }}</th>
                            <th class="text-end">{{ __('shop.total') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr>
                                <td><strong>{{ $order->number }}</strong></td>
                                <td>{{ $order->placed_at?->format('d.m.Y') }}</td>
                                <td>
                                    @php
                                        $badge = match($order->status) {
                                            'placed'     => 'secondary',
                                            'confirmed'  => 'primary',
                                            'processing' => 'info',
                                            'shipped'    => 'warning',
                                            'delivered'  => 'success',
                                            'cancelled'  => 'danger',
                                            default      => 'secondary',
                                        };
                                        $label = match($order->status) {
                                            'placed'     => __('shop.status_received'),
                                            'confirmed'  => __('shop.status_confirmed'),
                                            'processing' => __('shop.status_processing'),
                                            'shipped'    => __('shop.status_shipped'),
                                            'delivered'  => __('shop.status_delivered'),
                                            'cancelled'  => __('shop.status_cancelled'),
                                            default      => $order->status,
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $badge }}">{{ $label }}</span>
                                </td>
                                <td class="text-end fw-semibold">
                                    {{ number_format($order->price_gross, 2, ',', ' ') }} {{ $order->currency }}
                                </td>
                                <td>
                                    <a href="{{ route('account.order', $order) }}" class="btn btn-sm btn-outline-primary">{{ __('buttons.detail') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-3">{{ $orders->links() }}</div>
    @endif
@endsection