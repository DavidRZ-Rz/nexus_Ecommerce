<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;

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
        // Evita problemas con claves largas en MySQL viejos
        Schema::defaultStringLength(191);

        // Forzar HTTPS si estás en producción
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
