@php
    $price = app(\App\Services\PriceCalculator::class)->calculate($product, auth()->user()?->customer);
@endphp

<div class="mono-card h-100 p-3 d-flex flex-column">
    @php
        $primaryImage = $product->productImages->firstWhere('is_primary', true) ?? $product->productImages->first();
        $imageUrl = $primaryImage
            ? route('product-images.show', ['mediaFile' => $primaryImage->mediaFile, 'variant' => 'thumb'])
            : route('product-images.placeholder', ['variant' => 'thumb']);
        $imageAlt = $primaryImage?->alt ?: $product->name;
    @endphp

    <div class="mb-3 border rounded overflow-hidden bg-dark-subtle">
        <img src="{{ $imageUrl }}" alt="{{ $imageAlt }}" class="w-100" style="height: 180px; object-fit: cover;">
    </div>

    <div class="small mono-muted mb-2">{{ $product->sku }}</div>
    <h3 class="h5 mb-3">{{ $product->name }}</h3>
    <div class="mt-auto">
        <div class="fs-4 fw-bold">{{ number_format($price['unit_gross'], 2, ',', ' ') }} {{ config('shop.currency') }}</div>
        <div class="small mono-muted mb-3">{{ __('shop.price_excluding_vat') }} {{ number_format($price['unit_net'], 2, ',', ' ') }} {{ config('shop.currency') }}</div>
        <div class="d-grid gap-2">
            <a href="{{ route('products.show', $product->slug) }}" class="btn btn-outline-light">{{ __('buttons.detail') }}</a>
            <form action="{{ route('cart.add') }}" method="POST">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="quantity" value="1">
                <button class="btn mono-btn w-100" type="submit">{{ __('buttons.add_to_cart') }}</button>
            </form>
        </div>
    </div>
</div>