@extends('emails.layout')

@section('title', 'Pembayaran Berhasil')

@section('preheader', 'Pembayaran '.$formatted_total_amount.' untuk pesanan '.$order_number.' sudah kami terima.')

@section('content')
    <p class="eyebrow">Pembayaran Diterima</p>
    <h1>Terima kasih, pembayaran Anda berhasil</h1>
    <p class="muted">Halo {{ $customer_name }}, pembayaran untuk pesanan {{ $order_number }} sudah kami terima pada {{ $payment_date }} WIB.</p>

    <p class="amount">{{ $formatted_total_amount }}</p>

    @if(!empty($needs_manual_delivery))
    <p>Pesanan Anda sedang disiapkan oleh admin. Produk akan muncul di halaman pesanan dan dikirim ke email ini setelah selesai.</p>
    @else
    <p>Produk Anda sedang dikirim otomatis. Detail akses akan muncul di halaman pesanan dan email terpisah dalam beberapa saat.</p>
    @endif

    <div class="btn-wrap">
        <a href="{{ $invoice_url }}" class="btn">Lihat Pesanan</a>
    </div>

    <hr class="rule">

    <p class="section-title">Rincian Pembayaran</p>
    @include('emails.partials.items')
    @include('emails.partials.amounts', ['total_label' => 'Total Dibayar'])

    <hr class="rule">

    <table role="presentation" class="rows" width="100%" cellpadding="0" cellspacing="0">
        <tr><td class="label">Nomor Pesanan</td><td class="value">{{ $order_number }}</td></tr>
        <tr><td class="label">Tanggal Pesanan</td><td class="value">{{ $order_date }}</td></tr>
        <tr><td class="label">Metode</td><td class="value">{{ $payment_method }}</td></tr>
    </table>
@endsection
