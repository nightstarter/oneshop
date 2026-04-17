<nav class="container pt-3 pt-lg-4">
    <div class="studio-shell rounded-4 px-3 px-lg-4 py-3">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center gap-3">
            <div class="d-flex justify-content-between align-items-center w-100 w-lg-auto">
                <a class="navbar-brand text-decoration-none text-dark fs-3" href="{{ route('home') }}">
                    {{ config('app.name', 'OneShop') }}
                </a>
                <span class="badge rounded-pill text-bg-light border d-lg-none">studio</span>
            </div>

            <form class="flex-grow-1" action="{{ route('products.index') }}" method="GET">
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-white border-0"><i class="bi bi-search"></i></span>
                    <input class="form-control border-0" type="search" name="q" value="{{ request('q') }}" placeholder="Najit produkt, znacku nebo SKU">
                    <button class="btn studio-btn" type="submit">{{ __('buttons.search') }}</button>
                </div>
            </form>

            <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
                <a href="{{ route('products.index') }}" class="btn btn-link text-dark text-decoration-none">{{ __('shop.products') }}</a>
                @php $cartCount = app(\App\Services\CartService::class)->totalQuantity(); @endphp
                <a href="{{ route('cart.index') }}" class="btn btn-outline-dark position-relative">
                    <i class="bi bi-bag"></i>
                    @if ($cartCount > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-dark">{{ $cartCount }}</span>
                    @endif
                </a>
                <span class="badge rounded-pill text-bg-light border d-none d-lg-inline">{{ __('shop.active_theme') }}: {{ config('shop.active_theme') }}</span>
                @auth
                    <div class="dropdown">
                        <button class="btn btn-dark dropdown-toggle" data-bs-toggle="dropdown" type="button">
                            {{ Auth::user()->name }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('account.index') }}">{{ __('shop.account') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('account.orders') }}">{{ __('shop.orders') }}</a></li>
                            @if (Auth::user()->is_admin)
                                <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}">{{ __('shop.administration') }}</a></li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button class="dropdown-item text-danger" type="submit">{{ __('shop.logout') }}</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-dark">{{ __('shop.login') }}</a>
                    <a href="{{ route('register') }}" class="btn studio-btn">{{ __('shop.register') }}</a>
                @endauth
            </div>
        </div>
    </div>
</nav>