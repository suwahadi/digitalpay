<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Faq;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $faqs = [
            [
                'question' => 'Apa itu DigitalPay?',
                'answer' => 'DigitalPay adalah platform yang menyediakan aplikasi premium dengan harga terjangkau untuk kebutuhan entertainment, produktivitas, dan pembelajaran Anda.',
                'category' => 'Umum',
                'sort_order' => 1,
            ],
            [
                'question' => 'Apakah layanan ini aman dan terpercaya?',
                'answer' => 'Ya, kami adalah platform yang aman dan terpercaya. Kami telah melayani ratusan transaksi dengan tingkat kepuasan customer yang tinggi.',
                'category' => 'Keamanan',
                'sort_order' => 2,
            ],
            [
                'question' => 'Bagaimana cara memesan produk?',
                'answer' => 'Anda bisa memesan dengan mudah melalui website kami. Pilih produk, isi data diri, pilih metode pembayaran, dan selesaikan transaksi. Akun premium akan dikirim setelah pembayaran dikonfirmasi.',
                'category' => 'Pemesanan',
                'sort_order' => 3,
            ],
            [
                'question' => 'Berapa lama proses pengiriman akun?',
                'answer' => 'Proses pengiriman akun premium biasanya dilakukan dalam waktu 1-24 jam setelah pembayaran dikonfirmasi, tergantung pada jenis produk dan ketersediaan stock.',
                'category' => 'Pengiriman',
                'sort_order' => 4,
            ],
            [
                'question' => 'Apakah ada garansi jika akun bermasalah?',
                'answer' => 'Ya, kami memberikan garansi sesuai dengan durasi yang Anda beli. Jika terjadi masalah pada akun, silakan hubungi customer service kami untuk mendapatkan bantuan.',
                'category' => 'Garansi',
                'sort_order' => 6,
            ],
            [
                'question' => 'Metode pembayaran apa saja yang tersedia?',
                'answer' => 'Kami menyediakan berbagai metode pembayaran seperti QRIS, GoPay, DANA, ShopeePay, dan Virtual Account dari berbagai bank (BCA, BNI, BRI).',
                'category' => 'Pembayaran',
                'sort_order' => 7,
            ],
            [
                'question' => 'Bagaimana cara menghubungi customer service?',
                'answer' => 'Anda bisa menghubungi customer service kami melalui WhatsApp atau email ke admin@digitalpay.web.id. Layanan customer service tersedia 24/7 untuk membantu Anda.',
                'category' => 'Customer Service',
                'sort_order' => 8,
            ],
            [
                'question' => 'Apakah bisa pesan tanpa membuat akun?',
                'answer' => 'Ya, Anda bisa melakukan pemesanan sebagai guest tanpa perlu registrasi. Namun, dengan membuat akun, Anda bisa melacak riwayat pesanan dan mendapatkan berbagai benefit member.',
                'category' => 'Akun',
                'sort_order' => 9,
            ],
            [
                'question' => 'Bagaimana cara melacak pesanan saya?',
                'answer' => 'Jika Anda memesan sebagai guest, kami akan mengirimkan link tracking melalui email. Jika Anda memiliki akun member, bisa login untuk melihat riwayat pesanan di dashboard.',
                'category' => 'Tracking',
                'sort_order' => 10,
            ],
        ];

        foreach ($faqs as $faqData) {
            Faq::create($faqData);
        }
    }
}
