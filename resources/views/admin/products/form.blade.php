@extends('admin.layout')

@section('title', $isEdit ? __('shop.admin.edit_product') : __('shop.admin.new_product'))

@section('content')
<h1 class="h3 mb-3">{{ $isEdit ? __('shop.admin.edit_product') : __('shop.admin.new_product') }}</h1>

<form method="POST" action="{{ $isEdit ? route('admin.products.update', $product) : route('admin.products.store') }}" class="card">
    <div class="card-body row g-3">
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div class="col-md-4">
            <label class="form-label">{{ __('shop.sku') }}</label>
            <input name="sku" class="form-control" value="{{ old('sku', $product->sku) }}" required>
        </div>
        <div class="col-md-8">
            <label class="form-label">{{ __('forms.name') }}</label>
            <input name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">{{ __('forms.slug') }}</label>
            <input name="slug" class="form-control" value="{{ old('slug', $product->slug) }}" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">{{ __('shop.price_net') }}</label>
            <input type="number" step="0.01" min="0" name="price" class="form-control" value="{{ old('price', $product->price) }}" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">{{ __('shop.admin.stock') }}</label>
            <input type="number" min="0" name="stock_quantity" class="form-control" value="{{ old('stock_quantity', $product->stockItem?->quantity ?? $product->available_quantity) }}" required>
        </div>

        <div class="col-12">
            <label class="form-label">{{ __('forms.description') }}</label>
            <textarea name="description" class="form-control" rows="4">{{ old('description', $product->description) }}</textarea>
        </div>

        <div class="col-12">
            <label class="form-label">{{ __('forms.categories') }}</label>
            <select name="category_ids[]" class="form-select" multiple size="8">
                @php($selected = old('category_ids', $selectedCategories))
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(in_array($cat->id, $selected))>{{ $cat->name }}</option>
                @endforeach
            </select>
            <div class="form-text">{{ __('forms.hold_ctrl') }}</div>
        </div>

        <div class="col-12">
            <div class="form-check">
                <input type="hidden" name="active" value="0">
                <input class="form-check-input" type="checkbox" name="active" value="1" id="active" @checked(old('active', $product->active ?? true))>
                <label class="form-check-label" for="active">{{ __('forms.active_product') }}</label>
            </div>
        </div>
    </div>
    <div class="card-footer d-flex gap-2">
        <button class="btn btn-primary">{{ __('buttons.save') }}</button>
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">{{ __('buttons.back') }}</a>
    </div>
</form>

@if ($isEdit)
    <div class="card mt-4">
        <div class="card-header">{{ __('shop.admin.product_composition') }}</div>
        <div class="card-body">
            @if ($product->stockItem === null)
                <p class="text-muted mb-0">{{ __('messages.product_composition_missing_stock_item') }}</p>
            @else
                <div class="mb-3">
                    <div><strong>{{ __('shop.admin.primary_stock_card') }}:</strong> {{ $product->stockItem->name }}</div>
                    <div class="small text-muted">SKU: {{ $product->stockItem->sku }} | {{ __('forms.quantity') }}: {{ $product->stockItem->quantity }}</div>
                    @if ($product->stockItem->isKit())
                        <div class="small text-muted">
                            {{ __('shop.admin.max_sellable_set_stock') }}: <strong>{{ $product->stockItem->availableQuantityForSale() }}</strong>
                        </div>
                    @endif
                </div>

                @if ($product->stockItem->isKit())
                    <form method="POST" action="{{ route('admin.products.composition.store', $product) }}" class="row g-2 mb-3">
                        @csrf
                        <div class="col-md-6">
                            <label class="form-label">{{ __('forms.component_stock_card') }}</label>
                            <select name="component_stock_item_id" class="form-select" required>
                                <option value="">{{ __('forms.select_option') }}</option>
                                @foreach ($availableComponentItems as $item)
                                    <option value="{{ $item->id }}">
                                        {{ $item->name }} ({{ $item->sku }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('forms.quantity') }}</label>
                            <input type="number" name="quantity" class="form-control" min="1" value="1" required>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button class="btn btn-outline-primary w-100" type="submit">{{ __('buttons.add_component') }}</button>
                        </div>
                    </form>
                @endif

                @if ($product->stockItem->kitComponents->isEmpty())
                    <p class="mb-0">{{ __('messages.product_composition_single_item') }}</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('forms.code') }}</th>
                                    <th>{{ __('forms.name') }}</th>
                                    <th>{{ __('forms.quantity') }}</th>
                                    <th>{{ __('shop.admin.stock') }}</th>
                                    <th class="text-end">{{ __('forms.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($product->stockItem->kitComponents as $component)
                                    <tr>
                                        <td>{{ $component->componentStockItem?->sku }}</td>
                                        <td>{{ $component->componentStockItem?->name }}</td>
                                        <td>
                                            <form method="POST" action="{{ route('admin.products.composition.update', [$product, $component]) }}" class="d-flex gap-2">
                                                @csrf
                                                @method('PATCH')
                                                <input type="number" name="quantity" class="form-control form-control-sm" min="1" value="{{ $component->quantity }}" required style="max-width: 110px;">
                                                <button type="submit" class="btn btn-sm btn-outline-primary">{{ __('buttons.update') }}</button>
                                            </form>
                                        </td>
                                        <td>{{ $component->componentStockItem?->quantity ?? 0 }}</td>
                                        <td class="text-end">
                                            <form method="POST" action="{{ route('admin.products.composition.destroy', [$product, $component]) }}" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm(@js(__('messages.delete_confirm')))">
                                                    {{ __('buttons.delete') }}
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @endif
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">{{ __('shop.admin.product_images') }}</div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.products.images.store', $product) }}" enctype="multipart/form-data" class="row g-3 mb-4">
                @csrf
                <div class="col-md-8">
                    <label class="form-label">{{ __('forms.images') }}</label>
                    <input type="file" name="images[]" class="form-control" accept="image/jpeg,image/png,image/webp" multiple required>
                    <div class="form-text">{{ __('forms.image_upload_help') }}</div>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button class="btn btn-primary w-100" type="submit">{{ __('buttons.upload_images') }}</button>
                </div>
            </form>

            @if ($product->productImages->isEmpty())
                <p class="text-muted mb-0">{{ __('messages.no_product_images') }}</p>
            @else
                <div class="row g-3">
                    @foreach ($product->productImages as $productImage)
                        <div class="col-12 col-lg-6">
                            <div class="border rounded p-3 h-100">
                                <div class="d-flex gap-3">
                                    <img
                                        src="{{ route('product-images.show', ['mediaFile' => $productImage->mediaFile, 'variant' => 'thumb']) }}"
                                        alt="{{ $productImage->alt ?: $product->name }}"
                                        style="width: 110px; height: 110px; object-fit: cover;"
                                        class="rounded border"
                                    >

                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <strong>#{{ $productImage->id }}</strong>
                                            @if ($productImage->is_primary)
                                                <span class="badge bg-success">{{ __('shop.admin.main_image') }}</span>
                                            @endif
                                        </div>

                                        <form method="POST" action="{{ route('admin.products.images.update', [$product, $productImage]) }}" class="row g-2">
                                            @csrf
                                            @method('PATCH')
                                            <div class="col-12">
                                                <label class="form-label small mb-1">{{ __('forms.image_alt') }}</label>
                                                <input name="alt" class="form-control form-control-sm" value="{{ old('alt', $productImage->alt) }}" maxlength="191">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label small mb-1">{{ __('forms.sort_order') }}</label>
                                                <input type="number" min="0" name="sort_order" class="form-control form-control-sm" value="{{ old('sort_order', $productImage->sort_order) }}" required>
                                            </div>
                                            <div class="col-12 d-flex gap-2">
                                                <button class="btn btn-sm btn-outline-primary" type="submit">{{ __('buttons.save') }}</button>
                                            </div>
                                        </form>

                                        <div class="d-flex gap-2 mt-2">
                                            <form method="POST" action="{{ route('admin.products.images.main', [$product, $productImage]) }}">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-success" type="submit" @disabled($productImage->is_primary)>
                                                    {{ __('buttons.set_as_main') }}
                                                </button>
                                            </form>

                                            <form method="POST" action="{{ route('admin.products.images.destroy', [$product, $productImage]) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm(@js(__('messages.delete_product_image_confirm')));"
                                                >
                                                    {{ __('buttons.delete') }}
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endif
@endsection
