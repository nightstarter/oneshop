@extends('admin.layout')

@section('title', __('shop.order_detail', ['number' => $order->number]))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">{{ __('shop.order_detail', ['number' => $order->number]) }}</h1>
    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">{{ __('buttons.back') }}</a>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">{{ __('shop.admin.order_items') }}</div>
            <div class="table-responsive">
                <table class="table table-striped mb-0 align-middle">
                    <thead><tr><th>{{ __('shop.product') }}</th><th>{{ __('shop.sku') }}</th><th>{{ __('shop.quantity') }}</th><th>{{ __('shop.price_per_item') }}</th><th>{{ __('shop.total') }}</th></tr></thead>
                    <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td>{{ $item->product_name }}</td>
                            <td>{{ $item->sku }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ number_format($item->unit_price_gross, 2, ',', ' ') }} {{ $order->currency }}</td>
                            <td>{{ number_format($item->total_price_gross, 2, ',', ' ') }} {{ $order->currency }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header">{{ __('shop.admin.order_status_card') }}</div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.orders.update-status', $order) }}">
                    @csrf
                    @method('PATCH')
                    <div class="mb-3">
                        <label class="form-label">{{ __('forms.status') }}</label>
                        <select name="status" class="form-select">
                            @foreach(['pending','confirmed','processing','shipped','delivered','cancelled'] as $status)
                                <option value="{{ $status }}" @selected(old('status', $order->status) === $status)>{{ __('shop.status_' . $status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('forms.note') }}</label>
                        <textarea name="note" class="form-control" rows="3">{{ old('note', $order->note) }}</textarea>
                    </div>
                    <button class="btn btn-primary w-100">{{ __('buttons.save') }}</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">{{ __('shop.admin.order_summary') }}</div>
            <div class="card-body">
                <p class="mb-1"><strong>{{ __('shop.admin.shipping_method') }}:</strong> {{ $order->shipping_name ?? '-' }} ({{ number_format((float) $order->shipping_price_gross, 2, ',', ' ') }} {{ $order->currency }})</p>
                <p class="mb-1"><strong>{{ __('shop.admin.payment_method') }}:</strong> {{ $order->payment_name ?? '-' }} ({{ number_format((float) $order->payment_price_gross, 2, ',', ' ') }} {{ $order->currency }})</p>
                @if ($order->pickup_point_name)
                    <p class="mb-1"><strong>{{ __('shop.admin.pickup_point') }}:</strong> {{ $order->pickup_point_name }} ({{ $order->pickup_point_address }})</p>
                @endif
                <hr>
                <p class="mb-1"><strong>{{ __('shop.price_net') }}:</strong> {{ number_format($order->price_net, 2, ',', ' ') }} {{ $order->currency }}</p>
                <p class="mb-1"><strong>{{ __('shop.vat') }}:</strong> {{ number_format($order->price_vat, 2, ',', ' ') }} {{ $order->currency }}</p>
                <p class="mb-0"><strong>{{ __('shop.total') }}:</strong> {{ number_format($order->price_gross, 2, ',', ' ') }} {{ $order->currency }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
