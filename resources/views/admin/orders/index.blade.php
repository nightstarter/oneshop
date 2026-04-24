@extends('admin.layout')

@section('title', __('shop.admin.orders'))

@section('content')
<h1 class="h3 mb-3">{{ __('shop.admin.orders') }}</h1>

<div id="admin-orders-listing" data-admin-ajax-pagination>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped mb-0 align-middle">
                <thead><tr><th>{{ __('shop.admin.order_number') }}</th><th>{{ __('shop.admin.customer') }}</th><th>{{ __('forms.status') }}</th><th>{{ __('shop.total') }}</th><th>{{ __('shop.date') }}</th><th></th></tr></thead>
                <tbody>
                @foreach($orders as $order)
                    <tr>
                        <td>{{ $order->number }}</td>
                        <td>{{ $order->customer?->first_name }} {{ $order->customer?->last_name }}</td>
                        <td>{{ __('shop.status_' . $order->status) }}</td>
                        <td>{{ number_format($order->price_gross, 2, ',', ' ') }} {{ $order->currency }}</td>
                        <td>{{ $order->placed_at?->format('d.m.Y H:i') }}</td>
                        <td class="text-end"><a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">{{ __('buttons.detail') }}</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $orders->links() }}</div>
</div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var listingRoot = document.getElementById('admin-orders-listing');
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
                    var nextListing = doc.getElementById('admin-orders-listing');

                    if (!nextListing) {
                        window.location.href = url;
                        return;
                    }

                    listingRoot.innerHTML = nextListing.innerHTML;
                    if (pushState) {
                        window.history.pushState({ adminOrdersPagination: true }, '', url);
                    }
                })
                .catch(function () {
                    window.location.href = url;
                });
            };

            document.addEventListener('click', function (event) {
                var link = event.target.closest('#admin-orders-listing .pagination a');
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
