@php
    $currentPage = $products->currentPage();
    $lastPage = $products->lastPage();
    $startPage = max(1, $currentPage - 1);
    $endPage = min($lastPage, $currentPage + 1);
@endphp

@if ($products->hasPages())
    <nav class="mb-3" aria-label="{{ __('Pagination Navigation') }}">
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

        <div class="text-muted small">
            {{ __('shop.pagination_showing', ['from' => $products->firstItem() ?? 0, 'to' => $products->lastItem() ?? 0, 'total' => $products->total()]) }}
        </div>
    </nav>
@endif

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

@if ($products->hasPages())
    <nav class="mt-4" aria-label="{{ __('Pagination Navigation') }}">
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

        <div class="text-muted small">
            {{ __('shop.pagination_showing', ['from' => $products->firstItem() ?? 0, 'to' => $products->lastItem() ?? 0, 'total' => $products->total()]) }}
        </div>
    </nav>
@endif
