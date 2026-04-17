<header class="container pt-3 pt-lg-4">
    <div class="mono-shell px-3 px-lg-4 py-3">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center gap-3">
            <div class="d-flex justify-content-between align-items-center w-100 w-lg-auto">
                <a href="{{ route('home') }}" class="text-decoration-none text-white fw-bold fs-3">{{ config('app.name', 'OneShop') }}</a>
                <span class="badge text-bg-light text-dark d-lg-none">mono</span>
            </div>
            <div class="d-flex gap-2 flex-wrap align-items-center flex-grow-1 justify-content-lg-center">
                <a href="{{ route('products.index') }}" class="btn btn-outline-light btn-sm">{{ __('shop.products') }}</a>
                <a href="{{ route('cart.index') }}" class="btn btn-outline-light btn-sm">{{ __('shop.cart') }}</a>
                <span class="badge rounded-pill text-bg-secondary d-none d-lg-inline">{{ __('shop.active_theme') }}: {{ config('shop.active_theme') }}</span>
            </div>
            <div class="d-flex gap-2 flex-wrap justify-content-lg-end">
                @auth
                    <a href="{{ route('account.index') }}" class="btn mono-btn btn-sm">{{ __('shop.account') }}</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-light btn-sm">{{ __('shop.login') }}</a>
                    <a href="{{ route('register') }}" class="btn mono-btn btn-sm">{{ __('shop.register') }}</a>
                @endauth
            </div>
        </div>
    </div>
</header>