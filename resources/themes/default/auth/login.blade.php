@extends('theme::layouts.app')

@section('title', __('shop.login') . ' - ' . config('app.name'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-header fw-bold text-center fs-5">
                    <i class="bi bi-person-lock me-1"></i>{{ __('shop.login') }}
                </div>
                <div class="card-body">
                    <form action="{{ route('login') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">{{ __('forms.email') }}</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   name="email" value="{{ old('email') }}" autofocus required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('forms.password') }}</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember">
                            <label class="form-check-label" for="remember">{{ __('forms.remember_me') }}</label>
                        </div>
                        <button class="btn btn-primary w-100" type="submit">{{ __('buttons.login') }}</button>
                    </form>
                </div>
                <div class="card-footer text-center text-muted small">
                    {{ __('messages.registration_prompt') }} <a href="{{ route('register') }}">{{ __('shop.register') }}</a>
                </div>
            </div>
        </div>
    </div>
@endsection