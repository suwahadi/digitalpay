# Deploy SSR (Inertia) di VPS + aaPanel

Panduan menjalankan DigitalPay dengan **Server-Side Rendering** aktif di VPS
(DigitalOcean / Alibaba / dll) yang dikelola **aaPanel**. SSR membuat title,
meta, dan isi halaman muncul di HTML mentah → baik untuk SEO dan preview share
(WhatsApp/Facebook/Twitter).

SSR memerlukan **dua proses jalan permanen**:

1. Aplikasi PHP (Laravel) — Website biasa di aaPanel (Nginx/OpenLiteSpeed + PHP-FPM).
2. Server Node SSR (`node bootstrap/ssr/ssr.js`, port **13714**) — dipanggil PHP tiap request.

Jika server Node mati, Inertia otomatis **fallback ke client-render** — situs tetap
jalan, hanya SEO-nya turun sementara.

## 1. Prasyarat di aaPanel (App Store → install)

- **PHP 8.2+** dengan ekstensi: `gd`, `intl`, `zip`, `pdo_mysql`, `mbstring`, `bcmath`, `exif`, `fileinfo`, `openssl`, `curl`.
- **Node.js 18+** (plugin "Node.js Version Manager").
- **PM2 Manager** (menjaga proses Node SSR tetap hidup & auto-start saat reboot).
- **Supervisor Manager** (menjaga queue worker).
- **MySQL 8**, **Redis** (opsional).

## 2. Build aset (sekali tiap deploy)

```bash
cd /www/wwwroot/digitalpay   # path project Anda
composer install --no-dev --optimize-autoloader
npm ci
npm run build                # menghasilkan public/build + bootstrap/ssr/ssr.js
php artisan migrate --force
php artisan storage:link
php artisan config:cache route:cache view:cache
```

> `npm run build` sudah menjalankan build client **dan** SSR (`vite build --ssr`).
> Pastikan file `bootstrap/ssr/ssr.js` terbentuk.

## 3. .env produksi (yang relevan untuk SSR & antrean)

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://digitalpay.web.id

INERTIA_SSR_ENABLED=true
INERTIA_SSR_URL=http://127.0.0.1:13714/render

QUEUE_CONNECTION=database
```

> Jika suatu saat SSR tidak dipakai (mis. pindah ke shared hosting), set
> `INERTIA_SSR_ENABLED=false` agar PHP tidak mencoba menghubungi port 13714.

## 4. Jalankan server SSR lewat PM2 Manager

Di aaPanel → **PM2 Manager → Add project**:

- Startup file / command: `bootstrap/ssr/ssr.js`
- Run directory: path project (mis. `/www/wwwroot/digitalpay`)
- Node version: 18+
- Name: `digitalpay-ssr`
- Auto restart: **on**

Atau via SSH:

```bash
cd /www/wwwroot/digitalpay
pm2 start bootstrap/ssr/ssr.js --name digitalpay-ssr
pm2 save && pm2 startup     # agar hidup lagi setelah reboot
```

Cek: `curl -s http://127.0.0.1:13714/health` harus `200`.

> Alternatif: `php artisan inertia:start-ssr` (membaca `config/inertia.php`).
> Keduanya sama; menjalankan `node bootstrap/ssr/ssr.js` langsung lebih mudah di PM2.

## 5. Queue worker (PM2)

Email OTP & reset password dikirim **sinkron** (tidak butuh worker). Notifikasi
order (pesanan dibuat, pembayaran sukses, dibatalkan, pengiriman produk) dan
job `ProcessDigitalDelivery` **di-queue**, jadi worker wajib jalan terus.

Worker dijalankan lewat PM2 yang sama dengan SSR, sebagai user `www`:

```bash
cd /www/wwwroot/digitalpay.web.id
sudo -u www env PM2_HOME=$PWD/.pm2 pm2 start artisan --name digitalpay-queue   --interpreter php -- queue:work --sleep=3 --tries=3 --backoff=60,300 --max-time=3600
sudo -u www env PM2_HOME=$PWD/.pm2 pm2 save
```

- `--tries=3 --backoff=60,300`: email yang gagal dicoba ulang setelah 1 lalu
  5 menit; setelah 3 kali gagal masuk tabel `failed_jobs`.
- `--max-time=3600`: worker keluar tiap jam dan PM2 menyalakannya lagi.
- Boot otomatis lewat service `pm2-www` (`pm2 resurrect`).
- `retry_after` antrean database 360 detik (`config/queue.php`), harus lebih
  besar dari timeout job terpanjang (300 detik).

Cek job gagal: `sudo -u www php artisan queue:failed`, ulangi dengan
`sudo -u www php artisan queue:retry all`.

## 6. Scheduler (wajib)

`orders:expire` berjalan tiap menit untuk mengubah order yang lewat batas bayar
menjadi `expired`. Pasang di crontab user `www` (bukan root, agar file cache dan
log tetap milik `www`):

```bash
crontab -u www -e
* * * * * cd /www/wwwroot/digitalpay.web.id && php artisan schedule:run >> /dev/null 2>&1
```

Cek: `sudo -u www php artisan schedule:list`.

## 7. Setiap kali deploy ulang

Jalankan sebagai `www`:

```bash
git pull
composer install --no-dev --optimize-autoloader   # jika composer.lock berubah
npm ci && npm run build
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan queue:restart       # WAJIB: worker memakai kode terbaru
PM2_HOME=$PWD/.pm2 pm2 restart digitalpay-ssr   # WAJIB: SSR memakai bundle terbaru
```

> Worker dan server SSR memuat kode ke memori, jadi perubahan tidak terpakai
> sampai keduanya di-restart.

## Verifikasi SSR aktif

```bash
curl -s https://digitalpay.web.id/product/<slug> | grep -c '<h1'
```

Jika SSR jalan, `<h1>` dan isi produk muncul di HTML mentah (bukan `<div id="app"></div>` kosong).

## Catatan

- Komponen frontend sudah dibuat SSR-safe (akses `window`/`localStorage` hanya
  di `onMounted` atau dijaga `typeof window`). Bila menambah komponen baru yang
  menyentuh browser API di `setup()`/`computed`, jaga dengan cara yang sama,
  atau halaman itu akan fallback ke client-render.
- Tidak ada rahasia yang ditaruh di SSR; server SSR hanya me-render HTML dari
  data yang sudah dikirim PHP.
