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
        $this->app->singleton(BrevoService::class);
        $this->app->singleton(MidtransService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->singleton(DigitalDeliveryService::class);

        Notification::extend('brevo', function ($app) {
            return new BrevoChannel();
        });

        \App\Models\Order::observe(\App\Observers\OrderObserver::class);
        \App\Models\DigitalDelivery::observe(\App\Observers\DigitalDeliveryObserver::class);

        // Carbon abbreviates August as "Agt"; the browser's id-ID Intl uses "Agu", so align server dates with it.
        \Illuminate\Support\Carbon::getTranslator()->setMessages('id', [
            'months_short' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
        ]);
    }
}
