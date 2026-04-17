@extends('theme::layouts.app')

@section('title', __('shop.homepage') . ' - ' . config('app.name'))

@section('content')
    <section class="studio-shell rounded-5 p-4 p-lg-5 mb-4 mb-lg-5 overflow-hidden position-relative">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <div class="small text-uppercase studio-muted mb-3">{{ __('shop.theme') }}</div>
                <h1 class="display-4 mb-3">{{ __('shop.hero_title') }}</h1>
                <p class="lead studio-muted mb-4">{{ __('messages.theme_runtime') }}</p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('products.index') }}" class="btn studio-btn btn-lg">{{ __('shop.products') }}</a>
                    <a href="#featured" class="btn btn-outline-dark btn-lg">{{ __('shop.featured_products') }}</a>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="studio-card p-4">
                    <div class="small text-uppercase studio-muted mb-2">{{ __('shop.active_theme') }}</div>
                    <div class="h2 mb-3">{{ config('shop.active_theme') }}</div>
                    <p class="studio-muted mb-0">{{ __('messages.theme_runtime') }}</p>
                </div>
            </div>
        </div>
    </section>

    @if ($categories->isNotEmpty())
        <section class="mb-5">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="mb-0">{{ __('shop.categories') }}</h2>
                <span class="small studio-muted">{{ trans_choice('shop.items_count', $categories->count(), ['count' => $categories->count()]) }}</span>
            </div>
            <div class="row g-3">
                @foreach ($categories as $category)
                    <div class="col-6 col-md-4 col-xl-2">
                        <a href="{{ route('products.category', $category->slug) }}" class="studio-card d-block p-3 h-100 text-decoration-none text-dark">
                            <div class="small text-uppercase studio-muted mb-2">{{ __('shop.category') }}</div>
                            <div class="fw-semibold">{{ $category->name }}</div>
                        </a>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <section id="featured">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0">{{ __('shop.featured_products') }}</h2>
            <span class="small studio-muted">{{ trans_choice('shop.items_count', $featuredProducts->count(), ['count' => $featuredProducts->count()]) }}</span>
        </div>
        <div class="row g-3 g-lg-4">
            @forelse ($featuredProducts as $product)
                <div class="col-12 col-md-6 col-xl-3">
                    @include('theme::products._card', ['product' => $product])
                </div>
            @empty
                <div class="col-12">
                    <div class="studio-card p-4 studio-muted">{{ __('shop.no_products_yet') }}</div>
                </div>
            @endforelse
        </div>
    </section>
@endsection