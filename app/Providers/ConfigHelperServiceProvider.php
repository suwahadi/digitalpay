<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Helpers\ConfigHelper;

class ConfigHelperServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('config.helper', function () {
            return new ConfigHelper();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        ConfigHelper::boot();
    }
}
