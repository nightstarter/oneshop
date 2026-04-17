@extends('theme::layouts.app')

@section('title', ($category ? $category->name : ($searchQuery ? __('shop.search_results') : __('shop.products'))) . ' - ' . config('app.name'))

@section('content')
    <div class="d-flex align-items-center mb-3">
        <h1 class="h3 mb-0">
            @if ($category)
                {{ $category->name }}
            @elseif ($searchQuery)
                {{ __('shop.search_results_for', ['query' => $searchQuery]) }}
            @else
                {{ __('shop.all_products') }}
            @endif
        </h1>
        <span class="ms-3 badge bg-secondary">{{ trans_choice('shop.products_count', $products->total(), ['count' => $products->total()]) }}</span>
    </div>

    <form class="row g-2 mb-4" action="{{ route('products.index') }}" method="GET">
        <div class="col-auto flex-grow-1">
            <input class="form-control" type="search" name="q"
                   placeholder="{{ __('forms.search_by_sku_or_name') }}"
                   value="{{ $searchQuery }}">
        </div>
        <div class="col-auto">
            <button class="btn btn-primary" type="submit"><i class="bi bi-search me-1"></i>{{ __('buttons.search') }}</button>
        </div>
        @if ($searchQuery)
            <div class="col-auto">
                        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">{{ __('buttons.cancel_filter') }}</a>
            </div>
        @endif
    </form>

    <div class="row g-3">
        @forelse ($products as $product)
            <div class="col-6 col-md-4 col-lg-3">
                @include('theme::products._card', ['product' => $product])
            </div>
        @empty
            <div class="col-12">
                <p class="text-muted">{{ __('messages.no_products_found') }}</p>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $products->withQueryString()->links() }}
    </div>
@endsection