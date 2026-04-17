@extends('theme::layouts.app')

@section('title', $product->name . ' - ' . config('app.name'))

@section('content')
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('shop.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('products.index') }}">{{ __('shop.products') }}</a></li>
            @foreach ($product->categories->take(1) as $cat)
                <li class="breadcrumb-item">
                    <a href="{{ route('products.category', $cat->slug) }}">{{ $cat->name }}</a>
                </li>
            @endforeach
            <li class="breadcrumb-item active">{{ $product->name }}</li>
        </ol>
    </nav>

    <div class="row g-4">
        <div class="col-md-8">
            <h1 class="h2">{{ $product->name }}</h1>
            <p class="text-muted small mb-1">{{ __('shop.sku') }}: <strong>{{ $product->sku }}</strong></p>

            @if ($product->categories->isNotEmpty())
                <div class="mb-3">
                    @foreach ($product->categories as $cat)
                        <a href="{{ route('products.category', $cat->slug) }}" class="badge bg-secondary text-decoration-none me-1">{{ $cat->name }}</a>
                    @endforeach
                </div>
            @endif

            @if ($product->description)
                <div class="mb-4">{!! nl2br(e($product->description)) !!}</div>
            @endif

            @if ($product->stock_qty > 0)
                <p class="text-success"><i class="bi bi-check-circle me-1"></i>{{ __('shop.in_stock', ['count' => $product->stock_qty]) }}</p>
            @else
                <p class="text-danger"><i class="bi bi-x-circle me-1"></i>{{ __('shop.out_of_stock') }}</p>
            @endif
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="price-gross mb-1">{{ number_format($price['unit_gross'], 2, ',', ' ') }} {{ config('shop.currency') }}</div>
                    <div class="price-net mb-3">
                        {{ __('shop.price_excluding_vat') }}: {{ number_format($price['unit_net'], 2, ',', ' ') }} {{ config('shop.currency') }}
                        <span class="text-muted">({{ __('shop.vat_label', ['rate' => $price['vat_rate']]) }})</span>
                    </div>

                    @if ($product->stock_qty > 0)
                        <form action="{{ route('cart.add') }}" method="POST">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <div class="input-group mb-3">
                                <span class="input-group-text">{{ __('forms.quantity') }}</span>
                                <input type="number" class="form-control" name="quantity" value="1" min="1" max="{{ $product->stock_qty }}">
                            </div>
                            <button class="btn btn-primary w-100" type="submit">
                                <i class="bi bi-cart-plus me-1"></i>{{ __('buttons.add_to_cart_full') }}
                            </button>
                        </form>
                    @else
                        <button class="btn btn-secondary w-100" type="button" disabled>{{ __('shop.out_of_stock') }}</button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if ($related->isNotEmpty())
        <hr class="my-4">
        <h2 class="h5 mb-3">{{ __('shop.related_products') }}</h2>
        <div class="row g-3">
            @foreach ($related as $rel)
                <div class="col-6 col-md-3">
                    @include('theme::products._card', ['product' => $rel])
                </div>
            @endforeach
        </div>
    @endif
@endsection
