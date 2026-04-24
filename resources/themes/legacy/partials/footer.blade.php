<footer class="legacy-footer mt-4">
    <div class="container-xxl py-4">
        <div class="row g-3">
            <div class="col-md-4">
                <h5 class="mb-2">{{ config('app.name', 'OneShop') }}</h5>
                <div class="text-muted">{{ __('shop.footer_tagline') }}</div>
            </div>
            <div class="col-md-4">
                <h6 class="mb-2">{{ __('shop.information') }}</h6>
                <div class="d-flex flex-column gap-1">
                    <a href="{{ route('home') }}">{{ __('shop.home') }}</a>
                    <a href="{{ route('products.index') }}">{{ __('shop.products') }}</a>
                </div>
            </div>
            <div class="col-md-4">
                <h6 class="mb-2">{{ __('shop.customer_zone') }}</h6>
                <div class="d-flex flex-column gap-1">
                    @auth
                        <a href="{{ route('account.index') }}">{{ __('shop.account') }}</a>
                        <a href="{{ route('account.orders') }}">{{ __('shop.orders') }}</a>
                    @else
                        <a href="{{ route('login') }}">{{ __('shop.login') }}</a>
                        <a href="{{ route('register') }}">{{ __('shop.register') }}</a>
                    @endauth
                </div>
            </div>
        </div>

        <hr class="my-3">
        <div class="d-flex flex-column flex-md-row justify-content-between gap-2 text-muted small">
            <span>&copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('shop.all_rights_reserved') }}</span>
            <span>{{ __('shop.active_theme') }}: <strong>{{ config('shop.active_theme') }}</strong></span>
        </div>
    </div>
</footer>
