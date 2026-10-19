<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Article;
use App\Models\User;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        
        $articles = [
            [
                'title' => '📢 PENGUMUMAN UNTUK SELURUH CUSTOMER DIGITALPAY',
                'slug' => 'pengumuman-untuk-seluruh-customer',
                'excerpt' => 'Informasi penting terkait layanan customer service dan cara menghubungi admin.',
                'content' => 'Halo Sahabat DigitalPay 👋

Kami informasikan bahwa layanan customer service kami siap melayani Anda 24/7 melalui berbagai channel komunikasi. Untuk mendapatkan respon yang lebih cepat, silakan gunakan fitur live chat di website atau hubungi WhatsApp admin kami.

Terima kasih atas kepercayaan Anda menggunakan layanan DigitalPay!',
                'category' => 'Pengumuman',
                'is_published' => true,
                'is_featured' => true,
                'published_at' => now()->subDays(2),
            ],
            [
                'title' => 'Cara Order Produk Di DigitalPay.web.id',
                'slug' => 'cara-order-produk-di-digitalpay',
                'excerpt' => 'Panduan lengkap cara memesan produk aplikasi premium di platform kami.',
                'content' => '## Cara Order Produk Di DigitalPay.web.id

### 1. Pilih Produk yang mau dibeli
Telusuri katalog aplikasi premium kami dan pilih produk yang Anda inginkan.

### 2. Pilih Variant yang mau dibeli
Setiap produk memiliki berbagai pilihan paket dengan durasi dan harga yang berbeda.

### 3. Masukkan Data Customer
Isi data diri Anda dengan lengkap untuk proses pengiriman akun.

### 4. Pilih Metode Pembayaran
Pilih metode pembayaran yang tersedia seperti QRIS, e-wallet, atau virtual account.

### 5. Selesaikan Pembayaran
Ikuti instruksi pembayaran dan tunggu konfirmasi dari sistem.

### 6. Terima Akun Premium
Setelah pembayaran berhasil, akun premium akan dikirim melalui email atau WhatsApp.',
                'category' => 'Tutorial',
                'is_published' => true,
                'is_featured' => false,
                'published_at' => now()->subDays(5),
            ],
            [
                'title' => 'Tips Hemat Berlangganan Aplikasi Premium',
                'slug' => 'tips-hemat-berlangganan-aplikasi-premium',
                'excerpt' => 'Cara cerdas berlangganan aplikasi premium dengan harga terjangkau.',
                'content' => '## Tips Hemat Berlangganan Aplikasi Premium

### 1. Pilih Paket Sharing
Untuk beberapa aplikasi seperti Netflix dan Spotify, Anda bisa memilih paket sharing yang lebih ekonomis.

### 2. Manfaatkan Promo dan Diskon
Pantau terus promo-promo menarik yang sering kami adakan di DigitalPay.

### 3. Beli Paket Jangka Panjang
Paket 3 atau 6 bulan biasanya lebih hemat dibandingkan berlangganan bulanan.

### 4. Gunakan Metode Pembayaran Hemat
Pilih metode pembayaran dengan fee terendah untuk menghemat biaya tambahan.',
                'category' => 'Tips',
                'is_published' => true,
                'is_featured' => false,
                'published_at' => now()->subDays(10),
            ],
        ];

        foreach ($articles as $articleData) {
            Article::create([
                'title' => $articleData['title'],
                'slug' => $articleData['slug'],
                'excerpt' => $articleData['excerpt'],
                'content' => $articleData['content'],
                'author_id' => $admin->id,
                'category' => $articleData['category'],
                'is_published' => $articleData['is_published'],
                'is_featured' => $articleData['is_featured'],
                'published_at' => $articleData['published_at'],
            ]);
        }
    }
}
