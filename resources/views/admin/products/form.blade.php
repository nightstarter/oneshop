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
            <input type="number" step="0.01" min="0" name="base_price_net" class="form-control" value="{{ old('base_price_net', $product->base_price_net) }}" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">{{ __('shop.admin.stock') }}</label>
            <input type="number" min="0" name="stock_qty" class="form-control" value="{{ old('stock_qty', $product->stock_qty ?? 0) }}" required>
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
                <input type="hidden" name="is_active" value="0">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $product->is_active ?? true))>
                <label class="form-check-label" for="is_active">{{ __('forms.active_product') }}</label>
            </div>
        </div>
    </div>
    <div class="card-footer d-flex gap-2">
        <button class="btn btn-primary">{{ __('buttons.save') }}</button>
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">{{ __('buttons.back') }}</a>
    </div>
</form>
@endsection
