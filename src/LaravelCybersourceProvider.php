<?php

namespace Dipesh79\LaravelCybersource;

use Illuminate\Support\ServiceProvider;

class LaravelCybersourceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/config/cybersource.php' => config_path('cybersource.php'),
        ]);
    }
}
