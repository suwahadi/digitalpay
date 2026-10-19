<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Category;
use Illuminate\Support\Str;

class UpdateProductsSeeder extends Seeder
{
    public function run(): void
    {
        $digitalCategory = Category::firstOrCreate([
            'slug' => 'digital-products'
        ], [
            'name' => 'Digital Products',
            'description' => 'Produk-produk digital seperti akun premium, software, dll',
            'icon' => 'fas fa-download',
            'sort_order' => 1,
            'is_active' => true
        ]);

        $product = Product::first();
        
        if ($product) {
            $product->update([
                'category_id' => $digitalCategory->id,
                'name' => 'Netflix Premium Account',
                'slug' => 'netflix-premium-account',
                'description' => 'Akun Netflix Premium dengan akses penuh ke semua konten dalam kualitas 4K Ultra HD. Nikmati streaming tanpa iklan dengan kualitas terbaik di 4 perangkat secara bersamaan.',
                'short_description' => 'Netflix Premium 30 hari - 4 Screen - 4K Ultra HD',
                'is_digital' => true,
                'digital_instructions' => 'Akun Netflix Premium siap pakai dengan durasi 30 hari. Dapat digunakan untuk streaming di 4 perangkat secara bersamaan dengan kualitas 4K.'
            ]);

            $variant = $product->variants()->first();
            if ($variant) {
                $variant->update([
                    'name' => '30 Days Premium',
                    'description' => 'Netflix Premium selama 30 hari',
                    'price' => 50000,
                    'duration_days' => 30,
                    'stock_quantity' => 100,
                    'auto_delivery' => true,
                    'max_downloads' => 5,
                    'download_expiry_hours' => 48,
                    'delivery_template' => '=== DIGITAL DELIVERY ===
Produk: Netflix Premium Account
Order: #{order_number}
Tanggal: {order_date}

=== INFORMASI AKSES ===
Website: https://netflix.com
Username: {username}
Password: {password}
Durasi: {duration}
Maksimal Screen: {max_screens}
Kualitas: {quality}

=== CARA PENGGUNAAN ===
1. Kunjungi netflix.com
2. Login menggunakan username dan password di atas
3. Nikmati streaming dengan kualitas 4K
4. Jangan bagikan akun ini ke orang lain

=== PENTING ===
- Akun berlaku selama 30 hari dari tanggal pembelian
- Maksimal 4 perangkat dapat streaming bersamaan
- Jangan ubah password atau profile
- Laporkan jika ada masalah dalam 24 jam

=== SUPPORT ===
Jika ada masalah, hubungi:
Email: admin@digitalpay.web.id
WhatsApp: +6281234567890

Terima kasih telah berbelanja!'
                ]);
            }

            $this->command->info('Product updated successfully: ' . $product->name);
        }

        $spotifyProduct = Product::where('slug', 'spotify')->first();

        ProductVariant::create([
            'product_id' => $spotifyProduct->id,
            'name' => '30 Days Premium',
            'description' => 'Spotify Premium selama 30 hari',
            'price' => 30000,
            'original_price' => 50000,
            'duration_days' => 30,
            'stock_quantity' => 50,
            'is_active' => true,
            'auto_delivery' => true,
            'max_downloads' => 3,
            'download_expiry_hours' => 24,
            'sort_order' => 1,
            'delivery_template' => '=== DIGITAL DELIVERY ===
Produk: Spotify Premium Account
Order: #{order_number}
Tanggal: {order_date}

=== INFORMASI AKSES ===
Website: https://spotify.com
Username: {username}
Password: {password}
Durasi: {duration}

=== CARA PENGGUNAAN ===
1. Kunjungi spotify.com atau buka aplikasi Spotify
2. Login menggunakan username dan password di atas
3. Nikmati musik tanpa iklan
4. Download lagu untuk offline listening

=== FITUR PREMIUM ===
- No advertisements
- Unlimited skips
- High quality audio (320kbps)
- Offline download
- Play any song

=== SUPPORT ===
Email: admin@digitalpay.web.id
WhatsApp: +6281234567890'
        ]);

        $youtubeProduct = Product::where('slug', 'youtube-premium')->first();

        ProductVariant::create([
            'product_id' => $youtubeProduct->id,
            'name' => '30 Days Premium',
            'description' => 'YouTube Premium selama 30 hari',
            'price' => 35000,
            'original_price' => 60000,
            'duration_days' => 30,
            'stock_quantity' => 30,
            'is_active' => true,
            'auto_delivery' => true,
            'max_downloads' => 3,
            'download_expiry_hours' => 24,
            'sort_order' => 1
        ]);

        $this->command->info('Digital products setup completed!');
        $this->command->info('Created digital products: Netflix, Spotify, YouTube Premium');
    }
}
