@php
    $price = app(\App\Services\PriceCalculator::class)->calculate($product, auth()->user()?->customer);
@endphp

<div class="studio-card h-100 p-3 d-flex flex-column">
    <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
        <div>
            <div class="small text-uppercase studio-muted">{{ $product->sku }}</div>
            <h3 class="h5 mb-0">{{ $product->name }}</h3>
        </div>
        <span class="badge text-bg-light border">{{ $product->categories->first()?->name ?? __('shop.product') }}</span>
    </div>

    <div class="mt-auto">
        <div class="fs-4 fw-semibold">{{ number_format($price['unit_gross'], 2, ',', ' ') }} {{ config('shop.currency') }}</div>
        <div class="small studio-muted mb-3">{{ __('shop.price_excluding_vat') }} {{ number_format($price['unit_net'], 2, ',', ' ') }} {{ config('shop.currency') }}</div>
        <div class="d-flex gap-2">
            <a href="{{ route('products.show', $product->slug) }}" class="btn btn-outline-dark flex-grow-1">{{ __('buttons.detail') }}</a>
            <form action="{{ route('cart.add') }}" method="POST" class="flex-grow-1">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="quantity" value="1">
                <button class="btn studio-btn w-100" type="submit">{{ __('buttons.add_to_cart') }}</button>
            </form>
        </div>
    </div>
</div>