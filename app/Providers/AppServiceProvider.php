<?php

namespace App\Providers;

use App\Contracts\ProductSearchInterface;
use App\Search\MysqlProductSearch;
use App\Support\ShopSettings;
use App\Support\ThemeView;
use App\Services\PaymentProviders\BankTransferPaymentProvider;
use App\Services\PaymentProviders\CashOnDeliveryPaymentProvider;
use App\Services\PaymentProviders\ComgatePaymentProvider;
use App\Services\PaymentProviderResolver;
use App\Services\ShippingProviders\GlsAddressShippingProvider;
use App\Services\ShippingProviderResolver;
use App\Services\ShippingProviders\PersonalPickupShippingProvider;
use App\Services\ShippingProviders\ZasilkovnaBoxShippingProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * Phase 1: bind to MysqlProductSearch (no external services needed).
     * Phase 2: swap to ScoutProductSearch and set SCOUT_DRIVER=typesense in .env.
     */
    public function register(): void
    {
        $this->app->bind(ProductSearchInterface::class, MysqlProductSearch::class);
        $this->app->singleton(ShopSettings::class);
        $this->app->singleton(ThemeView::class);

        $this->app->singleton(ShippingProviderResolver::class, function ($app) {
            return new ShippingProviderResolver([
                $app->make(GlsAddressShippingProvider::class),
                $app->make(PersonalPickupShippingProvider::class),
                $app->make(ZasilkovnaBoxShippingProvider::class),
            ]);
        });

        $this->app->singleton(PaymentProviderResolver::class, function ($app) {
            return new PaymentProviderResolver([
                $app->make(BankTransferPaymentProvider::class),
                $app->make(CashOnDeliveryPaymentProvider::class),
                $app->make(ComgatePaymentProvider::class),
            ]);
        });

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        $activeTheme = $this->app->make(ShopSettings::class)->activeTheme();

        config(['shop.active_theme' => $activeTheme]);

        View::getFinder()->replaceNamespace('theme', resource_path('themes/' . $activeTheme));
        View::getFinder()->replaceNamespace('theme-default', resource_path('themes/default'));
    }
}
