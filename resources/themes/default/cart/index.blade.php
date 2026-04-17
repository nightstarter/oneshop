@extends('theme::layouts.app')

@section('title', __('shop.cart') . ' - ' . config('app.name'))

@section('content')
    <h1 class="h3 mb-4"><i class="bi bi-cart3 me-2"></i>{{ __('shop.cart') }}</h1>

    @if ($items->isEmpty())
        <div class="text-center py-5">
            <i class="bi bi-cart-x display-1 text-muted"></i>
            <p class="mt-3 text-muted fs-5">{{ __('messages.cart_empty') }}</p>
            <a href="{{ route('products.index') }}" class="btn btn-primary mt-2">{{ __('buttons.go_to_products') }}</a>
        </div>
    @else
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <table class="table mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('shop.product') }}</th>
                                    <th class="text-end" style="width:110px">{{ __('shop.price_per_item') }}</th>
                                    <th class="text-center" style="width:130px">{{ __('shop.quantity') }}</th>
                                    <th class="text-end" style="width:120px">{{ __('shop.total') }}</th>
                                    <th style="width:50px"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($items as $item)
                                    <tr>
                                        <td>
                                            <a href="{{ route('products.show', $item->product->slug) }}"
                                               class="text-decoration-none fw-semibold">{{ $item->product->name }}</a>
                                            <div class="text-muted small">{{ __('shop.sku') }}: {{ $item->product->sku }}</div>
                                        </td>
                                        <td class="text-end">
                                            <div class="fw-semibold">{{ number_format($item->unit_gross, 2, ',', ' ') }} {{ config('shop.currency') }}</div>
                                            <div class="text-muted small">{{ __('shop.price_excluding_vat') }}: {{ number_format($item->unit_net, 2, ',', ' ') }}</div>
                                        </td>
                                        <td class="text-center">
                                            <form action="{{ route('cart.update') }}" method="POST" class="d-flex align-items-center justify-content-center gap-1">
                                                @csrf
                                                <input type="hidden" name="product_id" value="{{ $item->product->id }}">
                                                <input type="number" name="quantity" value="{{ $item->quantity }}"
                                                       min="0" class="form-control form-control-sm text-center" style="width:70px">
                                                <button class="btn btn-outline-secondary btn-sm" title="{{ __('buttons.update') }}">
                                                    <i class="bi bi-arrow-clockwise"></i>
                                                </button>
                                            </form>
                                        </td>
                                        <td class="text-end fw-bold">
                                            {{ number_format($item->total_gross, 2, ',', ' ') }} {{ config('shop.currency') }}
                                        </td>
                                        <td class="text-center">
                                            <form action="{{ route('cart.remove') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="product_id" value="{{ $item->product->id }}">
                                                <button class="btn btn-link text-danger p-0" title="{{ __('buttons.remove') }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header fw-bold">{{ __('checkout.cart_summary') }}</div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">{{ __('shop.price_net') }}</span>
                            <span>{{ number_format($totals['net'], 2, ',', ' ') }} {{ config('shop.currency') }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">{{ __('shop.vat_label', ['rate' => config('shop.vat_rate')]) }}</span>
                            <span>{{ number_format($totals['vat'], 2, ',', ' ') }} {{ config('shop.currency') }}</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3 fw-bold fs-5">
                            <span>{{ __('shop.total') }}</span>
                            <span class="text-danger">{{ number_format($totals['gross'], 2, ',', ' ') }} {{ config('shop.currency') }}</span>
                        </div>
                        <a href="{{ route('checkout.index') }}" class="btn btn-success w-100 btn-lg">
                            <i class="bi bi-credit-card me-1"></i>{{ __('buttons.continue_to_checkout') }}
                        </a>
                        <a href="{{ route('products.index') }}" class="btn btn-link w-100 mt-1 text-muted small">
                            {{ __('buttons.continue_shopping') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection