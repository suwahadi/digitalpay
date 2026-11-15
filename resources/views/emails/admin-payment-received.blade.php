@extends('emails.layout')

@section('title', 'Pembayaran Masuk')

@section('header-title', 'Pembayaran Masuk')

@section('content')
    <p class="greeting">Halo Admin,</p>

    <p>Pembayaran untuk pesanan berikut sudah diterima.</p>

    <div class="order-info">
        <p><strong>Nomor Pesanan:</strong> {{ $order_number }}</p>
        <p><strong>Total Dibayar:</strong> {{ $formatted_total_amount }}</p>
        <p><strong>Metode Pembayaran:</strong> {{ $payment_method }}</p>
        <p><strong>Tanggal Pembayaran:</strong> {{ $payment_date }}</p>
    </div>

    <div class="order-info">
        <p><strong>Pembeli:</strong> {{ $customer_name }}</p>
        <p><strong>Email:</strong> {{ $customer_email }}</p>
        @if($customer_phone)
        <p><strong>WhatsApp:</strong> {{ $customer_phone }}</p>
        @endif
    </div>

    <h3 style="margin-top: 25px; margin-bottom: 12px; font-size: 15px; font-weight: 600;">Produk</h3>
    <table class="items-table">
        <thead>
            <tr>
                <th>Produk</th>
                <th style="text-align: center;">Qty</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td>
                    <strong style="font-size: 13px;">{{ $item['product_name'] }}</strong><br>
                    <span class="text-small">{{ $item['variant_name'] }}</span>
                </td>
                <td style="text-align: center; font-size: 13px;">{{ $item['quantity'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($needs_manual_delivery)
    <div class="alert alert-warning">
        <p style="margin: 0;"><strong>Perlu Pengiriman Manual</strong></p>
        <p style="margin: 5px 0 0 0;">Pesanan ini berisi produk yang harus dikirim manual oleh admin.</p>
    </div>
    @endif

    <div style="text-align: center;">
        <a href="{{ $admin_url }}" class="button">Lihat Pesanan</a>
    </div>
@endsection
