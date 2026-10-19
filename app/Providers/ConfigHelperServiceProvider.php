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
        // Register the config helper
        $this->app->singleton('config.helper', function () {
            return new ConfigHelper();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Boot the configuration helper to load database settings
        ConfigHelper::boot();
        
        // Register global config helper functions
        if (!function_exists('site_config')) {
            function site_config($key = null, $default = null)
            {
                if ($key === null) {
                    return ConfigHelper::globalSettings();
                }
                
                return config("site.{$key}", $default);
            }
        }

        // Add specific helper functions for common settings
        if (!function_exists('site_name')) {
            function site_name()
            {
                return config('site.site_name', 'DigitalPay');
            }
        }

        if (!function_exists('site_tagline')) {
            function site_tagline()
            {
                return config('site.site_tagline', 'Segala Kebutuhan Aplikasi Premium Ada Disini');
            }
        }

        if (!function_exists('site_description')) {
            function site_description()
            {
                return config('site.site_description', 'Platform terpercaya untuk kebutuhan aplikasi premium');
            }
        }

        if (!function_exists('site_logo')) {
            function site_logo()
            {
                return config('site.site_logo', '/images/logo.png');
            }
        }

        if (!function_exists('contact_settings')) {
            function contact_settings()
            {
                return ConfigHelper::contactSettings();
            }
        }

        if (!function_exists('social_settings')) {
            function social_settings()
            {
                return ConfigHelper::socialSettings();
            }
        }
    }
}