<footer class="container pb-4 pb-lg-5">
    <div class="mono-shell px-4 py-4 d-flex flex-column flex-lg-row justify-content-between gap-3">
        <div>
            <div class="small text-uppercase mono-muted">{{ __('shop.theme') }}</div>
            <div class="fw-semibold">Mono theme</div>
        </div>
        <div class="text-lg-end mono-muted small">
            <div>{{ __('shop.active_theme') }}: {{ config('shop.active_theme') }}</div>
            <div>&copy; {{ date('Y') }} {{ config('app.name', 'OneShop') }}</div>
        </div>
    </div>
</footer>