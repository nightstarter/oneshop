@extends('admin.layout')

@section('title', __('shop.admin.pages'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">{{ __('shop.admin.pages') }}</h1>
    <a href="{{ route('admin.pages.create') }}" class="btn btn-primary">{{ __('shop.admin.new_page') }}</a>
</div>

<div id="admin-pages-listing" data-admin-ajax-pagination>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped mb-0 align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>{{ __('forms.title') }}</th>
                        <th>{{ __('forms.slug') }}</th>
                        <th>{{ __('shop.admin.published') }}</th>
                        <th>{{ __('shop.admin.created_at') }}</th>
                        <th>{{ __('shop.admin.updated_at') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($pages as $page)
                    <tr>
                        <td>{{ $page->id }}</td>
                        <td>{{ $page->title }}</td>
                        <td>
                            <code>{{ $page->slug }}</code>
                            @if($page->is_published)
                                <a href="{{ route('pages.show', $page->slug) }}" target="_blank" class="ms-1 text-muted" title="{{ __('shop.admin.view_page') }}">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            @endif
                        </td>
                        <td>
                            {!! $page->is_published
                                ? '<span class="badge bg-success">' . __('shop.admin.yes') . '</span>'
                                : '<span class="badge bg-secondary">' . __('shop.admin.no') . '</span>' !!}
                        </td>
                        <td>{{ $page->created_at->format('d.m.Y H:i') }}</td>
                        <td>{{ $page->updated_at->format('d.m.Y H:i') }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.pages.edit', $page) }}" class="btn btn-sm btn-outline-primary">{{ __('buttons.edit') }}</a>
                            <form action="{{ route('admin.pages.destroy', $page) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" onclick="return confirm('{{ __('messages.delete_page_confirm') }}')">{{ __('buttons.delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">{{ __('messages.no_pages') }}</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $pages->links() }}</div>
</div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var listingRoot = document.getElementById('admin-pages-listing');
            if (!listingRoot) {
                return;
            }

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
                    var parser = new DOMParser();
                    var doc = parser.parseFromString(html, 'text/html');
                    var nextListing = doc.getElementById('admin-pages-listing');

                    if (!nextListing) {
                        window.location.href = url;
                        return;
                    }

                    listingRoot.innerHTML = nextListing.innerHTML;
                    if (pushState) {
                        window.history.pushState({ adminPagesPagination: true }, '', url);
                    }
                })
                .catch(function () {
                    window.location.href = url;
                });
            };

            document.addEventListener('click', function (event) {
                var link = event.target.closest('#admin-pages-listing .pagination a');
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
