@extends('emails.layout')

@section('title', 'Pesanan Dibatalkan')

@section('header-title', 'Pesanan Dibatalkan')

@section('content')
    <p class="greeting">Halo {{ $customer_name }},</p>
    
    <p>Pesanan Anda telah dibatalkan.</p>
    
    <div class="alert alert-danger">
        <p style="margin: 0;"><strong>Pesanan Dibatalkan</strong></p>
        <p style="margin: 5px 0 0 0;">{{ $reason ?? 'Pesanan Anda telah dibatalkan.' }}</p>
    </div>
    
    <div class="order-info">
        <p><strong>Nomor Pesanan:</strong> {{ $order_number }}</p>
        <p><strong>Tanggal Pesanan:</strong> {{ $order_date }}</p>
        <p><strong>Tanggal Pembatalan:</strong> {{ $cancellation_date }}</p>
    </div>
    
    <h3 style="margin-top: 25px; margin-bottom: 12px; font-size: 15px; font-weight: 600;">Detail Pesanan yang Dibatalkan</h3>
    
    <table class="items-table">
        <thead>
            <tr>
                <th>Produk</th>
                <th style="text-align: center;">Qty</th>
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
                <td style="text-align: right; font-size: 13px;">{{ $item['formatted_total'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="total-section">
        <div class="total-row grand-total">
            <span>Total Pesanan:</span>
            <span>{{ $formatted_total_amount }}</span>
        </div>
    </div>
    
    <div class="info-box" style="margin-top: 20px;">
        <h4>Alasan Pembatalan Umum</h4>
        <ul style="margin: 0; padding-left: 20px; font-size: 13px;">
            <li style="margin-bottom: 4px;">Pembayaran tidak dilakukan sebelum batas waktu</li>
            <li style="margin-bottom: 4px;">Permintaan pembatalan dari pelanggan</li>
            <li style="margin-bottom: 4px;">Stok produk tidak tersedia</li>
            <li>Masalah teknis dalam pemrosesan</li>
        </ul>
    </div>
    
    <div class="alert alert-info" style="margin-top: 15px;">
        <p style="margin: 0;"><strong>Ingin Memesan Lagi?</strong></p>
        <p style="margin: 8px 0 0 0; font-size: 13px;">Anda dapat membuat pesanan baru kapan saja. Kami siap melayani Anda!</p>
    </div>
    
    <p style="margin-top: 25px;">Terima kasih atas pengertian Anda.</p>
@endsection
