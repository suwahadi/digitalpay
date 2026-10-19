@extends('emails.layout')

@section('title', 'Pengiriman Produk Digital')

@section('header-title', 'Produk Digital Anda')

@section('content')
    <p class="greeting">Halo {{ $customer_name }},</p>
    
    <p>Produk digital Anda telah siap! Berikut adalah detail lengkap produk yang Anda pesan:</p>
    
    <div class="alert alert-success">
        <p style="margin: 0;"><strong>Produk Siap Digunakan</strong></p>
        <p style="margin: 5px 0 0 0;">Silakan gunakan informasi akses di bawah ini untuk produk Anda.</p>
    </div>
    
    <div class="order-info">
        <p><strong>Nomor Pesanan:</strong> {{ $order_number }}</p>
        <p><strong>Tanggal Pengiriman:</strong> {{ $delivery_date }}</p>
    </div>
    
    <h3 style="margin-top: 25px; margin-bottom: 12px; font-size: 15px; font-weight: 600;">Detail Produk Digital</h3>
    
    @foreach($items as $item)
    <div class="product-box">
        <h4>{{ $item['product_name'] }}</h4>
        <p class="text-small" style="margin: 4px 0;"><strong>Varian:</strong> {{ $item['variant_name'] }}</p>
        <p class="text-small" style="margin: 4px 0;"><strong>Jumlah:</strong> {{ $item['quantity'] }}</p>
        
        @if(isset($item['digital_code']) && $item['digital_code'] !== 'Akan dikirim terpisah')
        <div class="content-box">
            <p class="text-xs" style="margin: 0 0 8px 0; text-transform: uppercase; font-weight: 600;">Informasi Akses Produk Digital</p>
            <pre style="margin: 0; font-size: 12px; color: #333; font-family: 'Courier New', monospace; white-space: pre-wrap; word-wrap: break-word; line-height: 1.5;">{{ $item['digital_code'] }}</pre>
        </div>
        @elseif(isset($item['digital_code']) && $item['digital_code'] === 'Akan dikirim terpisah')
        <div class="alert alert-warning" style="margin-top: 10px;">
            <p style="margin: 0;"><strong>Status:</strong> Produk sedang diproses dan akan dikirim terpisah melalui email.</p>
        </div>
        @endif
        
        @if(isset($item['instructions']) && $item['instructions'])
        <div class="alert alert-info" style="margin-top: 10px;">
            <p style="margin: 0; font-size: 12px; text-transform: uppercase; font-weight: 600;">Instruksi Penggunaan</p>
            <p style="margin: 8px 0 0 0; font-size: 13px;">{{ $item['instructions'] }}</p>
        </div>
        @endif
    </div>
    @endforeach
    
    @if(isset($delivery_items) && count($delivery_items) > 0)
    <h3 style="margin-top: 25px; margin-bottom: 12px; font-size: 15px; font-weight: 600;">Item Tambahan</h3>
    @foreach($delivery_items as $deliveryItem)
    <div class="info-box">
        {!! $deliveryItem !!}
    </div>
    @endforeach
    @endif
    
    <div class="alert alert-warning" style="margin-top: 25px;">
        <p style="margin: 0;"><strong>Perhatian Penting:</strong></p>
        <ul style="margin: 8px 0 0 20px; padding: 0; font-size: 13px;">
            <li style="margin-bottom: 4px;">Simpan informasi akses produk digital Anda dengan aman</li>
            <li style="margin-bottom: 4px;">Jangan bagikan informasi ini kepada orang lain</li>
            <li style="margin-bottom: 4px;">Informasi ini hanya berlaku untuk penggunaan pribadi</li>
        </ul>
    </div>
    
    @if(isset($instructions))
    <p style="margin-top: 20px; font-size: 13px;">{{ $instructions }}</p>
    @endif
    
    <p style="margin-top: 25px;">Terima kasih telah berbelanja dengan kami!</p>
@endsection
