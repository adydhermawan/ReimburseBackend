<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS scheme for all URLs when APP_URL is HTTPS
        // Force HTTPS scheme for all URLs when APP_URL is HTTPS
        // if (str_starts_with(config('app.url'), 'https://')) {
        //     URL::forceScheme('https');
        // }
    }
}
