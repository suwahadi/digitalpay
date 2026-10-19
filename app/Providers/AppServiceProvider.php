<?php

namespace App\Providers;

use App\Notifications\Channels\BrevoChannel;
use App\Services\BrevoService;
use App\Services\DigitalDeliveryService;
use App\Services\MidtransService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Notification;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register Brevo Service
        $this->app->singleton(BrevoService::class);
        
        // Register Midtrans Service
        $this->app->singleton(MidtransService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->singleton(DigitalDeliveryService::class);
        
        // Register custom Brevo notification channel
        Notification::extend('brevo', function ($app) {
            return new BrevoChannel();
        });
        
        // Register Observers
        \App\Models\Order::observe(\App\Observers\OrderObserver::class);
        \App\Models\DigitalDelivery::observe(\App\Observers\DigitalDeliveryObserver::class);
    }
}
