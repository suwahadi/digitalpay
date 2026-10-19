<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key_name' => 'site_name', 'value' => 'DigitalPay', 'type' => 'string', 'group_name' => 'site', 'is_public' => true],
            ['key_name' => 'site_tagline', 'value' => 'Segala Kebutuhan Aplikasi Premium Ada Disini', 'type' => 'string', 'group_name' => 'site', 'is_public' => true],
            ['key_name' => 'site_description', 'value' => 'Platform terpercaya untuk kebutuhan aplikasi premium dengan harga terjangkau', 'type' => 'string', 'group_name' => 'site', 'is_public' => true],
            ['key_name' => 'site_logo', 'value' => '/images/logo.png', 'type' => 'string', 'group_name' => 'site', 'is_public' => true],
            ['key_name' => 'site_favicon', 'value' => '/storage/images/favicon.png', 'type' => 'string', 'group_name' => 'site', 'is_public' => true],
            
            ['key_name' => 'contact_email', 'value' => 'admin@digitalpay.web.id', 'type' => 'string', 'group_name' => 'contact', 'is_public' => true],
            ['key_name' => 'contact_phone', 'value' => '081234567890', 'type' => 'string', 'group_name' => 'contact', 'is_public' => true],
            ['key_name' => 'contact_whatsapp', 'value' => '6281234567890', 'type' => 'string', 'group_name' => 'contact', 'is_public' => true],
            ['key_name' => 'contact_telegram', 'value' => '@digitalpay_admin', 'type' => 'string', 'group_name' => 'contact', 'is_public' => true],
            ['key_name' => 'operating_hours', 'value' => '09:00 - 21:00 WIB', 'type' => 'string', 'group_name' => 'contact', 'is_public' => true],
            
            ['key_name' => 'order_number_prefix', 'value' => 'DIG', 'type' => 'string', 'group_name' => 'business', 'is_public' => false],
            ['key_name' => 'order_expiry_hours', 'value' => '2', 'type' => 'number', 'group_name' => 'business', 'is_public' => false],
            ['key_name' => 'guest_token_expiry_months', 'value' => '6', 'type' => 'number', 'group_name' => 'business', 'is_public' => false],
            ['key_name' => 'auto_delivery_enabled', 'value' => 'false', 'type' => 'boolean', 'group_name' => 'business', 'is_public' => false],
            
            ['key_name' => 'social_instagram', 'value' => 'https://instagram.com/', 'type' => 'string', 'group_name' => 'social', 'is_public' => true],
            ['key_name' => 'social_facebook', 'value' => 'https://facebook.com/', 'type' => 'string', 'group_name' => 'social', 'is_public' => true],
            ['key_name' => 'social_twitter', 'value' => 'https://twitter.com/', 'type' => 'string', 'group_name' => 'social', 'is_public' => true],
            ['key_name' => 'social_youtube', 'value' => 'https://youtube.com/@', 'type' => 'string', 'group_name' => 'social', 'is_public' => true],
        ];

        foreach ($settings as $setting) {
            Setting::create($setting);
        }
    }
}
