<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'OneShop'))</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .navbar-brand { font-weight: 700; letter-spacing: -.5px; }
        .product-card { transition: box-shadow .15s; }
        .product-card:hover { box-shadow: 0 .5rem 1rem rgba(0,0,0,.15) !important; }
        .price-gross { font-size: 1.25rem; font-weight: 700; color: #dc3545; }
        .price-net { font-size: .85rem; color: #6c757d; }
        footer { margin-top: 4rem; }
    </style>
    @stack('styles')
</head>
<body>
    @include('theme::partials.header')

    @if (session('success'))
        <div class="container mt-3">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    @if (session('warning'))
        <div class="container mt-3">
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                {{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="container mt-3">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    <main class="container py-4">
        @yield('content')
    </main>

    @include('theme::partials.footer')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.i18n = @json(\App\Support\JavaScriptTranslations::storefront());
        window.appLocale = @json(app()->getLocale());
    </script>
    @stack('scripts')
</body>
</html>
