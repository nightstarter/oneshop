@php
    $primaryImage = $product->productImages->firstWhere('is_primary', true) ?? $product->productImages->first();
    $imageUrl = $primaryImage
        ? route('product-images.show', ['mediaFile' => $primaryImage->mediaFile, 'variant' => 'thumb'])
        : route('product-images.placeholder', ['variant' => 'thumb']);
    $imageAlt = $primaryImage?->alt ?: $product->name;
    $price = app(\App\Services\PriceCalculator::class)->calculate($product, auth()->user()?->customer);
@endphp

<article class="legacy-card d-flex flex-column">
    <a href="{{ route('products.show', $product->slug) }}" class="text-decoration-none text-dark d-block p-3 pb-2">
        <h3 class="h3 mb-3" style="font-weight:500; line-height:1.2; min-height:86px;">{{ $product->name }}</h3>

        <div class="text-center mb-3" style="min-height:170px;">
            <img src="{{ $imageUrl }}" alt="{{ $imageAlt }}" class="img-fluid" style="max-height: 165px; width: auto;">
        </div>

        <div class="text-muted mb-2" style="font-size:1.35rem; min-height:66px;">
            @if ($product->description)
                {{ \Illuminate\Support\Str::limit(strip_tags($product->description), 60) }}
            @else
                {{ __('shop.sku') }}: {{ $product->sku }}
            @endif
        </div>

        <div class="d-flex align-items-end justify-content-between flex-wrap gap-2">
            <div>
                <div class="legacy-price-main">{{ number_format($price['unit_gross'], 0, ',', ' ') }} {{ config('shop.currency') }}</div>
                <div class="text-muted" style="font-size:1.05rem;">{{ number_format($price['unit_net'], 2, '.', ' ') }} {{ __('shop.price_excluding_vat') }}</div>
            </div>
        </div>
    </a>

    <div class="mt-auto p-3 pt-0 d-grid">
        <form action="{{ route('cart.add') }}" method="POST" class="m-0">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <input type="hidden" name="quantity" value="1">
            <button class="btn legacy-button legacy-button-primary btn-lg w-100" type="submit">
                KOUPIT
            </button>
        </form>
    </div>
</article>
