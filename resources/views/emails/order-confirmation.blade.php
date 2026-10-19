@extends('emails.layout')

@section('title', 'Konfirmasi Pesanan')

@section('header-title', 'Konfirmasi Pesanan')

@section('content')
    <p class="greeting">Halo {{ $customer_name }},</p>
    
    <p>Terima kasih atas pesanan Anda! Pesanan Anda telah berhasil dibuat dan menunggu pembayaran.</p>
    
    <div class="order-info">
        <p><strong>Nomor Pesanan:</strong> {{ $order_number }}</p>
        <p><strong>Tanggal Pesanan:</strong> {{ $order_date }}</p>
        <p><strong>Metode Pembayaran:</strong> {{ $payment_method }}</p>
        @if(isset($expires_at))
        <p><strong>Batas Pembayaran:</strong> {{ $expires_at }}</p>
        @endif
    </div>
    
    <h3 style="margin-top: 25px; margin-bottom: 12px; font-size: 15px; font-weight: 600;">Detail Pesanan</h3>
    
    <table class="items-table">
        <thead>
            <tr>
                <th>Produk</th>
                <th style="text-align: center;">Qty</th>
                <th style="text-align: right;">Harga</th>
                <th style="text-align: right;">Total</th>
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
                <td style="text-align: right; font-size: 13px;">{{ $item['formatted_price'] }}</td>
                <td style="text-align: right; font-size: 13px;">{{ $item['formatted_total'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="total-section">
        <div class="total-row">
            <span>Subtotal:</span>
            <span>{{ $formatted_subtotal }}</span>
        </div>
        @if($fee_amount > 0)
        <div class="total-row">
            <span>Biaya Layanan:</span>
            <span>{{ $formatted_fee_amount }}</span>
        </div>
        @endif
        <div class="total-row grand-total">
            <span>Total Pembayaran:</span>
            <span>{{ $formatted_total_amount }}</span>
        </div>
    </div>
    
    @if(isset($payment_url))
    <div class="alert alert-info">
        <p style="margin: 0;"><strong>Segera selesaikan pembayaran Anda!</strong></p>
        @if(isset($time_remaining))
        <p style="margin: 5px 0 0 0;">Pesanan ini akan kedaluwarsa dalam {{ $time_remaining }}</p>
        @endif
    </div>
    
    <div style="text-align: center;">
        <a href="{{ $payment_url }}" class="button">Bayar Sekarang</a>
    </div>
    @endif
    
    <div class="alert alert-warning" style="margin-top: 30px;">
        <p style="margin: 0;"><strong>Penting:</strong></p>
        <ul style="margin: 10px 0 0 20px; padding: 0;">
            <li>Pastikan Anda melakukan pembayaran sebelum batas waktu yang ditentukan</li>
            <li>Setelah pembayaran dikonfirmasi, produk akan dikirim ke email Anda</li>
            <li>Simpan nomor pesanan untuk referensi Anda</li>
        </ul>
    </div>
@endsection
