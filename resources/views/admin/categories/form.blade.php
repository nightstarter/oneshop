@extends('admin.layout')

@section('title', $isEdit ? __('shop.admin.edit_category') : __('shop.admin.new_category'))

@section('content')
<h1 class="h3 mb-3">{{ $isEdit ? __('shop.admin.edit_category') : __('shop.admin.new_category') }}</h1>

<form method="POST" action="{{ $isEdit ? route('admin.categories.update', $category) : route('admin.categories.store') }}" class="card">
    <div class="card-body row g-3">
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div class="col-md-6">
            <label class="form-label">{{ __('forms.name') }}</label>
            <input name="name" class="form-control" value="{{ old('name', $category->name) }}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">{{ __('forms.slug') }}</label>
            <input name="slug" class="form-control" value="{{ old('slug', $category->slug) }}" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">{{ __('forms.parent_category') }}</label>
            <select name="parent_id" class="form-select">
                <option value="">{{ __('forms.parent_category_none') }}</option>
                @foreach($parents as $parent)
                    <option value="{{ $parent->id }}" @selected((string)old('parent_id', $category->parent_id) === (string)$parent->id)>{{ $parent->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">{{ __('forms.sort_order') }}</label>
            <input type="number" min="0" name="sort_order" class="form-control" value="{{ old('sort_order', $category->sort_order ?? 0) }}">
        </div>

        <div class="col-12">
            <label class="form-label">{{ __('forms.description') }}</label>
            <textarea name="description" rows="4" class="form-control">{{ old('description', $category->description) }}</textarea>
        </div>

        <div class="col-12">
            <div class="form-check">
                <input type="hidden" name="is_active" value="0">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $category->is_active ?? true))>
                <label class="form-check-label" for="is_active">{{ __('forms.active_category') }}</label>
            </div>
        </div>
    </div>
    <div class="card-footer d-flex gap-2">
        <button class="btn btn-primary">{{ __('buttons.save') }}</button>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">{{ __('buttons.back') }}</a>
    </div>
</form>
@endsection
