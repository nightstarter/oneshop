<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'OneShop'))</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Source+Sans+3:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --legacy-bg: #eceff1;
            --legacy-paper: #ffffff;
            --legacy-ink: #2d3338;
            --legacy-muted: #6f7880;
            --legacy-line: #d4d9de;
            --legacy-brand: #d42027;
            --legacy-nav: #3a3d42;
            --legacy-blue: #2f97d1;
            --legacy-blue-dark: #247bb0;
            --legacy-stock: #0e7a1d;
            --legacy-price: #f8b500;
        }

        body {
            margin: 0;
            background: linear-gradient(180deg, #f5f7f8 0%, var(--legacy-bg) 40%, #e4e7ea 100%);
            color: var(--legacy-ink);
            font-family: 'Source Sans 3', sans-serif;
            min-height: 100vh;
        }

        h1, h2, h3, h4, h5, .legacy-nav-link, .legacy-button {
            font-family: 'Oswald', sans-serif;
            letter-spacing: .02em;
        }

        .legacy-shell {
            max-width: 1400px;
            margin: 0 auto;
            background: var(--legacy-paper);
            border-left: 1px solid var(--legacy-line);
            border-right: 1px solid var(--legacy-line);
            box-shadow: 0 14px 40px rgba(20, 35, 48, 0.07);
        }

        .legacy-topline {
            font-size: .95rem;
            background: #f9fbfc;
            border-bottom: 1px solid var(--legacy-line);
        }

        .legacy-topline a {
            color: #2a8dbf;
            text-decoration: none;
            font-weight: 600;
        }

        .legacy-logo {
            color: var(--legacy-brand);
            font-family: 'Oswald', sans-serif;
            font-weight: 700;
            font-size: clamp(2.1rem, 3.2vw, 3rem);
            letter-spacing: .03em;
            line-height: 1;
            text-decoration: none;
            display: inline-block;
        }

        .legacy-logo-sub {
            display: block;
            color: #23282d;
            font-size: .52em;
            letter-spacing: .2em;
            text-transform: lowercase;
            margin-top: .1rem;
        }

        .legacy-search .form-control {
            border: 1px solid #cad1d7;
            padding: .95rem 1rem;
            font-size: 1.05rem;
        }

        .legacy-button {
            text-transform: uppercase;
            font-weight: 600;
            border: 1px solid rgba(0, 0, 0, 0.14);
            border-radius: .22rem;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.34);
        }

        .legacy-button-primary {
            background: linear-gradient(180deg, #39a9e2 0%, var(--legacy-blue) 100%);
            color: #fff;
        }

        .legacy-button-primary:hover,
        .legacy-button-primary:focus {
            background: linear-gradient(180deg, #2f97d1 0%, var(--legacy-blue-dark) 100%);
            color: #fff;
        }

        .legacy-mainnav {
            background: var(--legacy-nav);
            border-top: 1px solid #51545a;
            border-bottom: 1px solid #2f3237;
        }

        .legacy-nav-link {
            color: #fff;
            text-transform: uppercase;
            font-size: 1.08rem;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 62px;
            border-right: 1px solid #61646a;
            border-left: 1px solid #31343a;
            transition: background .2s ease;
        }

        .legacy-nav-link:hover,
        .legacy-nav-link:focus {
            color: #fff;
            background: #2e3136;
        }

        .legacy-breadcrumb {
            background: #dde3e8;
            border-radius: .22rem;
            border: 1px solid #d3d8dd;
            padding: .6rem .9rem;
            margin-top: .8rem;
            margin-bottom: 1rem;
            font-size: 1.35rem;
        }

        .legacy-section-box {
            background: #f4f6f8;
            border: 1px solid var(--legacy-line);
            border-radius: .25rem;
        }

        .legacy-card {
            border: 1px solid var(--legacy-line);
            border-radius: .3rem;
            background: #fff;
            height: 100%;
        }

        .legacy-price-main {
            font-family: 'Oswald', sans-serif;
            font-size: clamp(1.9rem, 2.6vw, 2.6rem);
            line-height: 1;
            color: #1f2327;
            font-weight: 600;
        }

        .legacy-stock {
            color: var(--legacy-stock);
            font-weight: 700;
            font-size: 1.28rem;
        }

        .legacy-flash {
            background: #d2e8d5;
            border: 1px solid #b6d3bb;
            color: #1e5d2c;
            font-family: 'Oswald', sans-serif;
            font-size: 1.55rem;
            padding: .85rem 1rem;
            border-radius: .25rem;
            margin-top: 1rem;
        }

        .legacy-price-tag {
            display: inline-flex;
            align-items: center;
            background: linear-gradient(180deg, #ffc423 0%, var(--legacy-price) 100%);
            color: #fff;
            font-family: 'Oswald', sans-serif;
            font-size: clamp(2rem, 3.2vw, 3rem);
            line-height: 1;
            border-radius: .25rem;
            padding: .4rem 1.2rem;
            clip-path: polygon(0 50%, 10px 0, 100% 0, 100% 100%, 10px 100%);
            text-shadow: 0 1px 0 rgba(0, 0, 0, .2);
        }

        .legacy-price-tag::before {
            content: '';
            width: 10px;
            height: 10px;
            background: #fff;
            border-radius: 50%;
            margin-right: .9rem;
            opacity: .92;
        }

        .legacy-footer {
            border-top: 1px solid var(--legacy-line);
            background: #f7f9fa;
            color: #4d5660;
        }

        .legacy-footer a {
            color: #2d8fc0;
            text-decoration: none;
        }

        .legacy-pagination .pagination {
            gap: .25rem;
        }

        .legacy-pagination .page-link {
            color: #247fb2;
            border: 1px solid #cfd6dc;
            border-radius: .2rem;
            min-width: 2.3rem;
            text-align: center;
            padding: .45rem .7rem;
            line-height: 1.2;
            font-size: 1rem;
            box-shadow: none;
        }

        .legacy-pagination .active .page-link {
            background: #1e89ca;
            border-color: #1e89ca;
            color: #fff;
            font-family: 'Oswald', sans-serif;
        }

        .legacy-pagination .page-item.disabled .page-link {
            color: #8e98a1;
            background: #f3f5f7;
            border-color: #d7dde2;
        }

        @media (max-width: 991.98px) {
            .legacy-nav-link {
                min-height: 52px;
                font-size: .95rem;
            }

            .legacy-breadcrumb {
                font-size: 1.15rem;
            }
        }
    </style>

    @stack('head')
    @stack('styles')
</head>
<body>
    <div class="legacy-shell">
        @include('theme::partials.header')

        @if (session('success'))
            <div class="container-xxl pt-3">
                <div class="alert alert-success mb-0">{{ session('success') }}</div>
            </div>
        @endif

        @if (session('warning'))
            <div class="container-xxl pt-3">
                <div class="alert alert-warning mb-0">{{ session('warning') }}</div>
            </div>
        @endif

        @if ($errors->any())
            <div class="container-xxl pt-3">
                <div class="alert alert-danger mb-0">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <main class="container-xxl pb-4">
            @yield('content')
        </main>

        @include('theme::partials.footer')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.i18n = @json(\App\Support\JavaScriptTranslations::storefront());
        window.appLocale = @json(app()->getLocale());
    </script>
    @stack('scripts')
</body>
</html>
