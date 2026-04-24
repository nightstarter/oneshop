<header>
    <div class="legacy-topline">
        <div class="container-xxl py-2 d-flex flex-wrap justify-content-between gap-2">
            <div class="d-flex flex-wrap gap-4">
                <a href="#">Dodaci podminky</a>
                <a href="#">Obchodni podminky</a>
                <a href="#">Kontakt</a>
            </div>
            <div class="d-flex flex-wrap gap-4">
                <a href="#">Velkoobchod</a>
                @auth
                    <form action="{{ route('logout') }}" method="POST" class="m-0 p-0">
                        @csrf
                        <button type="submit" class="btn btn-link p-0">{{ __('shop.logout') }}</button>
                    </form>
                @else
                    <a href="{{ route('login') }}">{{ __('shop.login') }}</a>
                @endauth
            </div>
        </div>
    </div>

    <div class="container-xxl py-3 py-lg-4">
        <div class="row g-3 align-items-center">
            <div class="col-lg-3 col-md-5">
                <a href="{{ route('home') }}" class="legacy-logo" aria-label="{{ config('app.name', 'OneShop') }}">
                    AKU-SHOP.cz
                    <span class="legacy-logo-sub">the battery people</span>
                </a>
            </div>

            <div class="col-lg-6 col-md-7">
                <form class="legacy-search" action="{{ route('products.index') }}" method="GET">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input
                            class="form-control"
                            type="search"
                            name="q"
                            value="{{ request('q') }}"
                            placeholder="{{ __('forms.search_products') }}"
                        >
                        <button class="btn legacy-button legacy-button-primary px-4" type="submit">
                            {{ __('buttons.search') }}
                        </button>
                    </div>
                </form>
            </div>

            <div class="col-lg-3 text-lg-end">
                @php $cartCount = app(\App\Services\CartService::class)->totalQuantity(); @endphp
                <a href="{{ route('cart.index') }}" class="d-inline-flex align-items-center gap-3 text-decoration-none">
                    <span class="h2 mb-0" style="color:#f0b100; font-family:'Oswald', sans-serif;">{{ $cartCount > 0 ? $cartCount : '0' }} ks</span>
                    <span class="h1 mb-0" style="color:#2f343a;"><i class="bi bi-basket2-fill"></i></span>
                </a>
            </div>
        </div>
    </div>

    <div class="legacy-mainnav">
        <nav class="container-xxl">
            <div class="row g-0 text-center">
                <div class="col-6 col-lg"><a href="{{ route('products.index') }}" class="legacy-nav-link">Akumulatory</a></div>
                <div class="col-6 col-lg"><a href="{{ route('products.index') }}" class="legacy-nav-link">Nabijecky</a></div>
                <div class="col-6 col-lg"><a href="{{ route('products.index') }}" class="legacy-nav-link">Baterie</a></div>
                <div class="col-6 col-lg"><a href="{{ route('products.index') }}" class="legacy-nav-link">Adaptery</a></div>
                <div class="col-6 col-lg"><a href="{{ route('products.index') }}" class="legacy-nav-link">Ostatni</a></div>
                <div class="col-6 col-lg"><a href="{{ route('products.index') }}" class="legacy-nav-link">Sady</a></div>
                <div class="col-12 col-lg"><a href="{{ route('products.index') }}" class="legacy-nav-link">Vyprodej</a></div>
            </div>
        </nav>
    </div>
</header>
