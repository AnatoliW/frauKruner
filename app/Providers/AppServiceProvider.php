<?php

namespace App\Providers;

use App\Shop\Shop;
use App\Shop\ShopFacade;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('shop', Shop::class);
        $this->app->singleton('cart', 'App\\Support\\Cart');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Config::set('mail.from.address', mail_from_address());
        Config::set('mail.from.name', mail_from_name());

        Paginator::useBootstrap();

        $this->loadViewsFrom(resource_path('views/vendor/voyager'), 'voyager');

        if (! class_exists('Voyager')) {
            class_alias('App\\Support\\Voyager', 'Voyager');
        }

        if (! class_exists('TCG\\Voyager\\Facades\\Voyager')) {
            class_alias('App\\Support\\Voyager', 'TCG\\Voyager\\Facades\\Voyager');
        }

        if (! class_exists('Shop')) {
            class_alias(ShopFacade::class, 'Shop');
        }

        if (! class_exists('Cart')) {
            class_alias('App\\Support\\Cart', 'Cart');
        }
    }
}
