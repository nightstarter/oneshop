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

    <div id="mono-product-listing" data-ajax-pagination>
        @include('theme::products._listing', ['products' => $products])
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var listingRoot = document.getElementById('mono-product-listing');
            if (!listingRoot) {
                return;
            }

            var parseAndSwap = function (html) {
                var parser = new DOMParser();
                var doc = parser.parseFromString(html, 'text/html');
                var nextListing = doc.getElementById('mono-product-listing');

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
                        window.history.pushState({ monoProductsPagination: true }, '', url);
                    }
                })
                .catch(function () {
                    window.location.href = url;
                });
            };

            document.addEventListener('click', function (event) {
                var link = event.target.closest('#mono-product-listing .pagination a');
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