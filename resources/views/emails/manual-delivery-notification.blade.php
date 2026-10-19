@extends('emails.layout')

@section('title', 'Pesanan Sedang Diproses')

@section('header-title', 'Pesanan Sedang Diproses')

@section('content')
    <p class="greeting">Halo {{ $customer_name }},</p>
    
    <p>Pembayaran Anda telah dikonfirmasi. Pesanan Anda sedang diproses oleh tim kami.</p>
    
    <div class="alert alert-info">
        <p style="margin: 0;"><strong>Pemrosesan Manual</strong></p>
        <p style="margin: 5px 0 0 0;">Produk Anda memerlukan pemrosesan manual dan akan dikirim dalam {{ $estimated_time ?? '1-3 hari kerja' }}.</p>
    </div>
    
    <div class="order-info">
        <p><strong>Nomor Pesanan:</strong> {{ $order_number }}</p>
        <p><strong>Tanggal Pesanan:</strong> {{ $order_date }}</p>
        <p><strong>Tanggal Pembayaran:</strong> {{ $payment_date }}</p>
        <p><strong>Estimasi Pengiriman:</strong> {{ $estimated_time ?? '1-3 hari kerja' }}</p>
    </div>
    
    <h3 style="margin-top: 25px; margin-bottom: 12px; font-size: 15px; font-weight: 600;">Produk yang Dipesan</h3>
    
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
    
    @if(isset($manual_notes) && $manual_notes)
    <div class="alert alert-info">
        <p style="margin: 0;"><strong>Catatan:</strong></p>
        <p style="margin: 10px 0 0 0;">{{ $manual_notes }}</p>
    </div>
    @endif
    
    <div class="info-box" style="margin-top: 20px;">
        <h4>Apa yang Terjadi Selanjutnya?</h4>
        <ol style="margin: 0; padding-left: 20px; font-size: 13px;">
            <li style="margin-bottom: 6px;">Tim kami akan memproses pesanan Anda secara manual</li>
            <li style="margin-bottom: 6px;">Produk akan dikirim ke email <strong>{{ $customer_email }}</strong></li>
            <li style="margin-bottom: 6px;">Anda akan menerima email konfirmasi setelah produk dikirim</li>
        </ol>
    </div>
    
    <div class="alert alert-warning" style="margin-top: 15px;">
        <p style="margin: 0;"><strong>Tips:</strong></p>
        <ul style="margin: 8px 0 0 20px; padding: 0; font-size: 13px;">
            <li style="margin-bottom: 4px;">Pastikan email Anda aktif dan dapat menerima email</li>
            <li style="margin-bottom: 4px;">Periksa folder spam/junk jika tidak menerima email</li>
            <li>Simpan nomor pesanan untuk referensi</li>
        </ul>
    </div>
    
    <p style="margin-top: 25px;">Terima kasih atas kesabaran Anda!</p>
@endsection
