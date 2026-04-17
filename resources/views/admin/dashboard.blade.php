@extends('admin.layout')

@section('title', __('shop.admin.dashboard'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">{{ __('shop.admin.dashboard') }}</h1>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card"><div class="card-body"><div class="text-muted small">{{ __('shop.admin.products') }}</div><div class="h4 mb-0">{{ $productsCount }}</div></div></div>
    </div>
    <div class="col-md-3">
        <div class="card"><div class="card-body"><div class="text-muted small">{{ __('shop.admin.categories') }}</div><div class="h4 mb-0">{{ $categoriesCount }}</div></div></div>
    </div>
    <div class="col-md-3">
        <div class="card"><div class="card-body"><div class="text-muted small">{{ __('shop.admin.customers') }}</div><div class="h4 mb-0">{{ $customersCount }}</div></div></div>
    </div>
    <div class="col-md-3">
        <div class="card"><div class="card-body"><div class="text-muted small">{{ __('shop.admin.orders') }}</div><div class="h4 mb-0">{{ $ordersCount }}</div></div></div>
    </div>
    <div class="col-md-3">
        <div class="card border-primary"><div class="card-body"><div class="text-muted small">{{ __('shop.active_theme') }}</div><div class="h4 mb-1">{{ $activeTheme }}</div><div class="small text-muted">{{ trans_choice('shop.admin.available_variants', $availableThemesCount, ['count' => $availableThemesCount]) }}</div></div></div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="fw-semibold">{{ __('shop.admin.storefront_theme_management') }}</div>
            <div class="text-muted small">{{ __('shop.admin.theme_saved_info') }}</div>
        </div>
        <a href="{{ route('admin.themes.index') }}" class="btn btn-outline-primary">{{ __('buttons.manage_themes') }}</a>
    </div>
</div>

<div class="card">
    <div class="card-header">{{ __('shop.admin.latest_orders') }}</div>
    <div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead><tr><th>{{ __('checkout.order_number') }}</th><th>{{ __('shop.order_status') }}</th><th>{{ __('shop.total') }}</th><th>{{ __('shop.date') }}</th><th></th></tr></thead>
            <tbody>
            @forelse ($latestOrders as $order)
                <tr>
                    <td>{{ $order->number }}</td>
                    <td>{{ $order->status }}</td>
                    <td>{{ number_format($order->price_gross, 2, ',', ' ') }} {{ $order->currency }}</td>
                    <td>{{ $order->placed_at?->format('d.m.Y H:i') }}</td>
                    <td><a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">{{ __('buttons.detail') }}</a></td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted">{{ __('messages.no_orders') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
