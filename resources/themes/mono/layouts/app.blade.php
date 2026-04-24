<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'OneShop'))</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --mono-bg: #0a0a0a;
            --mono-surface: #141414;
            --mono-surface-2: #1d1d1d;
            --mono-text: #f3f3f3;
            --mono-muted: #9a9a9a;
            --mono-line: #2c2c2c;
            --mono-accent: #ffffff;
        }

        body {
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, rgba(255,255,255,.07), transparent 25%),
                linear-gradient(180deg, #000 0%, #111 100%);
            color: var(--mono-text);
            font-family: 'Space Grotesk', sans-serif;
        }

        a {
            color: inherit;
        }

        .mono-shell {
            background: rgba(20, 20, 20, 0.9);
            border: 1px solid var(--mono-line);
            border-radius: 1.5rem;
            box-shadow: 0 1.25rem 3rem rgba(0, 0, 0, 0.35);
        }

        .mono-card {
            background: linear-gradient(180deg, rgba(29,29,29,.98), rgba(15,15,15,.98));
            border: 1px solid var(--mono-line);
            border-radius: 1.25rem;
        }

        .mono-btn {
            background: var(--mono-accent);
            color: #111;
            border-color: var(--mono-accent);
        }

        .mono-btn:hover,
        .mono-btn:focus {
            background: #d6d6d6;
            border-color: #d6d6d6;
            color: #111;
        }

        .mono-muted {
            color: var(--mono-muted);
        }

        .mono-pagination .pagination {
            gap: .3rem;
        }

        .mono-pagination .page-link {
            background: #101010;
            color: #ececec;
            border-color: #2c2c2c;
            border-radius: .45rem;
            min-width: 2.2rem;
            text-align: center;
            box-shadow: none;
        }

        .mono-pagination .page-item.active .page-link {
            background: #ffffff;
            color: #111111;
            border-color: #ffffff;
        }

        .mono-pagination .page-item.disabled .page-link {
            color: #7b7b7b;
            background: #151515;
            border-color: #272727;
        }

        .alert {
            border-radius: 1rem;
        }
    </style>
    @stack('styles')
</head>
<body>
    @include('theme::partials.header')

    @if (session('success'))
        <div class="container pt-3"><div class="alert alert-success mono-shell text-white border-0">{{ session('success') }}</div></div>
    @endif

    @if (session('warning'))
        <div class="container pt-3"><div class="alert alert-warning mono-shell border-0">{{ session('warning') }}</div></div>
    @endif

    @if ($errors->any())
        <div class="container pt-3">
            <div class="alert alert-danger mono-shell border-0">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <main class="container py-4 py-lg-5">
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