@extends('theme::layouts.app')

@section('title', __('shop.register') . ' - ' . config('app.name'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header fw-bold text-center fs-5">
                    <i class="bi bi-person-plus me-1"></i>{{ __('shop.register') }}
                </div>
                <div class="card-body">
                    <form action="{{ route('register') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">{{ __('forms.display_name') }}</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       name="name" value="{{ old('name') }}" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('forms.first_name') }}</label>
                                <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                       name="first_name" value="{{ old('first_name') }}" required>
                                @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('forms.last_name') }}</label>
                                <input type="text" class="form-control @error('last_name') is-invalid @enderror"
                                       name="last_name" value="{{ old('last_name') }}" required>
                                @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">{{ __('forms.email') }}</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                       name="email" value="{{ old('email') }}" required>
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">{{ __('forms.phone') }}</label>
                                <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                       name="phone" value="{{ old('phone') }}">
                                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('forms.password') }}</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                       name="password" required>
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('forms.password_confirmation') }}</label>
                                <input type="password" class="form-control" name="password_confirmation" required>
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary w-100" type="submit">{{ __('shop.register') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center text-muted small">
                    {{ __('messages.login_prompt') }} <a href="{{ route('login') }}">{{ __('buttons.login') }}</a>
                </div>
            </div>
        </div>
    </div>
@endsection