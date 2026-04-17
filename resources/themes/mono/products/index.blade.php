@extends('theme::layouts.app')

@section('title', ($category ? $category->name : ($searchQuery ? __('shop.search_results') : __('shop.products'))) . ' - ' . config('app.name'))

@section('content')
    <section class="mono-shell p-4 mb-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-end">
            <div>
                <div class="small text-uppercase mono-muted mb-2">{{ __('shop.products') }}</div>
                <h1 class="display-6 mb-0">
                    @if ($category)
                        {{ $category->name }}
                    @elseif ($searchQuery)
                        {{ __('shop.search_results_for', ['query' => $searchQuery]) }}
                    @else
                        {{ __('shop.all_products') }}
                    @endif
                </h1>
            </div>
            <span class="mono-muted">{{ trans_choice('shop.products_count', $products->total(), ['count' => $products->total()]) }}</span>
        </div>
    </section>

    <form class="mono-card p-3 mb-4" action="{{ route('products.index') }}" method="GET">
        <div class="row g-2 align-items-center">
            <div class="col-lg">
                <input class="form-control form-control-lg bg-dark text-white border-secondary" type="search" name="q" value="{{ $searchQuery }}" placeholder="{{ __('forms.search_by_sku_or_name') }}">
            </div>
            <div class="col-lg-auto d-grid d-lg-block">
                <button class="btn mono-btn btn-lg" type="submit">{{ __('buttons.search') }}</button>
            </div>
        </div>
    </form>

    <div class="row g-3 g-lg-4">
        @forelse ($products as $product)
            <div class="col-12 col-md-6 col-xl-3">
                @include('theme::products._card', ['product' => $product])
            </div>
        @empty
            <div class="col-12"><div class="mono-card p-4 mono-muted">{{ __('messages.no_products_found') }}</div></div>
        @endforelse
    </div>

    <div class="mt-4">{{ $products->withQueryString()->links() }}</div>
@endsection