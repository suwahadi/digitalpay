# AGENTS.md

Aturan wajib untuk setiap agen AI yang bekerja di repo DigitalPay.

## Proyek

- Laravel 12, Inertia 2 + Vue 3, Tailwind 3, Filament 3 (panel admin), MySQL, queue `database`, email via Brevo.
- Toko produk digital: checkout satu varian per pesanan, voucher, pengiriman produk otomatis/manual.
- Pembayaran: QRISGate (QRIS dinamis dari QRIS statis DANA Bisnis, konfirmasi via webhook) dengan Midtrans sebagai cadangan. Gateway dipilih lewat `.env` `PAYMENT_GATEWAY`.
- Bahasa antarmuka dan pesan untuk pengguna: Bahasa Indonesia.

## Aturan coding

- Tanpa komentar sama sekali di kode: `//`, `#`, `/* */`, PHPDoc `/** */`, `<!-- -->`, `{{-- --}}`.
- Pengecualian:
  - Kode fungsi lama yang dinonaktifkan sementara, diberi komentar singkat `//sementara disable`.
  - File tes (PHPUnit/Pest) dan `.env.example` boleh memakai komentar, seminimal mungkin.
- Setiap file yang disentuh: hapus semua komentar yang ada di file itu, termasuk kode mati yang dikomentari, tanpa perlu konfirmasi. Pastikan sistem dan fitur tetap berjalan (jangan menghapus anotasi yang dibaca mesin seperti `@vite`, atribut PHP `#[...]`, atau directive Blade).
- Ikuti gaya kode yang sudah ada; jangan menambah dependensi tanpa alasan kuat.
- Jalankan tes terkait (`php artisan test`) sebelum commit.

## Aturan git

- Selalu commit dan push langsung ke branch `main`. Jangan membuat branch lain.
- Setiap fase pengerjaan selesai: langsung commit + push tanpa meminta izin, kecuali pemilik repo meminta ditunda.
- Author commit: `suwahadi <suwahadi@gmail.com>`.
- Pesan commit tanpa anotasi AI apa pun: tanpa `Co-Authored-By`, tanpa menyebut Claude, Anthropic, atau tautan sesi.

## Komunikasi

- Saran dan pertanyaan kepada pemilik repo disampaikan dalam bentuk wizard (pilihan ganda) berbahasa Indonesia yang jelas.
- Jangan mulai implementasi besar sebelum pemilik repo menyetujui rencana.
