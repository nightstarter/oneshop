<footer class="container pb-4 pb-lg-5">
    <div class="studio-shell rounded-4 px-4 py-4">
        <div class="row g-3 align-items-center">
            <div class="col-lg-6">
                <div class="small text-uppercase studio-muted mb-2">{{ __('shop.theme') }}</div>
                <div class="h4 mb-0">Studio</div>
            </div>
            <div class="col-lg-6 text-lg-end">
                <div class="small studio-muted">{{ __('shop.active_theme') }}: {{ config('shop.active_theme') }}</div>
                <div class="small studio-muted">&copy; {{ date('Y') }} {{ config('app.name', 'OneShop') }}</div>
            </div>
        </div>
    </div>
</footer>