@extends('admin.layout')

@section('title', $isEdit ? __('shop.admin.edit_customer') : __('shop.admin.new_customer'))

@section('content')
<h1 class="h3 mb-3">{{ $isEdit ? __('shop.admin.edit_customer') : __('shop.admin.new_customer') }}</h1>

<form method="POST" action="{{ $isEdit ? route('admin.customers.update', $customer) : route('admin.customers.store') }}" class="card">
    <div class="card-body row g-3">
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div class="col-md-6">
            <label class="form-label">{{ __('shop.admin.user_optional') }}</label>
            <select name="user_id" class="form-select">
                <option value="">{{ __('forms.user_none') }}</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" @selected((string)old('user_id', $customer->user_id) === (string)$user->id)>{{ $user->name }} ({{ $user->email }})</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label">{{ __('shop.admin.price_list_optional') }}</label>
            <select name="price_list_id" class="form-select">
                <option value="">{{ __('forms.price_list_none') }}</option>
                @foreach($priceLists as $priceList)
                    <option value="{{ $priceList->id }}" @selected((string)old('price_list_id', $customer->price_list_id) === (string)$priceList->id)>{{ $priceList->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label">{{ __('forms.type') }}</label>
            <select name="type" class="form-select" required>
                <option value="retail" @selected(old('type', $customer->type ?: 'retail') === 'retail')>Retail</option>
                <option value="b2b" @selected(old('type', $customer->type) === 'b2b')>B2B</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">{{ __('forms.first_name') }}</label>
            <input name="first_name" class="form-control" value="{{ old('first_name', $customer->first_name) }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">{{ __('forms.last_name') }}</label>
            <input name="last_name" class="form-control" value="{{ old('last_name', $customer->last_name) }}" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">{{ __('forms.email') }}</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $customer->email) }}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">{{ __('forms.phone') }}</label>
            <input name="phone" class="form-control" value="{{ old('phone', $customer->phone) }}">
        </div>

        <div class="col-md-4">
            <label class="form-label">{{ __('forms.company_name') }}</label>
            <input name="company_name" class="form-control" value="{{ old('company_name', $customer->company_name) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">{{ __('forms.company_id') }}</label>
            <input name="company_id" class="form-control" value="{{ old('company_id', $customer->company_id) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">{{ __('forms.vat_id') }}</label>
            <input name="vat_id" class="form-control" value="{{ old('vat_id', $customer->vat_id) }}">
        </div>

        <div class="col-12">
            <div class="form-check">
                <input type="hidden" name="is_active" value="0">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $customer->is_active ?? true))>
                <label class="form-check-label" for="is_active">{{ __('forms.active_customer') }}</label>
            </div>
        </div>
    </div>
    <div class="card-footer d-flex gap-2">
        <button class="btn btn-primary">{{ __('buttons.save') }}</button>
        <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">{{ __('buttons.back') }}</a>
    </div>
</form>
@endsection
