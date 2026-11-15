@extends('emails.layout')

@section('title', 'Selesaikan Pembayaran')

@section('preheader', 'Bayar '.$formatted_total_amount.' untuk pesanan '.$order_number.' sebelum '.$expires_at.'.')

@section('content')
    <p class="eyebrow">Menunggu Pembayaran</p>
    <h1>Selesaikan pembayaran Anda</h1>
    <p class="muted">Halo {{ $customer_name }}, pesanan Anda sudah kami terima. Bayar sebelum <strong style="color: #111827;">{{ $expires_at }} WIB</strong> agar pesanan tidak dibatalkan otomatis.</p>

    <p class="amount">{{ $formatted_total_amount }}</p>
    <p class="muted small">Bayar sesuai nominal ini, termasuk kode unik.</p>

    @if(!empty($payment_url))
    <div class="btn-wrap">
        <a href="{{ $payment_url }}" class="btn">Bayar Sekarang</a>
    </div>
    @endif

    <hr class="rule">

    <p class="section-title">Rincian Pesanan</p>
    @include('emails.partials.items')
    @include('emails.partials.amounts', ['total_label' => 'Total Pembayaran'])

    <hr class="rule">

    <table role="presentation" class="rows" width="100%" cellpadding="0" cellspacing="0">
        <tr><td class="label">Nomor Pesanan</td><td class="value">{{ $order_number }}</td></tr>
        <tr><td class="label">Tanggal</td><td class="value">{{ $order_date }}</td></tr>
        <tr><td class="label">Metode</td><td class="value">{{ $payment_method }}</td></tr>
    </table>

    <p class="muted small" style="margin-top: 20px;">Setelah pembayaran terkonfirmasi, produk dikirim ke halaman pesanan dan ke email ini.</p>
@endsection
