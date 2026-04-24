@extends('theme::layouts.app')

@section('title', $product->name . ' - ' . config('app.name'))

@section('content')
    @php
        $galleryImages = $product->productImages;
        $mainImage = $galleryImages->firstWhere('is_primary', true) ?? $galleryImages->first();
    @endphp

    <div class="legacy-breadcrumb d-flex align-items-center gap-2">
        <i class="bi bi-house-fill small"></i>
        <a href="{{ route('home') }}" class="text-decoration-none">{{ __('shop.home') }}</a>
        <i class="bi bi-chevron-right small text-muted"></i>
        @foreach ($product->categories->take(1) as $cat)
            <a href="{{ route('products.category', $cat->slug) }}" class="text-decoration-none">{{ $cat->name }}</a>
            <i class="bi bi-chevron-right small text-muted"></i>
        @endforeach
        <span>{{ $product->name }}</span>
    </div>

    <h1 class="display-2 mb-4" style="line-height:1.05;">{{ $product->name }}</h1>

    <div class="row g-4 g-xl-5 align-items-start mb-4">
        <div class="col-lg-4">
            <div class="legacy-card p-3 text-center">
                <img
                    id="product-main-image"
                    src="{{ $mainImage ? route('product-images.show', ['mediaFile' => $mainImage->mediaFile, 'variant' => 'main']) : route('product-images.placeholder', ['variant' => 'main']) }}"
                    alt="{{ $mainImage?->alt ?: $product->name }}"
                    class="img-fluid"
                    style="max-height: 360px; width:auto;"
                >
            </div>

            @if ($galleryImages->count() > 1)
                <div class="d-flex gap-2 mt-3 flex-wrap">
                    @foreach ($galleryImages as $image)
                        <button
                            type="button"
                            class="btn btn-light border p-1 product-thumb"
                            data-image-url="{{ route('product-images.show', ['mediaFile' => $image->mediaFile, 'variant' => 'main']) }}"
                            data-image-alt="{{ $image->alt ?: $product->name }}"
                        >
                            <img
                                src="{{ route('product-images.show', ['mediaFile' => $image->mediaFile, 'variant' => 'thumb']) }}"
                                alt="{{ $image->alt ?: $product->name }}"
                                style="width: 86px; height: 74px; object-fit: cover;"
                            >
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="col-lg-8">
            <div class="legacy-section-box p-3 p-lg-4">
                <div class="row g-3" style="font-size: 1.95rem;">
                    <div class="col-md-6 text-muted">Kod zbozi:</div>
                    <div class="col-md-6">{{ $product->sku }}</div>

                    <div class="col-md-6 text-muted">Dostupnost:</div>
                    <div class="col-md-6">
                        @if ($product->available_quantity > 0)
                            <span class="legacy-stock">Skladem <i class="bi bi-info-circle-fill"></i></span>
                        @else
                            <span class="text-danger fw-bold">{{ __('shop.out_of_stock') }}</span>
                        @endif
                    </div>

                    <div class="col-md-6 text-muted">Predpokladane datum doruceni</div>
                    <div class="col-md-6 fw-semibold">{{ now()->addDays(4)->translatedFormat('j. F') }}</div>

                    <div class="col-md-6 text-muted">Cena s DPH</div>
                    <div class="col-md-6"><span class="legacy-price-tag">{{ number_format($price['unit_gross'], 0, ',', ' ') }} {{ config('shop.currency') }}</span></div>
                </div>

                <div class="legacy-flash">Doprava zdarma !</div>

                <div class="row align-items-center g-3 mt-2">
                    <div class="col-md-5 col-xl-4">
                        @if ($product->available_quantity > 0)
                            <form action="{{ route('cart.add') }}" method="POST">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <div class="input-group input-group-lg">
                                    <button class="btn btn-outline-secondary" type="button" data-qty-step="-1">-</button>
                                    <input type="number" class="form-control text-center" name="quantity" value="1" min="1" max="{{ $product->available_quantity }}" data-qty-input>
                                    <button class="btn btn-outline-secondary" type="button" data-qty-step="1">+</button>
                                </div>
                            </form>
                        @endif
                    </div>
                    <div class="col-md-7 col-xl-8">
                        @if ($product->available_quantity > 0)
                            <button class="btn legacy-button legacy-button-primary btn-lg w-100" type="button" data-submit-add>
                                DO KOSIKU
                            </button>
                        @else
                            <button class="btn btn-secondary btn-lg w-100" type="button" disabled>{{ __('shop.out_of_stock') }}</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($parameterValues->isNotEmpty())
        <section class="mb-4">
            <div class="legacy-section-box p-3 p-lg-4 mb-2">
                <h2 class="h2 mb-0"><i class="bi bi-arrow-right-circle-fill text-primary me-2"></i>Technicke parametry</h2>
            </div>
            @include('theme::products._parameters', ['parameterValues' => $parameterValues])
        </section>
    @endif

    @if ($carrier->deviceModels->isNotEmpty() || $carrier->partNumbers->isNotEmpty())
        <section class="mb-4">
            <div class="legacy-section-box p-3 p-lg-4 mb-2">
                <h2 class="h2 mb-0"><i class="bi bi-arrow-right-circle-fill text-primary me-2"></i>Kompatibilita</h2>
            </div>
            <div class="legacy-section-box p-3 p-lg-4">
                <div class="mb-4 text-center">
                    <input type="search" class="form-control form-control-lg mx-auto" style="max-width: 360px;" placeholder="Vyhledat kompatibilitu" data-compat-search>
                </div>

                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-3" data-compat-grid>
                    @foreach ($carrier->deviceModels->sortBy('model_name') as $deviceModel)
                        <div class="col" data-compat-item>
                            <div class="small" style="font-size:1.25rem;">{{ $deviceModel->brand ? $deviceModel->brand . ' ' : '' }}{{ $deviceModel->model_name }}</div>
                        </div>
                    @endforeach
                    @foreach ($carrier->partNumbers->sortBy('value') as $partNumber)
                        <div class="col" data-compat-item>
                            <div class="small" style="font-size:1.25rem;">{{ $partNumber->value }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($related->isNotEmpty())
        <section class="mt-5">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h1 mb-0">{{ __('shop.related_products') }}</h2>
                <span class="text-muted">{{ trans_choice('shop.items_count', $related->count(), ['count' => $related->count()]) }}</span>
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

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var addButton = document.querySelector('[data-submit-add]');
            var addForm = document.querySelector('form[action="{{ route('cart.add') }}"]');

            if (addButton && addForm) {
                addButton.addEventListener('click', function () {
                    addForm.submit();
                });
            }

            document.querySelectorAll('[data-qty-step]').forEach(function (button) {
                button.addEventListener('click', function () {
                    var input = button.closest('.input-group').querySelector('[data-qty-input]');
                    if (!input) {
                        return;
                    }

                    var current = parseInt(input.value || '1', 10);
                    var min = parseInt(input.min || '1', 10);
                    var max = parseInt(input.max || '9999', 10);
                    var next = current + parseInt(button.getAttribute('data-qty-step'), 10);

                    if (next < min) {
                        next = min;
                    }
                    if (next > max) {
                        next = max;
                    }

                    input.value = String(next);
                });
            });

            var mainImage = document.getElementById('product-main-image');
            if (mainImage) {
                document.querySelectorAll('.product-thumb').forEach(function (thumb) {
                    var switchImage = function () {
                        var url = thumb.dataset.imageUrl;
                        var alt = thumb.dataset.imageAlt;

                        if (!url) {
                            return;
                        }

                        mainImage.src = url;
                        if (alt) {
                            mainImage.alt = alt;
                        }
                    };

                    thumb.addEventListener('click', switchImage);
                    thumb.addEventListener('mouseenter', switchImage);
                });
            }

            var compatSearch = document.querySelector('[data-compat-search]');
            if (compatSearch) {
                compatSearch.addEventListener('input', function () {
                    var needle = compatSearch.value.toLowerCase().trim();
                    document.querySelectorAll('[data-compat-item]').forEach(function (item) {
                        var text = item.textContent.toLowerCase();
                        item.style.display = needle === '' || text.indexOf(needle) !== -1 ? '' : 'none';
                    });
                });
            }
        });
    </script>
@endpush
