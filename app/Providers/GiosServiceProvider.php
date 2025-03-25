<?php

namespace App\Providers;

use App\Services\GiosApi;
use Illuminate\Support\ServiceProvider;

class GiosServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(GiosApi::class, function ($app) {
            return new GiosApi();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
