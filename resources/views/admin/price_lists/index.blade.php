@extends('admin.layout')

@section('title', __('shop.admin.price_lists'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">{{ __('shop.admin.price_lists') }}</h1>
    <a href="{{ route('admin.price-lists.create') }}" class="btn btn-primary">{{ __('shop.admin.new_price_list') }}</a>
</div>

<div id="admin-price-lists-listing" data-admin-ajax-pagination>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped mb-0 align-middle">
                <thead><tr><th>ID</th><th>{{ __('forms.name') }}</th><th>{{ __('forms.code') }}</th><th>{{ __('forms.currency') }}</th><th>{{ __('shop.admin.validity') }}</th><th>{{ __('shop.admin.active') }}</th><th></th></tr></thead>
                <tbody>
                @foreach($priceLists as $priceList)
                    <tr>
                        <td>{{ $priceList->id }}</td>
                        <td>{{ $priceList->name }}</td>
                        <td>{{ $priceList->code }}</td>
                        <td>{{ $priceList->currency }}</td>
                        <td>{{ optional($priceList->valid_from)->format('d.m.Y') ?? '-' }} - {{ optional($priceList->valid_to)->format('d.m.Y') ?? '-' }}</td>
                        <td>{!! $priceList->is_active ? '<span class="badge bg-success">' . __('shop.admin.yes') . '</span>' : '<span class="badge bg-secondary">' . __('shop.admin.no') . '</span>' !!}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.price-lists.edit', $priceList) }}" class="btn btn-sm btn-outline-primary">{{ __('buttons.edit') }}</a>
                            <form action="{{ route('admin.price-lists.destroy', $priceList) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" onclick="return confirm('{{ __('messages.delete_price_list_confirm') }}')">{{ __('buttons.delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $priceLists->links() }}</div>
</div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var listingRoot = document.getElementById('admin-price-lists-listing');
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
                    var nextListing = doc.getElementById('admin-price-lists-listing');

                    if (!nextListing) {
                        window.location.href = url;
                        return;
                    }

                    listingRoot.innerHTML = nextListing.innerHTML;
                    if (pushState) {
                        window.history.pushState({ adminPriceListsPagination: true }, '', url);
                    }
                })
                .catch(function () {
                    window.location.href = url;
                });
            };

            document.addEventListener('click', function (event) {
                var link = event.target.closest('#admin-price-lists-listing .pagination a');
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
