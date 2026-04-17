@extends('admin.layout')

@section('title', __('shop.admin.customers'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">{{ __('shop.admin.customers') }}</h1>
    <a href="{{ route('admin.customers.create') }}" class="btn btn-primary">{{ __('shop.admin.new_customer') }}</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-striped mb-0 align-middle">
            <thead><tr><th>ID</th><th>{{ __('forms.first_name') }}</th><th>{{ __('forms.email') }}</th><th>{{ __('forms.type') }}</th><th>{{ __('shop.admin.price_lists') }}</th><th>{{ __('shop.admin.active') }}</th><th></th></tr></thead>
            <tbody>
            @foreach($customers as $customer)
                <tr>
                    <td>{{ $customer->id }}</td>
                    <td>{{ $customer->first_name }} {{ $customer->last_name }}</td>
                    <td>{{ $customer->email }}</td>
                    <td>{{ strtoupper($customer->type) }}</td>
                    <td>{{ $customer->priceList?->name ?? '-' }}</td>
                    <td>{!! $customer->is_active ? '<span class="badge bg-success">' . __('shop.admin.yes') . '</span>' : '<span class="badge bg-secondary">' . __('shop.admin.no') . '</span>' !!}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-sm btn-outline-primary">{{ __('buttons.edit') }}</a>
                        <form action="{{ route('admin.customers.destroy', $customer) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" onclick="return confirm('{{ __('messages.delete_customer_confirm') }}')">{{ __('buttons.delete') }}</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $customers->links() }}</div>
@endsection
