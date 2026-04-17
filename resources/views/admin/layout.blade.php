<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('shop.admin.title')) - {{ config('app.name', 'OneShop') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('admin.dashboard') }}">{{ __('shop.admin.title') }} - {{ config('app.name', 'OneShop') }}</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="adminNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="{{ route('admin.dashboard') }}">{{ __('shop.admin.dashboard') }}</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('admin.products.index') }}">{{ __('shop.admin.products') }}</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('admin.categories.index') }}">{{ __('shop.admin.categories') }}</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('admin.price-lists.index') }}">{{ __('shop.admin.price_lists') }}</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('admin.shipping-methods.index') }}">{{ __('shop.admin.shipping_methods') }}</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('admin.payment-methods.index') }}">{{ __('shop.admin.payment_methods') }}</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('admin.payment-transactions.index') }}">{{ __('shop.admin.payment_transactions') }}</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('admin.customers.index') }}">{{ __('shop.admin.customers') }}</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('admin.orders.index') }}">{{ __('shop.admin.orders') }}</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('admin.themes.index') }}">{{ __('shop.admin.themes') }}</a></li>
            </ul>
            <div class="d-flex gap-2">
                <a href="{{ route('home') }}" class="btn btn-outline-light btn-sm">{{ __('shop.admin.frontend') }}</a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm">{{ __('shop.logout') }}</button>
                </form>
            </div>
        </div>
    </div>
</nav>

<main class="container py-4">
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    window.i18n = @json(\App\Support\JavaScriptTranslations::admin());
    window.appLocale = @json(app()->getLocale());
</script>
</body>
</html>
