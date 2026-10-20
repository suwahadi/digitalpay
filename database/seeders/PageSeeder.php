<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Page;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            'about' => ['Tentang Kami', $this->about()],
            'contact' => ['Kontak', $this->contact()],
            'help' => ['Pusat Bantuan', $this->help()],
            'terms' => ['Syarat & Ketentuan', $this->terms()],
            'privacy' => ['Kebijakan Privasi', $this->privacy()],
        ];

        foreach ($pages as $slug => [$title, $content]) {
            Page::updateOrCreate(
                ['slug' => $slug],
                ['title' => $title, 'content' => $content, 'is_published' => true]
            );
        }
    }

    private function about(): string
    {
        return <<<'HTML'
<p>DigitalPay adalah toko online untuk produk digital dan layanan bisnis. Di sini Anda bisa membeli akses aplikasi premium seperti YouTube Premium dan Netflix, serta layanan untuk pemilik usaha seperti jasa balasan ulasan Google Maps.</p>

<h2>Cara kami bekerja</h2>
<p>Semua pesanan dibuat langsung di website ini. Anda memilih paket, mengisi nama, email, dan nomor telepon, lalu membayar dengan metode yang tersedia di halaman checkout. Anda bisa memesan tanpa membuat akun. Jika Anda mendaftar, semua pesanan tersimpan di menu Riwayat Pesanan.</p>
<p>Setelah pembayaran dikonfirmasi, detail produk kami kirim ke email yang Anda isi saat checkout. Akun aplikasi premium biasanya terkirim dalam 1 sampai 24 jam, tergantung jenis produk dan ketersediaan stok. Untuk jasa balasan ulasan Google Maps, admin menghubungi Anda setelah pembayaran untuk memulai pengerjaan.</p>

<h2>Harga dan garansi</h2>
<p>Harga di halaman produk adalah harga paket. Biaya platform ditampilkan di halaman checkout sebelum Anda membuat pesanan.</p>
<p>Akun aplikasi premium bergaransi selama durasi yang Anda beli. Jika akun bermasalah dalam masa itu, kirim email ke kami dengan menyertakan nomor pesanan.</p>

<h2>Hubungi kami</h2>
<p>Pertanyaan, kendala pesanan, dan klaim garansi bisa dikirim ke <a href="mailto:admin@digitalpay.web.id">admin@digitalpay.web.id</a>. Panduan pemesanan dan pembayaran ada di <a href="/help">Pusat Bantuan</a>.</p>
HTML;
    }

    private function contact(): string
    {
        return <<<'HTML'
<p>Cara tercepat menghubungi DigitalPay adalah lewat email ke <a href="mailto:admin@digitalpay.web.id">admin@digitalpay.web.id</a>.</p>

<h2>Agar kami bisa langsung membantu</h2>
<p>Sertakan informasi berikut di email Anda:</p>
<ul>
<li>Nomor pesanan. Nomor ini diawali DIG- dan tertera di halaman invoice.</li>
<li>Email yang Anda pakai saat checkout.</li>
<li>Penjelasan singkat kendalanya.</li>
<li>Tangkapan layar jika ada, misalnya bukti pembayaran atau pesan error.</li>
</ul>

<h2>Sebelum mengirim email</h2>
<p>Pertanyaan soal cara bayar, status pesanan, dan garansi sudah dijawab di <a href="/help">Pusat Bantuan</a> dan <a href="/faq">FAQ</a>. Jika Anda punya akun, status setiap pesanan juga bisa dilihat di menu <a href="/user/history">Riwayat Pesanan</a>.</p>
HTML;
    }

    private function help(): string
    {
        return <<<'HTML'
<p>Halaman ini menjelaskan cara memesan, membayar, dan menerima produk di DigitalPay. Jika jawaban yang Anda cari tidak ada di sini, buka <a href="/faq">FAQ</a> atau kirim email ke <a href="mailto:admin@digitalpay.web.id">admin@digitalpay.web.id</a>.</p>

<h2>Cara memesan</h2>
<ol>
<li>Buka halaman produk dan pilih paket yang Anda inginkan.</li>
<li>Klik Beli Sekarang, periksa paket yang dipilih, lalu klik Lanjut.</li>
<li>Isi nama, email, dan nomor telepon. Pastikan email benar, karena detail produk dikirim ke alamat ini.</li>
<li>Pilih metode pembayaran dan klik Buat Pesanan. Periksa ringkasan yang muncul, lalu klik Sudah Benar.</li>
<li>Bayar sesuai instruksi di halaman invoice sebelum batas waktunya habis.</li>
</ol>
<p>Untuk jasa balasan ulasan Google Maps, tempel link profil Google Maps bisnis Anda di kolom Catatan saat checkout.</p>
<p>Anda bisa memesan tanpa akun. Dengan akun, semua pesanan tersimpan di menu Riwayat Pesanan.</p>

<h2>Membayar dengan QRIS</h2>
<p>QRIS bisa dibayar dari aplikasi m-banking atau e-wallet apa pun yang mendukung QRIS.</p>
<ol>
<li>Pindai kode QR di halaman invoice.</li>
<li>Ketik nominal persis seperti yang tertera, termasuk kode uniknya. Kode unik membantu admin mencocokkan pembayaran Anda.</li>
<li>Setelah membayar, tunggu admin mencocokkan pembayaran. Halaman invoice akan menampilkan tanda LUNAS setelah pembayaran dikonfirmasi.</li>
</ol>
<p>Jika metode lain tersedia di halaman checkout, seperti virtual account atau e-wallet, ikuti instruksi pembayaran yang muncul. Status pembayaran untuk metode tersebut diperbarui otomatis.</p>

<h2>Batas waktu pembayaran</h2>
<p>Setiap pesanan punya batas waktu pembayaran yang tertera di halaman invoice. Pesanan yang belum dibayar sampai batas waktu itu otomatis kedaluwarsa. Jangan membayar pesanan yang sudah kedaluwarsa. Buat pesanan baru dari halaman produk.</p>

<h2>Setelah pembayaran dikonfirmasi</h2>
<p>Detail akun aplikasi premium kami kirim ke email Anda, biasanya dalam 1 sampai 24 jam tergantung jenis produk dan ketersediaan stok. Jika email belum masuk, periksa juga folder Spam atau Promosi.</p>
<p>Untuk jasa balasan ulasan Google Maps, admin menghubungi Anda setelah pembayaran. Balasan ulasan dikerjakan paling lambat 3 hari kerja.</p>

<h2>Garansi dan kendala akun</h2>
<p>Akun aplikasi premium bergaransi selama durasi yang Anda beli. Jika akun tidak bisa dipakai dalam masa garansi, kirim email ke <a href="mailto:admin@digitalpay.web.id">admin@digitalpay.web.id</a> dengan nomor pesanan dan tangkapan layar kendalanya.</p>

<h2>Akun DigitalPay</h2>
<p>Lupa password? Buka halaman <a href="/forgot-password">Lupa Password</a> dan masukkan email akun Anda untuk menerima link reset. Data profil dan password bisa diubah di halaman <a href="/user/profile">Profil</a>.</p>
HTML;
    }

    private function terms(): string
    {
        return <<<'HTML'
<p><em>Terakhir diperbarui: 20 Oktober 2026</em></p>
<p>Syarat dan ketentuan ini berlaku untuk penggunaan website digitalpay.web.id dan setiap pesanan yang dibuat di dalamnya. Dengan membuat pesanan, Anda dianggap telah membaca dan menyetujui ketentuan ini.</p>

<h2>1. Pesanan</h2>
<ul>
<li>Anda bertanggung jawab atas kebenaran data yang diisi saat checkout, terutama alamat email. Produk dikirim ke email tersebut, dan kami tidak bertanggung jawab atas pengiriman yang gagal karena alamat email salah.</li>
<li>Pesanan diproses setelah pembayaran diterima dan dikonfirmasi.</li>
<li>Layanan yang memerlukan data tambahan, seperti link Google Maps untuk jasa balasan ulasan, wajib dilengkapi di kolom Catatan saat checkout.</li>
</ul>

<h2>2. Harga dan pembayaran</h2>
<ul>
<li>Semua harga dalam Rupiah. Biaya platform ditampilkan di halaman checkout sebelum pesanan dibuat.</li>
<li>Untuk pembayaran QRIS, total tagihan memuat kode unik. Bayar sesuai nominal yang tertera agar pembayaran bisa dicocokkan.</li>
<li>Pesanan yang belum dibayar sampai batas waktu di halaman invoice otomatis kedaluwarsa. Pembayaran untuk pesanan yang sudah kedaluwarsa tidak diproses otomatis. Jika hal ini terjadi, hubungi kami melalui email.</li>
</ul>

<h2>3. Pengiriman</h2>
<ul>
<li>Akun aplikasi premium dikirim ke email Anda, biasanya dalam 1 sampai 24 jam setelah pembayaran dikonfirmasi, tergantung jenis produk dan ketersediaan stok.</li>
<li>Jasa balasan ulasan Google Maps dikerjakan paling lambat 3 hari kerja setelah pembayaran, sesuai jumlah balasan pada paket yang dibeli.</li>
</ul>

<h2>4. Penggunaan produk</h2>
<ul>
<li>Gunakan produk sesuai petunjuk yang dikirim bersama pesanan, termasuk batas jumlah pengguna atau perangkat pada jenis akun tertentu.</li>
<li>Jangan mengubah email, password, atau data profil pada akun bersama, kecuali petunjuk pesanan mengizinkannya.</li>
</ul>

<h2>5. Garansi</h2>
<ul>
<li>Akun aplikasi premium bergaransi selama durasi yang Anda beli.</li>
<li>Garansi tidak berlaku jika kendala muncul karena penggunaan yang tidak sesuai petunjuk pesanan, misalnya mengubah password akun bersama.</li>
<li>Klaim garansi dikirim melalui email dengan menyertakan nomor pesanan dan bukti kendala.</li>
</ul>

<h2>6. Pembatalan dan pengembalian dana</h2>
<ul>
<li>Pesanan yang produknya sudah dikirim tidak dapat dibatalkan.</li>
<li>Jika kami tidak dapat memenuhi pesanan yang sudah dibayar, misalnya karena stok habis, kami menghubungi Anda melalui email untuk menawarkan penggantian produk atau pengembalian dana.</li>
</ul>

<h2>7. Perubahan ketentuan</h2>
<p>Kami dapat memperbarui syarat dan ketentuan ini sewaktu-waktu. Versi terbaru selalu tersedia di halaman ini, dengan tanggal pembaruan di bagian atas.</p>

<h2>8. Kontak</h2>
<p>Pertanyaan tentang ketentuan ini dapat dikirim ke <a href="mailto:admin@digitalpay.web.id">admin@digitalpay.web.id</a>.</p>
HTML;
    }

    private function privacy(): string
    {
        return <<<'HTML'
<p><em>Terakhir diperbarui: 20 Oktober 2026</em></p>
<p>Kebijakan ini menjelaskan data yang dikumpulkan DigitalPay saat Anda menggunakan digitalpay.web.id, untuk apa data itu dipakai, dan pihak lain yang ikut memprosesnya.</p>

<h2>Data yang kami kumpulkan</h2>
<ul>
<li>Data pesanan: nama, email, dan nomor telepon yang Anda isi saat checkout, catatan pesanan, produk yang dibeli, dan status pembayaran.</li>
<li>Data akun, jika Anda mendaftar: nama, email, nomor telepon, dan password. Password disimpan dalam bentuk hash, sehingga tidak bisa dibaca oleh siapa pun, termasuk kami.</li>
<li>Data teknis yang diperlukan agar website berjalan, seperti cookie sesi untuk menjaga status login dan keamanan formulir.</li>
</ul>
<p>Kami tidak menyimpan nomor kartu, PIN, atau kredensial m-banking dan e-wallet Anda.</p>

<h2>Penggunaan data</h2>
<ul>
<li>Memproses pesanan dan mengirim detail produk ke email Anda.</li>
<li>Mencocokkan dan memverifikasi pembayaran.</li>
<li>Menghubungi Anda terkait pesanan, misalnya untuk pengerjaan jasa atau klaim garansi.</li>
<li>Menjaga keamanan website, termasuk membatasi percobaan login yang berulang.</li>
</ul>
<p>Kami tidak memakai data Anda untuk iklan dan tidak menjualnya kepada pihak lain.</p>

<h2>Pihak lain yang memproses data</h2>
<ul>
<li>Brevo, layanan pengiriman email, menerima nama dan email Anda untuk mengirim email terkait pesanan.</li>
<li>Midtrans, penyedia gateway pembayaran, memproses data transaksi jika Anda membayar dengan metode yang dilayani Midtrans.</li>
<li>Iconify, penyedia ikon, menerima permintaan dari browser Anda, termasuk alamat IP, saat ikon di website dimuat.</li>
</ul>

<h2>Penyimpanan di browser</h2>
<p>Website ini menyimpan pilihan tema terang atau gelap di penyimpanan lokal browser Anda. Data ini tidak dikirim ke server. Kami tidak memakai cookie iklan atau alat analitik pihak ketiga.</p>

<h2>Penyimpanan dan keamanan data</h2>
<p>Data disimpan di server yang kami kelola dan hanya dapat diakses oleh admin untuk keperluan pesanan. Koneksi ke website memakai HTTPS. Data pesanan kami simpan selama masih diperlukan untuk layanan purna jual, klaim garansi, dan pencatatan transaksi.</p>

<h2>Hak Anda</h2>
<ul>
<li>Melihat dan memperbarui data profil melalui halaman <a href="/user/profile">Profil</a>, jika Anda memiliki akun.</li>
<li>Meminta salinan data atau penghapusan akun dengan mengirim email ke <a href="mailto:admin@digitalpay.web.id">admin@digitalpay.web.id</a>. Data transaksi yang masih diperlukan untuk pencatatan dapat tetap kami simpan.</li>
</ul>

<h2>Perubahan kebijakan</h2>
<p>Jika kebijakan ini berubah, versi terbaru dan tanggal pembaruannya akan tercantum di halaman ini.</p>

<h2>Kontak</h2>
<p>Pertanyaan tentang privasi dapat dikirim ke <a href="mailto:admin@digitalpay.web.id">admin@digitalpay.web.id</a>.</p>
HTML;
    }
}
