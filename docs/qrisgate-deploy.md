# Deploy Pembayaran QRISGate

Panduan mengaktifkan pembayaran QRIS dinamis lewat [QRISGate](https://qrisgate.web.id/docs) di server produksi (aaPanel).

## Alur singkat

1. Pembeli checkout. Server memanggil `POST /api/v1/transactions` dengan `external_order_id` = nomor order dan `amount` = total order (harga − voucher + fee metode).
2. QRISGate mengembalikan `qr_string`, kode unik (Rp 1–20), `total_amount`, dan `expires_at`. Total dan batas waktu order disamakan dengan nilai dari QRISGate.
3. Halaman invoice menampilkan QR dinamis (nominal terkunci). Halaman memeriksa status setiap 10 detik.
4. Pembayaran masuk, lalu QRISGate mengirim webhook `payment.paid` ke `/qrisgate/webhook`. Order menjadi lunas, email "pembayaran berhasil" terkirim, dan pengiriman produk berjalan otomatis.
5. Cadangan: perintah `qrisgate:sync` (setiap 2 menit) mengecek status transaksi yang masih menunggu, untuk berjaga-jaga kalau webhook gagal sampai.
6. Cadangan terakhir: tombol **Tandai Lunas** di admin (daftar Order dan halaman detail Order).

Kalau QRISGate gagal saat checkout (timeout, saldo kredit habis, batas transaksi aktif), order dibatalkan dan pembeli diminta mencoba lagi.

## 1. Persiapan akun QRISGate

- [ ] Daftar dan verifikasi email di qrisgate.web.id.
- [ ] Unggah **QRIS statis DANA Bisnis** di Dashboard.
- [ ] Aktifkan kanal deteksi mutasi: aplikasi Android **QRISGate Listener** (akses notifikasi aktif, battery optimization di-bypass, login akun yang benar), atau Moota/Cekmutasi.
- [ ] Isi saldo kredit (minimal Rp 100 per transaksi) atau berlangganan.
- [ ] Salin **Live API Key** (`qg_live_...`). Kunci hanya ditampilkan sekali.
- [ ] Set **URL webhook** ke `https://digitalpay.web.id/qrisgate/webhook`, lalu salin **webhook secret**.

## 2. `.env` produksi

```env
PAYMENT_GATEWAY=qrisgate
QRISGATE_BASE_URL=https://qrisgate.web.id/api/v1
QRISGATE_API_KEY=qg_live_xxxxxxxx
QRISGATE_WEBHOOK_SECRET=xxxxxxxx
QRISGATE_TIMEOUT=15
```

Variabel lama `MANUAL_QRIS_ENABLED` dan `MANUAL_QRIS_EXPIRES_MINUTES` sudah tidak dipakai dan boleh dihapus.

Untuk kembali ke Midtrans: `PAYMENT_GATEWAY=midtrans` (kredensial `MIDTRANS_*` harus terisi).

Setelah mengubah `.env`:

```bash
php artisan config:cache
php artisan route:cache
```

## 3. Metode pembayaran di admin

- [ ] Menu **Metode Pembayaran**: metode berkode `qris` harus **aktif**. Saat `PAYMENT_GATEWAY=qrisgate`, hanya metode ini yang ditampilkan di checkout.
- [ ] Atur fee metode QRIS sesuai keinginan (0, flat, atau persen). Kode unik dari QRISGate ditambahkan di atas total tersebut.

## 4. Proses yang wajib berjalan di server

**Cron scheduler** (aaPanel → Cron → Shell Script, setiap 1 menit):

```bash
cd /www/wwwroot/digitalpay && php artisan schedule:run >> /dev/null 2>&1
```

Scheduler menjalankan `orders:expire` (setiap menit) dan `qrisgate:sync` (setiap 2 menit).

**Queue worker** (aaPanel → Supervisor Manager), untuk pengiriman produk dan email:

```bash
php /www/wwwroot/digitalpay/artisan queue:work --sleep=3 --tries=3 --max-time=3600
```

Setelah setiap deploy, restart worker: `php artisan queue:restart`.

## 5. Webhook

- [ ] URL `https://digitalpay.web.id/qrisgate/webhook` bisa diakses publik lewat HTTPS (tidak terhalang firewall, Cloudflare challenge, atau basic auth).
- [ ] Jam server sinkron (NTP). Tanda tangan webhook ditolak kalau selisih waktu lebih dari 5 menit.
- [ ] Respons webhook: `200` (diterima), `401` (tanda tangan salah atau kedaluwarsa), `400` (payload rusak). Riwayat penerimaan tercatat di tabel `payment_logs` dengan `event_type` berawalan `qrisgate_`.

## 6. Uji sebelum go-live

- [ ] Jalankan tes otomatis: `php artisan test`.
- [ ] Buat satu pesanan nominal kecil, scan QR, bayar. Pastikan order otomatis lunas, email masuk, dan produk terkirim.
- [ ] Uji bayar dengan nominal salah lewat QR statis lain. Order harus tetap **menunggu**, lalu bisa diselesaikan dengan **Tandai Lunas**.
- [ ] Jalankan manual `php artisan qrisgate:sync` dan pastikan tidak ada error.
- [ ] Cek log `storage/logs/laravel.log` untuk pesan `[QRISGate]` atau `[Checkout] QRISGate`.

## Catatan

- Dokumentasi QRISGate tidak menyebut apakah `GET /api/v1/transactions/:id` membutuhkan API key. Aplikasi tetap mengirim header `Authorization: Bearer`. Pastikan `qrisgate:sync` berjalan tanpa error saat uji coba.
- Pembayaran yang masuk setelah order kedaluwarsa tetap diproses menjadi lunas, dan produk dikirim kalau stok masih ada.
- Email untuk order QRISGate: pesanan dibuat, pembayaran berhasil, dan pengiriman produk. Email kedaluwarsa dan pembatalan tidak dikirim.
