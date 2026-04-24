@extends('theme::layouts.app')

@section('title', ($category ? $category->name : ($searchQuery ? __('shop.search_results') : __('shop.products'))) . ' - ' . config('app.name'))

@section('content')
    <div class="legacy-breadcrumb d-flex align-items-center gap-2">
        <i class="bi bi-house-fill small"></i>
        <a href="{{ route('home') }}" class="text-decoration-none">{{ __('shop.home') }}</a>
        <i class="bi bi-chevron-right small text-muted"></i>
        <span>
            @if ($category)
                {{ $category->name }}
            @elseif ($searchQuery)
                {{ __('shop.search_results') }}
            @else
                {{ __('shop.products') }}
            @endif
        </span>
    </div>

    <section class="legacy-section-box p-3 p-lg-4 mb-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-2">
            <h1 class="display-4 mb-0" style="line-height:1;">
                @if ($category)
                    {{ $category->name }}
                @elseif ($searchQuery)
                    {{ __('shop.search_results_for', ['query' => $searchQuery]) }}
                @else
                    {{ __('shop.all_products') }}
                @endif
            </h1>

            <div class="d-flex align-items-center gap-2" style="font-size:1.85rem;">
                <label for="legacy-sort" class="mb-0">Radit podle:</label>
                <select id="legacy-sort" class="form-select" style="min-width:170px;" onchange="window.location.href=this.value;">
                    <option value="{{ request()->fullUrlWithQuery(['sort' => 'default']) }}" {{ request('sort', 'default') === 'default' ? 'selected' : '' }}>Vychozi</option>
                    <option value="{{ request()->fullUrlWithQuery(['sort' => 'name']) }}" {{ request('sort') === 'name' ? 'selected' : '' }}>Nazev A-Z</option>
                    <option value="{{ request()->fullUrlWithQuery(['sort' => 'price']) }}" {{ request('sort') === 'price' ? 'selected' : '' }}>Cena</option>
                </select>
            </div>
        </div>

        <form action="{{ route('products.index') }}" method="GET" class="mt-3">
            <div class="input-group input-group-lg">
                <input
                    class="form-control"
                    type="search"
                    name="q"
                    value="{{ $searchQuery }}"
                    placeholder="{{ __('forms.search_by_sku_or_name') }}"
                >
                <button class="btn legacy-button legacy-button-primary px-4" type="submit">{{ __('buttons.search') }}</button>
                @if ($searchQuery)
                    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">{{ __('buttons.cancel_filter') }}</a>
                @endif
            </div>
        </form>
    </section>

    <div id="legacy-product-listing" data-ajax-pagination>
        @include('theme::products._listing', ['products' => $products])
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var listingRoot = document.getElementById('legacy-product-listing');
            if (!listingRoot) {
                return;
            }

            var parseAndSwap = function (html) {
                var parser = new DOMParser();
                var doc = parser.parseFromString(html, 'text/html');
                var nextListing = doc.getElementById('legacy-product-listing');

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
                        window.history.pushState({ legacyProductsPagination: true }, '', url);
                    }
                })
                .catch(function () {
                    window.location.href = url;
                });
            };

            document.addEventListener('click', function (event) {
                var link = event.target.closest('#legacy-product-listing .pagination a');
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
