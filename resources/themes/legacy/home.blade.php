@extends('theme::layouts.app')

@section('title', __('shop.homepage') . ' - ' . config('app.name'))

@section('content')
    <div class="legacy-breadcrumb d-flex align-items-center gap-2">
        <i class="bi bi-house-fill small"></i>
        <span>{{ __('shop.home') }}</span>
    </div>

    @php
        $slides = $featuredProducts->take(3)->values();
    @endphp

    <section class="legacy-section-box p-2 mb-4 overflow-hidden">
        @if ($slides->isNotEmpty())
            <div id="legacyHomeCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-indicators">
                    @foreach ($slides as $slide)
                        <button
                            type="button"
                            data-bs-target="#legacyHomeCarousel"
                            data-bs-slide-to="{{ $loop->index }}"
                            class="{{ $loop->first ? 'active' : '' }}"
                            @if ($loop->first) aria-current="true" @endif
                            aria-label="Slide {{ $loop->iteration }}"
                        ></button>
                    @endforeach
                </div>

                <div class="carousel-inner" style="max-height: 470px;">
                    @foreach ($slides as $slide)
                        @php
                            $slideImage = $slide->productImages->firstWhere('is_primary', true) ?? $slide->productImages->first();
                            $slideImageUrl = $slideImage
                                ? route('product-images.show', ['mediaFile' => $slideImage->mediaFile, 'variant' => 'main'])
                                : route('product-images.placeholder', ['variant' => 'main']);
                        @endphp

                        <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                            <a href="{{ route('products.show', $slide->slug) }}" class="text-decoration-none text-dark d-block">
                                <div class="position-relative" style="background: radial-gradient(circle at 70% 35%, rgba(255,255,255,.5), transparent 45%), linear-gradient(130deg, #efac13 0%, #f8d13f 48%, #e9a113 100%); min-height: 430px;">
                                    <div class="row align-items-center g-0 h-100">
                                        <div class="col-lg-8 p-4 p-lg-5">
                                            <h1 class="display-4 mb-3" style="text-shadow:0 2px 0 rgba(255,255,255,.55);">{{ $slide->name }}</h1>
                                            <p class="fs-3 mb-4">{{ __('shop.hero_title') }}</p>
                                            <span class="btn legacy-button legacy-button-primary btn-lg">{{ __('buttons.browse_products') }}</span>
                                        </div>
                                        <div class="col-lg-4 text-center p-4">
                                            <img src="{{ $slideImageUrl }}" alt="{{ $slide->name }}" class="img-fluid" style="max-height: 360px; width: auto; filter: drop-shadow(0 10px 16px rgba(0,0,0,.3));">
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>

                <button class="carousel-control-prev" type="button" data-bs-target="#legacyHomeCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#legacyHomeCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        @else
            <div class="p-5 text-center">
                <h2>{{ __('shop.no_products_yet') }}</h2>
            </div>
        @endif
    </section>

    @if ($categories->isNotEmpty())
        <section class="legacy-section-box p-3 p-lg-4 mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <h2 class="h1 mb-0">{{ __('shop.categories') }}</h2>
                <span class="text-muted">{{ trans_choice('shop.items_count', $categories->count(), ['count' => $categories->count()]) }}</span>
            </div>
            <div class="row g-2 g-lg-3">
                @foreach ($categories as $category)
                    <div class="col-6 col-md-4 col-lg-2">
                        <a href="{{ route('products.category', $category->slug) }}" class="legacy-card d-flex align-items-center justify-content-center text-center text-decoration-none text-dark p-3 h-100">
                            <strong>{{ $category->name }}</strong>
                        </a>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <section>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h1 mb-0">{{ __('shop.featured_products') }}</h2>
            <a href="{{ route('products.index') }}" class="btn legacy-button legacy-button-primary px-4">{{ __('shop.products') }}</a>
        </div>
        <div class="row g-3 g-lg-4">
            @forelse ($featuredProducts as $product)
                <div class="col-12 col-sm-6 col-lg-3">
                    @include('theme::products._card', ['product' => $product])
                </div>
            @empty
                <div class="col-12">
                    <div class="legacy-section-box p-4 text-muted">{{ __('shop.no_products_yet') }}</div>
                </div>
            @endforelse
        </div>
    </section>
@endsection
