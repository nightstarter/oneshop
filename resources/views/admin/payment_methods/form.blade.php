@extends('admin.layout')

@section('title', $isEdit ? __('shop.admin.edit_payment_method') : __('shop.admin.new_payment_method'))

@section('content')
<h1 class="h3 mb-3">{{ $isEdit ? __('shop.admin.edit_payment_method') : __('shop.admin.new_payment_method') }}</h1>

<form method="POST" action="{{ $isEdit ? route('admin.payment-methods.update', $paymentMethod) : route('admin.payment-methods.store') }}" class="card">
    <div class="card-body row g-3">
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div class="col-md-4">
            <label class="form-label">{{ __('forms.code') }}</label>
            <input name="code" class="form-control" value="{{ old('code', $paymentMethod->code) }}" required>
        </div>
        <div class="col-md-8">
            <label class="form-label">{{ __('forms.name') }}</label>
            <input name="name" class="form-control" value="{{ old('name', $paymentMethod->name) }}" required>
        </div>

        <div class="col-md-4">
            <label class="form-label">{{ __('forms.provider_code') }}</label>
            <input name="provider_code" class="form-control" value="{{ old('provider_code', $paymentMethod->provider_code) }}" required>
            <div class="form-text">{{ __('shop.admin.provider_code_payment_hint') }}</div>
        </div>
        <div class="col-md-4">
            <label class="form-label">{{ __('forms.type') }}</label>
            <select name="type" class="form-select" required>
                @php($selectedType = old('type', $paymentMethod->type ?: 'offline'))
                <option value="offline" @selected($selectedType === 'offline')>offline</option>
                <option value="redirect" @selected($selectedType === 'redirect')>redirect</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">{{ __('forms.sort_order') }}</label>
            <input type="number" min="0" name="sort_order" class="form-control" value="{{ old('sort_order', $paymentMethod->sort_order ?? 0) }}">
        </div>

        <div class="col-md-6">
            <label class="form-label">{{ __('shop.price_net') }}</label>
            <input type="number" step="0.01" min="0" name="price_net" class="form-control" value="{{ old('price_net', $paymentMethod->price_net ?? 0) }}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">{{ __('forms.price_gross') }}</label>
            <input type="number" step="0.01" min="0" name="price_gross" class="form-control" value="{{ old('price_gross', $paymentMethod->price_gross ?? 0) }}" required>
        </div>

        <div class="col-12">
            <div class="form-check">
                <input type="hidden" name="is_active" value="0">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $paymentMethod->is_active ?? true))>
                <label class="form-check-label" for="is_active">{{ __('forms.active_payment_method') }}</label>
            </div>
        </div>
    </div>
    <div class="card-footer d-flex gap-2">
        <button class="btn btn-primary">{{ __('buttons.save') }}</button>
        <a href="{{ route('admin.payment-methods.index') }}" class="btn btn-outline-secondary">{{ __('buttons.back') }}</a>
    </div>
</form>
@endsection
