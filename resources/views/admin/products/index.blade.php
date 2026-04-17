@extends('admin.layout')

@section('title', __('shop.admin.products'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">{{ __('shop.admin.products') }}</h1>
    <a href="{{ route('admin.products.create') }}" class="btn btn-primary">{{ __('shop.admin.new_product') }}</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-striped mb-0 align-middle">
            <thead><tr><th>ID</th><th>{{ __('shop.sku') }}</th><th>{{ __('forms.name') }}</th><th>{{ __('shop.categories') }}</th><th>{{ __('shop.price_net') }}</th><th>{{ __('shop.admin.active') }}</th><th></th></tr></thead>
            <tbody>
            @foreach($products as $product)
                <tr>
                    <td>{{ $product->id }}</td>
                    <td>{{ $product->sku }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->categories->pluck('name')->join(', ') }}</td>
                    <td>{{ number_format($product->base_price_net, 2, ',', ' ') }}</td>
                    <td>{!! $product->is_active ? '<span class="badge bg-success">' . e(__('shop.admin.yes')) . '</span>' : '<span class="badge bg-secondary">' . e(__('shop.admin.no')) . '</span>' !!}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-outline-primary">{{ __('buttons.edit') }}</a>
                        <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm(@js(__('messages.delete_confirm'))) ">{{ __('buttons.delete') }}</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $products->links() }}</div>
@endsection
