@extends('emails.layout')

@section('title', 'Pembayaran Berhasil')

@section('header-title', 'Pembayaran Berhasil')

@section('content')
    <p class="greeting">Halo {{ $customer_name }},</p>
    
    <p>Selamat! Pembayaran Anda telah berhasil dikonfirmasi.</p>
    
    <div class="alert alert-success">
        <p style="margin: 0;"><strong>Pembayaran Dikonfirmasi</strong></p>
        <p style="margin: 5px 0 0 0;">Pesanan Anda sedang diproses dan akan segera dikirim.</p>
    </div>
    
    <div class="order-info">
        <p><strong>Nomor Pesanan:</strong> {{ $order_number }}</p>
        <p><strong>Tanggal Pesanan:</strong> {{ $order_date }}</p>
        <p><strong>Tanggal Pembayaran:</strong> {{ $payment_date }}</p>
        <p><strong>Metode Pembayaran:</strong> {{ $payment_method }}</p>
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
                    @if(isset($item['auto_delivery']) && $item['auto_delivery'])
                    <br><span class="text-small" style="color: #28a745;">Pengiriman Otomatis</span>
                    @endif
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
            <span>Total Dibayar:</span>
            <span>{{ $formatted_total_amount }}</span>
        </div>
    </div>
    
    @if(isset($has_digital_items) && $has_digital_items)
    <div class="alert alert-info">
        <p style="margin: 0;"><strong>Produk Digital</strong></p>
        <p style="margin: 5px 0 0 0;">Produk digital Anda akan dikirim ke email ini dalam beberapa menit.</p>
    </div>
    @endif
    
    @if(isset($needs_manual_delivery) && $needs_manual_delivery)
    <div class="alert alert-warning">
        <p style="margin: 0;"><strong>Pemrosesan Manual</strong></p>
        <p style="margin: 5px 0 0 0;">Beberapa produk memerlukan pemrosesan manual. Tim kami akan segera mengirimkannya dalam 1-3 hari kerja.</p>
    </div>
    @endif
    
    <p style="margin-top: 25px;">Terima kasih telah berbelanja dengan kami!</p>
@endsection
