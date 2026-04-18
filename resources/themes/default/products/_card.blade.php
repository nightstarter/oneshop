<div class="card h-100 product-card shadow-sm">
    <a href="{{ route('products.show', $product->slug) }}" class="text-decoration-none text-dark">
        @php
            $primaryImage = $product->productImages->firstWhere('is_primary', true) ?? $product->productImages->first();
            $imageUrl = $primaryImage
                ? route('product-images.show', ['mediaFile' => $primaryImage->mediaFile, 'variant' => 'thumb'])
                : route('product-images.placeholder', ['variant' => 'thumb']);
            $imageAlt = $primaryImage?->alt ?: $product->name;
        @endphp

        <div class="ratio ratio-4x3 bg-light border-bottom">
            <img src="{{ $imageUrl }}" alt="{{ $imageAlt }}" class="w-100 h-100" style="object-fit: cover;">
        </div>

        <div class="card-body d-flex flex-column">
            <h6 class="card-title mb-1">{{ $product->name }}</h6>
            <small class="text-muted mb-2">{{ __('shop.sku') }}: {{ $product->sku }}</small>
            @php
                $price = app(\App\Services\PriceCalculator::class)->calculate($product, auth()->user()?->customer);
            @endphp
            <div class="mt-auto">
                <div class="price-gross">{{ number_format($price['unit_gross'], 2, ',', ' ') }} {{ config('shop.currency') }}</div>
                <div class="price-net">{{ __('shop.price_net') }}: {{ number_format($price['unit_net'], 2, ',', ' ') }} {{ config('shop.currency') }}</div>
            </div>
        </div>
    </a>
    <div class="card-footer bg-transparent border-0 pb-3">
        <form action="{{ route('cart.add') }}" method="POST">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <input type="hidden" name="quantity" value="1">
            <button class="btn btn-primary btn-sm w-100">
                <i class="bi bi-cart-plus me-1"></i>{{ __('buttons.add_to_cart') }}
            </button>
        </form>
    </div>
</div>