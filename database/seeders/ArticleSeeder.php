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
                'content' => '<p>Halo Sahabat DigitalPay 👋</p>'
                    . '<p>Kami informasikan bahwa layanan customer service kami siap melayani Anda 24/7 melalui berbagai channel komunikasi. Untuk mendapatkan respon yang lebih cepat, silakan gunakan fitur live chat di website.</p>'
                    . '<p>Terima kasih atas kepercayaan Anda menggunakan layanan DigitalPay!</p>',
                'category' => 'Pengumuman',
                'is_published' => true,
                'is_featured' => true,
                'published_at' => now()->subDays(8),
            ],
            [
                'title' => 'Cara Order Produk Di DigitalPay.web.id',
                'slug' => 'cara-order-produk-di-digitalpay',
                'excerpt' => 'Panduan lengkap cara memesan produk aplikasi premium di platform kami.',
                'content' => '<h3>1. Pilih Produk yang mau dibeli</h3>'
                    . '<p>Telusuri katalog aplikasi premium kami dan pilih produk yang Anda inginkan.</p>'
                    . '<h3>2. Pilih Varian yang mau dibeli</h3>'
                    . '<p>Setiap produk memiliki berbagai pilihan paket dengan durasi dan harga yang berbeda.</p>'
                    . '<h3>3. Masukkan Data Customer</h3>'
                    . '<p>Isi data diri Anda dengan lengkap untuk proses pengiriman akun.</p>'
                    . '<h3>4. Pilih Metode Pembayaran</h3>'
                    . '<p>Pilih metode pembayaran yang tersedia seperti QRIS, e-wallet, atau virtual account.</p>'
                    . '<h3>5. Selesaikan Pembayaran</h3>'
                    . '<p>Ikuti instruksi pembayaran dan tunggu konfirmasi dari sistem.</p>'
                    . '<h3>6. Terima Akun Premium</h3>'
                    . '<p>Setelah pembayaran berhasil, akun premium akan dikirim melalui email atau WhatsApp.</p>',
                'category' => 'Tutorial',
                'is_published' => true,
                'is_featured' => false,
                'published_at' => now()->subDays(19),
            ],
            [
                'title' => 'Tips Hemat Berlangganan Aplikasi Premium',
                'slug' => 'tips-hemat-berlangganan-aplikasi-premium',
                'excerpt' => 'Cara cerdas berlangganan aplikasi premium dengan harga terjangkau.',
                'content' => '<h3>1. Pilih Paket Sharing</h3>'
                    . '<p>Untuk beberapa aplikasi seperti Netflix dan Spotify, Anda bisa memilih paket sharing yang lebih ekonomis.</p>'
                    . '<h3>2. Manfaatkan Promo dan Diskon</h3>'
                    . '<p>Pantau terus promo-promo menarik yang sering kami adakan di DigitalPay.</p>'
                    . '<h3>3. Beli Paket Jangka Panjang</h3>'
                    . '<p>Paket 3 atau 6 bulan biasanya lebih hemat dibandingkan berlangganan bulanan.</p>'
                    . '<h3>4. Gunakan Metode Pembayaran Hemat</h3>'
                    . '<p>Pilih metode pembayaran dengan fee terendah untuk menghemat biaya tambahan.</p>',
                'category' => 'Tips',
                'is_published' => true,
                'is_featured' => false,
                'published_at' => now()->subDays(13),
            ],
        ];

        foreach ($articles as $articleData) {
            Article::updateOrCreate(
                ['slug' => $articleData['slug']],
                array_merge($articleData, ['author_id' => $admin?->id]),
            );
        }
    }
}
