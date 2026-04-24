@extends('admin.layout')

@section('title', __('shop.admin.payment_methods'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">{{ __('shop.admin.payment_methods') }}</h1>
    <a href="{{ route('admin.payment-methods.create') }}" class="btn btn-primary">{{ __('shop.admin.new_payment_method') }}</a>
</div>

<div id="admin-payment-methods-listing" data-admin-ajax-pagination>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped mb-0 align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>{{ __('shop.admin.code') }}</th>
                        <th>{{ __('forms.name') }}</th>
                        <th>{{ __('shop.admin.provider') }}</th>
                        <th>{{ __('shop.admin.type') }}</th>
                        <th>{{ __('shop.admin.price_gross') }}</th>
                        <th>{{ __('shop.admin.compatible_shipping_methods') }}</th>
                        <th>{{ __('shop.admin.active') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($paymentMethods as $method)
                    <tr>
                        <td>{{ $method->id }}</td>
                        <td>{{ $method->code }}</td>
                        <td>{{ $method->name }}</td>
                        <td>{{ $method->provider_code }}</td>
                        <td>{{ $method->type }}</td>
                        <td>{{ number_format($method->price_gross, 2, ',', ' ') }} {{ config('shop.currency') }}</td>
                        <td>{{ $method->shippingMethods->pluck('name')->join(', ') }}</td>
                        <td>{!! $method->is_active ? '<span class="badge bg-success">' . __('shop.admin.yes') . '</span>' : '<span class="badge bg-secondary">' . __('shop.admin.no') . '</span>' !!}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.payment-methods.edit', $method) }}" class="btn btn-sm btn-outline-primary">{{ __('buttons.edit') }}</a>
                            <form action="{{ route('admin.payment-methods.destroy', $method) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('{{ __('messages.delete_payment_confirm') }}')">{{ __('buttons.delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $paymentMethods->links() }}</div>
</div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var listingRoot = document.getElementById('admin-payment-methods-listing');
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
                    var nextListing = doc.getElementById('admin-payment-methods-listing');

                    if (!nextListing) {
                        window.location.href = url;
                        return;
                    }

                    listingRoot.innerHTML = nextListing.innerHTML;
                    if (pushState) {
                        window.history.pushState({ adminPaymentMethodsPagination: true }, '', url);
                    }
                })
                .catch(function () {
                    window.location.href = url;
                });
            };

            document.addEventListener('click', function (event) {
                var link = event.target.closest('#admin-payment-methods-listing .pagination a');
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
