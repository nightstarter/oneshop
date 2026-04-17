@extends('theme::layouts.app')

@section('title', __('shop.homepage') . ' - ' . config('app.name'))

@section('content')
    <section class="mono-shell p-4 p-lg-5 mb-4 mb-lg-5">
        <div class="row g-4 align-items-end">
            <div class="col-lg-8">
                <div class="small text-uppercase mono-muted mb-3">{{ __('shop.theme') }}</div>
                <h1 class="display-4 mb-3">{{ __('shop.hero_title') }}</h1>
                <p class="lead mono-muted mb-4">{{ __('messages.theme_runtime') }}</p>
                <a href="{{ route('products.index') }}" class="btn mono-btn btn-lg">{{ __('shop.products') }}</a>
            </div>
            <div class="col-lg-4">
                <div class="mono-card p-4">
                    <div class="small text-uppercase mono-muted mb-2">{{ __('shop.active_theme') }}</div>
                    <ul class="mb-0 ps-3 mono-muted small">
                        <li>{{ __('shop.theme') }}</li>
                        <li>{{ __('shop.homepage') }}</li>
                        <li>{{ __('shop.products') }}</li>
                        <li>{{ __('shop.cart') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <div class="row g-3 g-lg-4 mb-4">
        @foreach ($categories as $category)
            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ route('products.category', $category->slug) }}" class="mono-card d-block p-3 text-decoration-none h-100">
                    <div class="small mono-muted text-uppercase mb-2">{{ __('shop.category') }}</div>
                    <div class="fw-semibold">{{ $category->name }}</div>
                </a>
            </div>
        @endforeach
    </div>

    <section>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0">{{ __('shop.featured_products') }}</h2>
            <span class="mono-muted small">{{ trans_choice('shop.items_count', $featuredProducts->count(), ['count' => $featuredProducts->count()]) }}</span>
        </div>
        <div class="row g-3 g-lg-4">
            @forelse ($featuredProducts as $product)
                <div class="col-12 col-md-6 col-xl-3">
                    @include('theme::products._card', ['product' => $product])
                </div>
            @empty
                <div class="col-12"><div class="mono-card p-4 mono-muted">{{ __('shop.no_products_yet') }}</div></div>
            @endforelse
        </div>
    </section>
@endsection