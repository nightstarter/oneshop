@extends('theme::layouts.app')

@section('title', $product->name . ' - ' . config('app.name'))

@section('content')
    <div class="row g-4 g-lg-5 align-items-start">
        <div class="col-lg-7">
            <div class="studio-shell rounded-5 p-4 p-lg-5">
                <div class="small text-uppercase studio-muted mb-2">{{ $product->sku }}</div>
                <h1 class="display-5 mb-3">{{ $product->name }}</h1>

                @if ($product->categories->isNotEmpty())
                    <div class="mb-4 d-flex flex-wrap gap-2">
                        @foreach ($product->categories as $cat)
                            <a href="{{ route('products.category', $cat->slug) }}" class="badge rounded-pill text-bg-light border text-decoration-none text-dark px-3 py-2">{{ $cat->name }}</a>
                        @endforeach
                    </div>
                @endif

                <p class="lead studio-muted mb-4">{{ $product->description ?: __('messages.theme_runtime') }}</p>

                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div class="h2 mb-0">{{ number_format($price['unit_gross'], 2, ',', ' ') }} {{ config('shop.currency') }}</div>
                    <div class="studio-muted">{{ __('shop.price_excluding_vat') }} {{ number_format($price['unit_net'], 2, ',', ' ') }} {{ config('shop.currency') }}</div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="studio-card p-4 p-lg-5 sticky-top" style="top: 2rem;">
                <div class="small text-uppercase studio-muted mb-2">{{ __('shop.order_status') }}</div>
                @if ($product->stock_qty > 0)
                    <div class="h4 mb-3">{{ __('shop.in_stock', ['count' => $product->stock_qty]) }}</div>
                    <form action="{{ route('cart.add') }}" method="POST">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <div class="mb-3">
                            <label class="form-label">{{ __('forms.quantity') }}</label>
                            <input type="number" class="form-control form-control-lg" name="quantity" value="1" min="1" max="{{ $product->stock_qty }}">
                        </div>
                        <button class="btn studio-btn btn-lg w-100" type="submit">{{ __('buttons.add_to_cart_full') }}</button>
                    </form>
                @else
                    <div class="h4 mb-3">{{ __('shop.out_of_stock') }}</div>
                    <button class="btn btn-outline-secondary btn-lg w-100" type="button" disabled>{{ __('shop.out_of_stock') }}</button>
                @endif
            </div>
        </div>
    </div>

    @if ($related->isNotEmpty())
        <section class="mt-5">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="mb-0">{{ __('shop.related_products') }}</h2>
                <span class="small studio-muted">{{ __('shop.featured_products') }}</span>
            </div>
            <div class="row g-3 g-lg-4">
                @foreach ($related as $rel)
                    <div class="col-12 col-md-6 col-xl-3">
                        @include('theme::products._card', ['product' => $rel])
                    </div>
                @endforeach
            </div>
        </section>
    @endif
@endsection