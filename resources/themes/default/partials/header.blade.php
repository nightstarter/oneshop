<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">
            <i class="bi bi-shop me-1"></i>{{ config('app.name', 'OneShop') }}
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#themeMainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="themeMainNav">
            <form class="d-flex mx-auto" style="width:360px;" action="{{ route('products.index') }}" method="GET">
                <input class="form-control me-2" type="search" name="q"
                      placeholder="{{ __('forms.search_products') }}"
                       value="{{ request('q') }}">
                <button class="btn btn-outline-light" type="submit">
                    <i class="bi bi-search"></i>
                </button>
            </form>

            <ul class="navbar-nav ms-auto align-items-center">
                @php $cartCount = app(\App\Services\CartService::class)->totalQuantity(); @endphp
                <li class="nav-item me-2">
                    <a class="nav-link position-relative" href="{{ route('cart.index') }}">
                        <i class="bi bi-cart3 fs-5"></i>
                        @if ($cartCount > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                {{ $cartCount }}
                            </span>
                        @endif
                    </a>
                </li>

                <li class="nav-item me-2 d-none d-lg-block">
                    <span class="badge text-bg-light">{{ __('shop.active_theme') }}: {{ config('shop.active_theme') }}</span>
                </li>

                @auth
                    @if (Auth::user()->is_admin)
                        <li class="nav-item me-2">
                            <a class="btn btn-warning btn-sm" href="{{ route('admin.dashboard') }}">{{ __('shop.administration') }}</a>
                        </li>
                    @endif
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i>{{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('account.index') }}">{{ __('shop.account') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('account.orders') }}">{{ __('shop.orders') }}</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button class="dropdown-item text-danger" type="submit">{{ __('shop.logout') }}</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">{{ __('shop.login') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-light btn-sm ms-1" href="{{ route('register') }}">{{ __('shop.register') }}</a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>
