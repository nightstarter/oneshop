<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'OneShop'))</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,700&family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --studio-ink: #1d1b19;
            --studio-paper: #f6efe5;
            --studio-accent: #d96f32;
            --studio-accent-dark: #a84d1f;
            --studio-line: rgba(29, 27, 25, 0.12);
            --studio-surface: rgba(255, 255, 255, 0.74);
        }

        body {
            min-height: 100vh;
            background:
                radial-gradient(circle at top right, rgba(217, 111, 50, 0.18), transparent 30%),
                radial-gradient(circle at bottom left, rgba(45, 105, 85, 0.12), transparent 28%),
                linear-gradient(180deg, #fbf6ef 0%, #f3eadc 100%);
            color: var(--studio-ink);
            font-family: 'IBM Plex Sans', sans-serif;
        }

        h1, h2, h3, h4, .navbar-brand {
            font-family: 'Fraunces', serif;
        }

        .studio-shell {
            background: var(--studio-surface);
            backdrop-filter: blur(14px);
            border: 1px solid rgba(255, 255, 255, 0.55);
            box-shadow: 0 24px 80px rgba(77, 53, 31, 0.12);
        }

        .studio-card {
            border: 1px solid var(--studio-line);
            border-radius: 1.25rem;
            background: rgba(255, 255, 255, 0.72);
            box-shadow: 0 1rem 2rem rgba(77, 53, 31, 0.08);
        }

        .studio-btn {
            background: var(--studio-accent);
            border-color: var(--studio-accent);
            color: #fff;
        }

        .studio-btn:hover,
        .studio-btn:focus {
            background: var(--studio-accent-dark);
            border-color: var(--studio-accent-dark);
            color: #fff;
        }

        .studio-muted {
            color: rgba(29, 27, 25, 0.66);
        }
    </style>
    @stack('styles')
</head>
<body>
    @include('theme::partials.header')

    @if (session('success'))
        <div class="container pt-3">
            <div class="alert alert-success studio-shell border-0">{{ session('success') }}</div>
        </div>
    @endif

    @if (session('warning'))
        <div class="container pt-3">
            <div class="alert alert-warning studio-shell border-0">{{ session('warning') }}</div>
        </div>
    @endif

    @if ($errors->any())
        <div class="container pt-3">
            <div class="alert alert-danger studio-shell border-0 mb-0">
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