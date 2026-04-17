@extends('admin.layout')

@section('title', $isEdit ? __('shop.admin.edit_shipping_method') : __('shop.admin.new_shipping_method'))

@section('content')
<h1 class="h3 mb-3">{{ $isEdit ? __('shop.admin.edit_shipping_method') : __('shop.admin.new_shipping_method') }}</h1>

<form method="POST" action="{{ $isEdit ? route('admin.shipping-methods.update', $shippingMethod) : route('admin.shipping-methods.store') }}" class="card">
    <div class="card-body row g-3">
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div class="col-md-4">
            <label class="form-label">{{ __('forms.code') }}</label>
            <input name="code" class="form-control" value="{{ old('code', $shippingMethod->code) }}" required>
        </div>
        <div class="col-md-8">
            <label class="form-label">{{ __('forms.name') }}</label>
            <input name="name" class="form-control" value="{{ old('name', $shippingMethod->name) }}" required>
        </div>

        <div class="col-md-4">
            <label class="form-label">{{ __('forms.provider_code') }}</label>
            <input name="provider_code" class="form-control" value="{{ old('provider_code', $shippingMethod->provider_code) }}" required>
            <div class="form-text">{{ __('shop.admin.provider_code_shipping_hint') }}</div>
        </div>
        <div class="col-md-4">
            <label class="form-label">{{ __('forms.type') }}</label>
            <input name="type" class="form-control" value="{{ old('type', $shippingMethod->type ?: 'standard') }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">{{ __('forms.sort_order') }}</label>
            <input type="number" min="0" name="sort_order" class="form-control" value="{{ old('sort_order', $shippingMethod->sort_order ?? 0) }}">
        </div>

        <div class="col-md-6">
            <label class="form-label">{{ __('shop.price_net') }}</label>
            <input type="number" step="0.01" min="0" name="price_net" class="form-control" value="{{ old('price_net', $shippingMethod->price_net ?? 0) }}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">{{ __('forms.price_gross') }}</label>
            <input type="number" step="0.01" min="0" name="price_gross" class="form-control" value="{{ old('price_gross', $shippingMethod->price_gross ?? 0) }}" required>
        </div>

        <div class="col-12">
            <label class="form-label">{{ __('shop.admin.allowed_payments') }}</label>
            @php($selected = old('payment_method_ids', $selectedPaymentMethods))
            <select name="payment_method_ids[]" class="form-select" multiple size="8">
                @foreach($paymentMethods as $paymentMethod)
                    <option value="{{ $paymentMethod->id }}" @selected(in_array($paymentMethod->id, $selected))>
                        {{ $paymentMethod->name }} ({{ $paymentMethod->code }})
                    </option>
                @endforeach
            </select>
            <div class="form-text">{{ __('shop.admin.shipping_payment_compatibility_help') }}</div>
        </div>

        <div class="col-12">
            <div class="form-check">
                <input type="hidden" name="is_active" value="0">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $shippingMethod->is_active ?? true))>
                <label class="form-check-label" for="is_active">{{ __('forms.active_shipping_method') }}</label>
            </div>
        </div>
    </div>
    <div class="card-footer d-flex gap-2">
        <button class="btn btn-primary">{{ __('buttons.save') }}</button>
        <a href="{{ route('admin.shipping-methods.index') }}" class="btn btn-outline-secondary">{{ __('buttons.back') }}</a>
    </div>
</form>
@endsection
