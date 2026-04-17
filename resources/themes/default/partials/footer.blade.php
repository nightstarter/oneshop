<footer class="bg-dark text-light py-4">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-3">
                <h6 class="fw-bold"><i class="bi bi-shop me-1"></i>{{ config('app.name', 'OneShop') }}</h6>
                <p class="small text-secondary mb-0">{{ __('shop.footer_tagline') }}</p>
            </div>
            <div class="col-md-4 mb-3">
                <h6 class="fw-bold">{{ __('shop.information') }}</h6>
                <ul class="list-unstyled small mb-0">
                    <li><a href="{{ route('products.index') }}" class="text-secondary text-decoration-none">{{ __('shop.all_products') }}</a></li>
                </ul>
            </div>
            <div class="col-md-4 mb-3">
                <h6 class="fw-bold">{{ __('shop.customer_zone') }}</h6>
                <ul class="list-unstyled small mb-0">
                    @auth
                        <li><a href="{{ route('account.orders') }}" class="text-secondary text-decoration-none">{{ __('shop.orders') }}</a></li>
                    @else
                        <li><a href="{{ route('login') }}" class="text-secondary text-decoration-none">{{ __('buttons.login') }}</a></li>
                        <li><a href="{{ route('register') }}" class="text-secondary text-decoration-none">{{ __('buttons.register') }}</a></li>
                    @endauth
                </ul>
            </div>
        </div>
        <hr class="border-secondary">
        <div class="d-flex flex-column flex-md-row justify-content-between gap-2 small text-secondary">
            <span>&copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('shop.all_rights_reserved') }}</span>
            <span>{{ __('shop.active_theme') }}: <strong class="text-light">{{ config('shop.active_theme') }}</strong></span>
        </div>
    </div>
</footer>
