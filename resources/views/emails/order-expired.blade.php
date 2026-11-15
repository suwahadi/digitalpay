@extends('emails.layout')

@section('title', 'Pesanan Kedaluwarsa')

@section('preheader', 'Batas pembayaran pesanan '.$order_number.' sudah lewat.')

@section('content')
    <p class="eyebrow">Kedaluwarsa</p>
    <h1>Batas pembayaran sudah lewat</h1>
    <p class="muted">Halo {{ $customer_name }}, pesanan {{ $order_number }} dibatalkan otomatis karena belum dibayar sampai {{ $expired_at }}.</p>

    <hr class="rule">

    <p class="section-title">Rincian Pesanan</p>
    @include('emails.partials.items')
    <table role="presentation" class="rows" width="100%" cellpadding="0" cellspacing="0" style="margin-top: 8px;">
        <tr class="total"><td class="label" style="color: #111827;">Total</td><td class="value">{{ $formatted_total_amount }}</td></tr>
    </table>

    <div class="btn-wrap">
        <a href="{{ $order_again_url }}" class="btn">Pesan Lagi</a>
    </div>

    <p class="muted small" style="margin-top: 20px;">Jika Anda sudah membayar pesanan ini, hubungi kami dengan menyertakan nomor pesanan.</p>
@endsection
