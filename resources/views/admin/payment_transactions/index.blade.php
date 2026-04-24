@extends('admin.layout')

@section('title', __('shop.admin.payment_transactions'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">{{ __('shop.admin.payment_transactions') }}</h1>
</div>

<form method="GET" class="card mb-3">
    <div class="card-body row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label">{{ __('forms.status') }}</label>
            <select name="status" class="form-select">
                <option value="">{{ __('shop.admin.filter_status_all') }}</option>
                @foreach(['pending','paid','failed'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">{{ __('forms.provider') }}</label>
            <input name="provider_code" class="form-control" value="{{ request('provider_code') }}" placeholder="comgate">
        </div>
        <div class="col-md-4">
            <button class="btn btn-primary me-2">{{ __('buttons.search') }}</button>
            <a href="{{ route('admin.payment-transactions.index') }}" class="btn btn-outline-secondary">{{ __('buttons.cancel_filter') }}</a>
        </div>
    </div>
</form>

<div id="admin-payment-transactions-listing" data-admin-ajax-pagination>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped mb-0 align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>{{ __('shop.admin.order') }}</th>
                        <th>{{ __('shop.admin.payment_method') }}</th>
                        <th>{{ __('shop.admin.provider') }}</th>
                        <th>{{ __('forms.status') }}</th>
                        <th>{{ __('shop.admin.amount') }}</th>
                        <th>{{ __('shop.admin.external_id') }}</th>
                        <th>{{ __('shop.admin.created_at') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($transactions as $tx)
                    <tr>
                        <td>{{ $tx->id }}</td>
                        <td>
                            @if($tx->order)
                                <a href="{{ route('admin.orders.show', $tx->order) }}">{{ $tx->order->number }}</a>
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $tx->paymentMethod?->name ?? '-' }}</td>
                        <td>{{ $tx->provider_code }}</td>
                        <td>
                            @php
                                $badge = match($tx->status) {
                                    'paid' => 'success',
                                    'failed' => 'danger',
                                    default => 'secondary',
                                };
                            @endphp
                            <span class="badge bg-{{ $badge }}">{{ $tx->status }}</span>
                        </td>
                        <td>{{ number_format((float) $tx->amount_gross, 2, ',', ' ') }} {{ $tx->currency }}</td>
                        <td>{{ $tx->external_id ?? '-' }}</td>
                        <td>{{ $tx->created_at?->format('d.m.Y H:i') }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.payment-transactions.show', $tx) }}" class="btn btn-sm btn-outline-primary">{{ __('buttons.detail') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted">{{ __('messages.no_transactions') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $transactions->links() }}</div>
</div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var listingRoot = document.getElementById('admin-payment-transactions-listing');
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
                    var nextListing = doc.getElementById('admin-payment-transactions-listing');

                    if (!nextListing) {
                        window.location.href = url;
                        return;
                    }

                    listingRoot.innerHTML = nextListing.innerHTML;
                    if (pushState) {
                        window.history.pushState({ adminPaymentTransactionsPagination: true }, '', url);
                    }
                })
                .catch(function () {
                    window.location.href = url;
                });
            };

            document.addEventListener('click', function (event) {
                var link = event.target.closest('#admin-payment-transactions-listing .pagination a');
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
