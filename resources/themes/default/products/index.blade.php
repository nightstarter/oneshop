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

    <div id="default-product-listing" data-ajax-pagination>
        @include('theme::products._listing', ['products' => $products])
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var listingRoot = document.getElementById('default-product-listing');
            if (!listingRoot) {
                return;
            }

            var parseAndSwap = function (html) {
                var parser = new DOMParser();
                var doc = parser.parseFromString(html, 'text/html');
                var nextListing = doc.getElementById('default-product-listing');

                if (!nextListing) {
                    window.location.reload();
                    return;
                }

                listingRoot.innerHTML = nextListing.innerHTML;
                window.scrollTo({ top: listingRoot.getBoundingClientRect().top + window.scrollY - 120, behavior: 'smooth' });
            };

            var loadPage = function (url, pushState) {
                fetch(url, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    }
                })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Pagination request failed');
                    }
                    return response.text();
                })
                .then(function (html) {
                    parseAndSwap(html);
                    if (pushState) {
                        window.history.pushState({ defaultProductsPagination: true }, '', url);
                    }
                })
                .catch(function () {
                    window.location.href = url;
                });
            };

            document.addEventListener('click', function (event) {
                var link = event.target.closest('#default-product-listing .pagination a');
                if (!link) {
                    return;
                }

                event.preventDefault();
                loadPage(link.href, true);
            });

            window.addEventListener('popstate', function () {
                loadPage(window.location.href, false);
            });
        });
    </script>
@endpush