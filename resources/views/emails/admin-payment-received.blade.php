@extends('emails.layout')

@section('title', 'Pembayaran Masuk')

@section('preheader', ($needs_manual_delivery ? 'Perlu kirim manual. ' : '').$order_number.' - '.$formatted_total_amount)

@section('content')
    @if($needs_manual_delivery)
    <p class="eyebrow alert">Perlu Kirim Manual</p>
    <h1>{{ $order_number }} lunas, produk belum terkirim</h1>
    <p>Pesanan ini perlu dikirim manual (varian manual atau stok otomatis habis). Buka pesanan lalu tekan <strong>Kirim Manual</strong>.</p>
    @else
    <p class="eyebrow">Pembayaran Masuk</p>
    <h1>{{ $order_number }} lunas</h1>
    <p class="muted">Produk sudah dikirim otomatis ke pembeli. Tidak perlu tindakan.</p>
    @endif

    <p class="amount">{{ $formatted_total_amount }}</p>
    <p class="muted small">{{ $payment_method }} &middot; {{ $payment_date }} WIB</p>

    <div class="btn-wrap">
        <a href="{{ $admin_url }}" class="btn">Lihat Pesanan</a>
    </div>

    <hr class="rule">

    <p class="section-title">Produk</p>
    @include('emails.partials.items')

    <hr class="rule">

    <p class="section-title">Pembeli</p>
    <table role="presentation" class="rows" width="100%" cellpadding="0" cellspacing="0">
        <tr><td class="label">Nama</td><td class="value">{{ $customer_name }}</td></tr>
        <tr><td class="label">Email</td><td class="value">{{ $customer_email }}</td></tr>
        @if($customer_phone)
        <tr><td class="label">WhatsApp</td><td class="value">{{ $customer_phone }}</td></tr>
        @endif
    </table>
@endsection
