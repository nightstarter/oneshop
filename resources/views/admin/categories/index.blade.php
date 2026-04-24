@extends('admin.layout')

@section('title', __('shop.admin.categories'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">{{ __('shop.admin.categories') }}</h1>
    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">{{ __('shop.admin.new_category') }}</a>
</div>

<div id="admin-categories-listing" data-admin-ajax-pagination>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped mb-0 align-middle">
                <thead><tr><th>ID</th><th>{{ __('forms.name') }}</th><th>{{ __('forms.slug') }}</th><th>{{ __('shop.admin.parent_category') }}</th><th>{{ __('shop.admin.sort_order') }}</th><th>{{ __('shop.admin.active') }}</th><th></th></tr></thead>
                <tbody>
                @foreach($categories as $category)
                    <tr>
                        <td>{{ $category->id }}</td>
                        <td>{{ $category->name }}</td>
                        <td>{{ $category->slug }}</td>
                        <td>{{ $category->parent?->name ?? '-' }}</td>
                        <td>{{ $category->sort_order }}</td>
                        <td>{!! $category->is_active ? '<span class="badge bg-success">' . __('shop.admin.yes') . '</span>' : '<span class="badge bg-secondary">' . __('shop.admin.no') . '</span>' !!}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-sm btn-outline-primary">{{ __('buttons.edit') }}</a>
                            <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" onclick="return confirm('{{ __('messages.delete_category_confirm') }}')">{{ __('buttons.delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $categories->links() }}</div>
</div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var listingRoot = document.getElementById('admin-categories-listing');
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
                    var nextListing = doc.getElementById('admin-categories-listing');

                    if (!nextListing) {
                        window.location.href = url;
                        return;
                    }

                    listingRoot.innerHTML = nextListing.innerHTML;
                    if (pushState) {
                        window.history.pushState({ adminCategoriesPagination: true }, '', url);
                    }
                })
                .catch(function () {
                    window.location.href = url;
                });
            };

            document.addEventListener('click', function (event) {
                var link = event.target.closest('#admin-categories-listing .pagination a');
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
