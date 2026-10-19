@extends('emails.layout')

@section('title', 'Produk Digital Telah Dikirim')

@section('header-title', 'Produk Digital Anda')

@section('content')
    <p class="greeting">Halo {{ $customer_name }},</p>
    
    <p>Pesanan Anda telah diproses dan produk digital telah siap! Berikut adalah informasi lengkap untuk mengakses produk Anda:</p>
    
    <div class="alert alert-success">
        <p style="margin: 0;"><strong>Produk Telah Dikirim</strong></p>
        <p style="margin: 5px 0 0 0;">Silakan gunakan informasi akses di bawah ini untuk produk digital Anda.</p>
    </div>
    
    <div class="order-info">
        <p><strong>Nomor Pesanan:</strong> {{ $order_number }}</p>
        <p><strong>Tanggal Pesanan:</strong> {{ $order_date }}</p>
        <p><strong>Tanggal Pembayaran:</strong> {{ $payment_date }}</p>
        <p><strong>Tanggal Pengiriman:</strong> {{ $delivery_date }}</p>
    </div>
    
    <h3 style="margin-top: 25px; margin-bottom: 12px; font-size: 15px; font-weight: 600;">Detail Produk Digital Anda</h3>
    
    @foreach($items as $item)
    <div class="product-box">
        <h4>{{ $item['product_name'] }}</h4>
        @if($item['variant_name'])
        <p class="text-small" style="margin: 4px 0;">{{ $item['variant_name'] }}</p>
        @endif
        <p class="text-small" style="margin: 4px 0;"><strong>Jumlah:</strong> {{ $item['quantity'] }}</p>
        
        @if(isset($item['delivery_content']) && $item['delivery_content'])
        <div class="content-box">
            <p class="text-xs" style="margin: 0 0 8px 0; text-transform: uppercase; font-weight: 600;">Informasi Akses</p>
            <pre style="margin: 0; font-size: 12px; color: #333; font-family: 'Courier New', monospace; white-space: pre-wrap; word-wrap: break-word; line-height: 1.5;">{{ $item['delivery_content'] }}</pre>
        </div>
        @endif
        
        @if(isset($item['instructions']) && $item['instructions'])
        <div class="alert alert-info" style="margin-top: 10px;">
            <p style="margin: 0; font-size: 12px; text-transform: uppercase; font-weight: 600;">Instruksi Penggunaan</p>
            <p style="margin: 8px 0 0 0; white-space: pre-wrap; font-size: 13px;">{{ $item['instructions'] }}</p>
        </div>
        @endif
    </div>
    @endforeach
    
    <div class="alert alert-warning" style="margin-top: 25px;">
        <p style="margin: 0;"><strong>Perhatian Penting:</strong></p>
        <ul style="margin: 8px 0 0 20px; padding: 0; font-size: 13px;">
            <li style="margin-bottom: 4px;">Simpan informasi akses produk digital Anda dengan aman</li>
            <li style="margin-bottom: 4px;">Jangan bagikan informasi ini kepada orang lain</li>
            <li style="margin-bottom: 4px;">Informasi ini hanya berlaku untuk penggunaan pribadi</li>
            <li style="margin-bottom: 4px;">Gunakan informasi akses sesuai dengan ketentuan layanan</li>
        </ul>
    </div>
    
    <div class="info-box" style="margin-top: 20px;">
        <h4>Tips Penggunaan</h4>
        <ul style="margin: 0; padding-left: 20px; font-size: 13px;">
            <li style="margin-bottom: 4px;">Pastikan Anda menyimpan email ini untuk referensi di masa mendatang</li>
            <li style="margin-bottom: 4px;">Jika ada informasi login, segera ubah password setelah login pertama kali (jika memungkinkan)</li>
            <li style="margin-bottom: 4px;">Periksa masa berlaku layanan yang tertera pada informasi akses</li>
        </ul>
    </div>
    
    <p style="margin-top: 25px; font-size: 13px;">Terima kasih telah berbelanja dengan kami! Kami berharap Anda puas dengan produk digital yang Anda terima.</p>
@endsection
