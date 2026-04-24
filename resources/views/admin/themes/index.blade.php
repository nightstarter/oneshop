@extends('admin.layout')

@section('title', __('shop.admin.themes'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">{{ __('shop.admin.theme_management') }}</h1>
            <p class="text-muted mb-0">{{ __('shop.admin.active_theme_help') }}</p>
        </div>
        <span class="badge bg-dark fs-6">{{ __('shop.active_theme') }}: {{ $activeTheme }}</span>
    </div>

    <form action="{{ route('admin.themes.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-3">
            @foreach ($themes as $theme)
                <div class="col-md-4">
                    <label class="card h-100 shadow-sm {{ $activeTheme === $theme ? 'border-primary' : '' }}" for="theme_{{ $theme }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <div class="text-muted small text-uppercase">{{ __('shop.theme') }}</div>
                                    <div class="h5 mb-0">{{ $theme }}</div>
                                </div>
                                <input
                                    class="form-check-input mt-1"
                                    type="radio"
                                    name="active_theme"
                                    id="theme_{{ $theme }}"
                                    value="{{ $theme }}"
                                    @checked($activeTheme === $theme)
                                >
                            </div>
                            <p class="text-muted small mb-0">
                                @if ($theme === 'default')
                                    {{ __('messages.theme_default_desc') }}
                                @elseif ($theme === 'studio')
                                    {{ __('messages.theme_studio_desc') }}
                                @elseif ($theme === 'mono')
                                    {{ __('messages.theme_mono_desc') }}
                                @elseif ($theme === 'legacy')
                                    {{ __('messages.theme_legacy_desc') }}
                                @else
                                    {{ __('messages.theme_custom_desc') }}
                                @endif
                            </p>
                        </div>
                    </label>
                </div>
            @endforeach
        </div>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary">{{ __('buttons.save_active_theme') }}</button>
            <a href="{{ route('home') }}" class="btn btn-outline-secondary">{{ __('buttons.open_frontend') }}</a>
        </div>
    </form>
@endsection