@extends('emails.layout')

@section('title', 'Pesanan Dibatalkan')

@section('preheader', 'Pesanan '.$order_number.' telah dibatalkan.')

@section('content')
    <p class="eyebrow">Dibatalkan</p>
    <h1>Pesanan Anda dibatalkan</h1>
    <p class="muted">Halo {{ $customer_name }}, pesanan {{ $order_number }} dibatalkan pada {{ $cancellation_date }}.</p>
    @if(!empty($reason))
    <p><span class="muted">Alasan:</span> {{ $reason }}</p>
    @endif

    <hr class="rule">

    <p class="section-title">Rincian Pesanan</p>
    @include('emails.partials.items')
    <table role="presentation" class="rows" width="100%" cellpadding="0" cellspacing="0" style="margin-top: 8px;">
        <tr class="total"><td class="label" style="color: #111827;">Total</td><td class="value">{{ $formatted_total_amount }}</td></tr>
    </table>

    <div class="btn-wrap">
        <a href="{{ url('/products') }}" class="btn">Belanja Lagi</a>
    </div>

    <p class="muted small" style="margin-top: 20px;">Jika Anda sudah membayar pesanan ini, hubungi kami dengan menyertakan nomor pesanan.</p>
@endsection
