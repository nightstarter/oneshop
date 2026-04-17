@extends('theme::layouts.app')

@section('title', __('shop.account') . ' - ' . config('app.name'))

@section('content')
    <h1 class="h3 mb-4"><i class="bi bi-person-circle me-2"></i>{{ __('shop.account') }}</h1>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header fw-bold">{{ __('shop.login_details') }}</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">{{ __('forms.name') }}</dt>
                        <dd class="col-sm-8">{{ $user->name }}</dd>
                        <dt class="col-sm-4">{{ __('forms.email') }}</dt>
                        <dd class="col-sm-8">{{ $user->email }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        @if ($customer)
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header fw-bold">{{ __('shop.contact_details') }}</div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">{{ __('forms.first_name') }}</dt>
                            <dd class="col-sm-8">{{ $customer->first_name }} {{ $customer->last_name }}</dd>
                            <dt class="col-sm-4">{{ __('forms.phone') }}</dt>
                            <dd class="col-sm-8">{{ $customer->phone ?? '-' }}</dd>
                            <dt class="col-sm-4">Typ</dt>
                            <dd class="col-sm-8">{{ $customer->type === 'b2b' ? 'B2B zakaznik' : 'Zakaznik' }}</dd>
                            @if ($customer->company_name)
                                <dt class="col-sm-4">Firma</dt>
                                <dd class="col-sm-8">{{ $customer->company_name }}</dd>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>
        @endif

        <div class="col-12">
            <a href="{{ route('account.orders') }}" class="btn btn-outline-primary">
                <i class="bi bi-list-ul me-1"></i>{{ __('buttons.my_orders') }}
            </a>
        </div>
    </div>
@endsection