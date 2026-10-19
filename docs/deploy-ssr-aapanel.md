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

## 5. Queue worker lewat Supervisor Manager

Email OTP & reset password dikirim **sinkron** (tidak butuh worker). Tapi
notifikasi order/delivery **di-queue**, jadi butuh worker jalan terus.

aaPanel → **Supervisor Manager → Add**:

- Name: `digitalpay-queue`
- Run user: user web (mis. `www`)
- Run directory: path project
- Command: `php artisan queue:work --sleep=3 --tries=3 --max-time=3600`
- Processes: 1

## 6. Scheduler (jika dipakai)

aaPanel → **Cron** → tambah tugas tiap menit:

```bash
php /www/wwwroot/digitalpay/artisan schedule:run >> /dev/null 2>&1
```

## 7. Setiap kali deploy ulang

```bash
git pull
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan config:cache route:cache view:cache
pm2 restart digitalpay-ssr      # WAJIB: agar SSR memakai bundle terbaru
```

> `pm2 restart` penting — server SSR memuat bundle ke memori, jadi build baru
> tidak terpakai sampai proses di-restart.

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
