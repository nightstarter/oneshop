@extends('theme::layouts.app')

@section('title', __('shop.home') . ' - ' . config('app.name'))

@section('content')
    <div class="p-5 mb-4 bg-dark text-white rounded-3">
        <div class="container-fluid py-3">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                <div>
                    <h1 class="display-5 fw-bold"><i class="bi bi-shop me-2"></i>{{ config('app.name') }}</h1>
                    <p class="col-md-8 fs-4 mb-0">{{ __('shop.hero_title') }}</p>
                </div>
                <span class="badge text-bg-light text-dark">{{ __('shop.default_theme_badge') }}</span>
            </div>
            <a class="btn btn-danger btn-lg mt-4" href="{{ route('products.index') }}">
                {{ __('buttons.browse_products') }} <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>

    @if ($categories->isNotEmpty())
        <h2 class="h4 mb-3">{{ __('shop.categories') }}</h2>
        <div class="row g-3 mb-5">
            @foreach ($categories as $category)
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="{{ route('products.category', $category->slug) }}"
                       class="card text-decoration-none text-center h-100 product-card shadow-sm">
                        <div class="card-body">
                            <i class="bi bi-tag fs-2 text-secondary"></i>
                            <p class="mt-2 mb-0 fw-semibold small">{{ $category->name }}</p>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4 mb-0">{{ __('shop.featured_products') }}</h2>
        <span class="text-muted small">{{ trans_choice('shop.items_count', $featuredProducts->count(), ['count' => $featuredProducts->count()]) }}</span>
    </div>
    <div class="row g-3">
        @forelse ($featuredProducts as $product)
            <div class="col-6 col-md-4 col-lg-3">
                @include('theme::products._card', ['product' => $product])
            </div>
        @empty
            <p class="text-muted">{{ __('shop.no_products_yet') }}</p>
        @endforelse
    </div>
@endsection
