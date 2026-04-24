@php
    $currentPage = $products->currentPage();
    $lastPage = $products->lastPage();
    $startPage = max(1, $currentPage - 1);
    $endPage = min($lastPage, $currentPage + 1);
@endphp

@if ($products->hasPages())
    <nav class="legacy-pagination mb-3" aria-label="{{ __('Pagination Navigation') }}">
        <ul class="pagination pagination-sm mb-2">
            <li class="page-item {{ $products->onFirstPage() ? 'disabled' : '' }}">
                @if ($products->onFirstPage())
                    <span class="page-link" aria-disabled="true">&laquo; {{ __('pagination.previous') }}</span>
                @else
                    <a class="page-link" href="{{ $products->previousPageUrl() }}" rel="prev">&laquo; {{ __('pagination.previous') }}</a>
                @endif
            </li>

            @for ($page = $startPage; $page <= $endPage; $page++)
                <li class="page-item {{ $page === $currentPage ? 'active' : '' }}">
                    @if ($page === $currentPage)
                        <span class="page-link">{{ $page }}</span>
                    @else
                        <a class="page-link" href="{{ $products->url($page) }}">{{ $page }}</a>
                    @endif
                </li>
            @endfor

            <li class="page-item {{ $products->hasMorePages() ? '' : 'disabled' }}">
                @if ($products->hasMorePages())
                    <a class="page-link" href="{{ $products->nextPageUrl() }}" rel="next">{{ __('pagination.next') }} &raquo;</a>
                @else
                    <span class="page-link" aria-disabled="true">{{ __('pagination.next') }} &raquo;</span>
                @endif
            </li>
        </ul>

        <div class="legacy-pagination-summary text-muted small">
            {{ __('shop.pagination_showing', ['from' => $products->firstItem() ?? 0, 'to' => $products->lastItem() ?? 0, 'total' => $products->total()]) }}
        </div>
    </nav>
@endif

<div class="row g-3 g-lg-4" data-products-grid>
    @forelse ($products as $product)
        <div class="col-12 col-md-6 col-xl-3">
            @include('theme::products._card', ['product' => $product])
        </div>
    @empty
        <div class="col-12">
            <div class="legacy-section-box p-4 text-muted">{{ __('messages.no_products_found') }}</div>
        </div>
    @endforelse
</div>

@if ($products->hasPages())
    <nav class="legacy-pagination mt-4" aria-label="{{ __('Pagination Navigation') }}">
        <ul class="pagination pagination-sm mb-2">
            <li class="page-item {{ $products->onFirstPage() ? 'disabled' : '' }}">
                @if ($products->onFirstPage())
                    <span class="page-link" aria-disabled="true">&laquo; {{ __('pagination.previous') }}</span>
                @else
                    <a class="page-link" href="{{ $products->previousPageUrl() }}" rel="prev">&laquo; {{ __('pagination.previous') }}</a>
                @endif
            </li>

            @for ($page = $startPage; $page <= $endPage; $page++)
                <li class="page-item {{ $page === $currentPage ? 'active' : '' }}">
                    @if ($page === $currentPage)
                        <span class="page-link">{{ $page }}</span>
                    @else
                        <a class="page-link" href="{{ $products->url($page) }}">{{ $page }}</a>
                    @endif
                </li>
            @endfor

            <li class="page-item {{ $products->hasMorePages() ? '' : 'disabled' }}">
                @if ($products->hasMorePages())
                    <a class="page-link" href="{{ $products->nextPageUrl() }}" rel="next">{{ __('pagination.next') }} &raquo;</a>
                @else
                    <span class="page-link" aria-disabled="true">{{ __('pagination.next') }} &raquo;</span>
                @endif
            </li>
        </ul>

        <div class="legacy-pagination-summary text-muted small">
            {{ __('shop.pagination_showing', ['from' => $products->firstItem() ?? 0, 'to' => $products->lastItem() ?? 0, 'total' => $products->total()]) }}
        </div>
    </nav>
@endif
