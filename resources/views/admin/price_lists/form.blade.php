@extends('admin.layout')

@section('title', $isEdit ? __('shop.admin.edit_price_list') : __('shop.admin.new_price_list'))

@section('content')
<h1 class="h3 mb-3">{{ $isEdit ? __('shop.admin.edit_price_list') : __('shop.admin.new_price_list') }}</h1>

<form method="POST" action="{{ $isEdit ? route('admin.price-lists.update', $priceList) : route('admin.price-lists.store') }}" class="card">
    <div class="card-body row g-3">
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div class="col-md-6">
            <label class="form-label">{{ __('forms.name') }}</label>
            <input name="name" class="form-control" value="{{ old('name', $priceList->name) }}" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">{{ __('forms.code') }}</label>
            <input name="code" class="form-control" value="{{ old('code', $priceList->code) }}" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">{{ __('forms.currency') }}</label>
            <input name="currency" class="form-control" value="{{ old('currency', $priceList->currency ?: config('shop.currency')) }}" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">{{ __('forms.valid_from') }}</label>
            <input type="date" name="valid_from" class="form-control" value="{{ old('valid_from', optional($priceList->valid_from)->format('Y-m-d')) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">{{ __('forms.valid_to') }}</label>
            <input type="date" name="valid_to" class="form-control" value="{{ old('valid_to', optional($priceList->valid_to)->format('Y-m-d')) }}">
        </div>

        <div class="col-12">
            <div class="form-check">
                <input type="hidden" name="is_active" value="0">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $priceList->is_active ?? true))>
                <label class="form-check-label" for="is_active">{{ __('forms.active_price_list') }}</label>
            </div>
        </div>
    </div>
    <div class="card-footer d-flex gap-2">
        <button class="btn btn-primary">{{ __('buttons.save') }}</button>
        <a href="{{ route('admin.price-lists.index') }}" class="btn btn-outline-secondary">{{ __('buttons.back') }}</a>
    </div>
</form>
@endsection
