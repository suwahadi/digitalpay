@extends('emails.layout')

@section('title', 'Produk Anda Siap')

@section('preheader', 'Detail akses produk untuk pesanan '.$order_number.' sudah tersedia.')

@section('content')
    <p class="eyebrow">Produk Terkirim</p>
    <h1>Produk Anda siap digunakan</h1>
    <p class="muted">Halo {{ $customer_name }}, berikut detail akses untuk pesanan {{ $order_number }}. Detail yang sama juga tersimpan di halaman pesanan.</p>

    <hr class="rule">

    @include('emails.partials.delivered')

    <div class="btn-wrap" style="padding-top: 4px;">
        <a href="{{ $invoice_url }}" class="btn">Lihat Pesanan</a>
    </div>

    <p class="muted small" style="margin-top: 20px;">Simpan email ini dan jangan bagikan detail akses kepada orang lain. Jika ada login, ganti password setelah masuk bila layanan mengizinkan.</p>
@endsection
