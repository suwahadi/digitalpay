<?php

namespace App\Helpers;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class ConfigHelper
{
    /**
     * Cache duration for settings (in minutes)
     */
    private static $cacheTime = 60;

    /**
     * Boot the configuration by loading database settings
     */
    public static function boot()
    {
        try {
            $settings = Setting::all()->pluck('casted_value', 'key_name')->toArray();
            
            foreach ($settings as $key => $value) {
                Config::set("site.{$key}", $value);
            }
        } catch (\Exception $e) {
        }
    }

    /**
     * Get a setting value from database with caching
     */
    public static function get($key, $default = null)
    {
        return Cache::remember("setting.{$key}", self::$cacheTime, function () use ($key, $default) {
            try {
                return Setting::get($key, $default);
            } catch (\Exception $e) {
                return $default;
            }
        });
    }

    /**
     * Set a setting value
     */
    public static function set($key, $value, $type = 'string', $group = 'general')
    {
        try {
            $result = Setting::set($key, $value, $type, $group);
            
            Cache::forget("setting.{$key}");
            
            return $result;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get all settings for a group
     */
    public static function getGroup($group)
    {
        return Cache::remember("settings.group.{$group}", self::$cacheTime, function () use ($group) {
            try {
                return Setting::group($group)->get()->pluck('casted_value', 'key_name');
            } catch (\Exception $e) {
                return collect();
            }
        });
    }

    /**
     * Clear all settings cache
     */
    public static function clearCache()
    {
        $keys = [
            'setting.site_name',
            'setting.site_tagline',
            'setting.site_description',
            'setting.site_logo',
            'setting.site_favicon',
            'setting.contact_email',
            'setting.contact_phone',
            'setting.contact_whatsapp',
            'setting.contact_telegram',
            'setting.operating_hours',
            'setting.social_instagram',
            'setting.social_facebook',
            'setting.social_twitter',
            'setting.social_youtube',
            'setting.order_number_prefix',
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Get site name
     */
    public static function siteName()
    {
        return self::get('site_name', 'DigitalPay');
    }

    /**
     * Get site tagline
     */
    public static function siteTagline()
    {
        return self::get('site_tagline', 'Segala Kebutuhan Aplikasi Premium Ada Disini');
    }

    /**
     * Get site description
     */
    public static function siteDescription()
    {
        return self::get('site_description', 'Platform terpercaya untuk kebutuhan aplikasi premium');
    }

    /**
     * Get site logo
     */
    public static function siteLogo()
    {
        return self::get('site_logo', '/images/logo.png');
    }

    /**
     * Get site favicon
     */
    public static function siteFavicon()
    {
        return self::get('site_favicon', '/images/favicon.png');
    }

    /**
     * Get all site settings
     */
    public static function siteSettings()
    {
        return [
            'site_name' => self::siteName(),
            'site_tagline' => self::siteTagline(),
            'site_description' => self::siteDescription(),
            'site_logo' => self::siteLogo(),
            'site_favicon' => self::siteFavicon(),
        ];
    }

    /**
     * Get contact settings
     */
    public static function contactSettings()
    {
        return [
            'email' => self::get('contact_email', 'admin@digitalpay.web.id'),
            'phone' => self::get('contact_phone', '081234567890'),
            'whatsapp' => self::get('contact_whatsapp', '6281234567890'),
            'telegram' => self::get('contact_telegram', '@digitalpay_admin'),
            'operating_hours' => self::get('operating_hours', '09:00 - 21:00 WIB'),
        ];
    }

    /**
     * Get social media settings
     */
    public static function socialSettings()
    {
        return [
            'instagram' => self::get('social_instagram'),
            'facebook' => self::get('social_facebook'),
            'twitter' => self::get('social_twitter'),
            'youtube' => self::get('social_youtube'),
        ];
    }

    /**
     * Get all global settings
     */
    public static function globalSettings()
    {
        return array_merge(
            self::siteSettings(),
            [
                'contact' => self::contactSettings(),
                'social' => self::socialSettings(),
                'title' => self::siteName(),
                'tagline' => self::siteTagline(),
                'description' => self::siteDescription(),
                'logo' => self::siteLogo(),
                'favicon' => self::siteFavicon(),
            ]
        );
    }
}