@extends('emails.layout')

@section('title', 'Pesanan Kedaluwarsa')

@section('header-title', 'Pesanan Kedaluwarsa')

@section('content')
    <p class="greeting">Halo {{ $customer_name }},</p>

    <p>Batas waktu pembayaran untuk pesanan Anda telah berakhir, sehingga pesanan dibatalkan otomatis oleh sistem.</p>

    <div class="alert alert-warning">
        <p style="margin: 0;"><strong>Pesanan Kedaluwarsa</strong></p>
        <p style="margin: 5px 0 0 0;">Kami tidak menerima pembayaran hingga batas waktu {{ $expired_at }}.</p>
    </div>

    <div class="order-info">
        <p><strong>Nomor Pesanan:</strong> {{ $order_number }}</p>
        <p><strong>Tanggal Pesanan:</strong> {{ $order_date }}</p>
        <p><strong>Batas Pembayaran:</strong> {{ $expired_at }}</p>
    </div>

    <h3 style="margin-top: 25px; margin-bottom: 12px; font-size: 15px; font-weight: 600;">Detail Pesanan yang Kedaluwarsa</h3>

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
        <h4>Sudah Terlanjur Membayar?</h4>
        <p style="margin: 0; font-size: 13px;">Jika Anda sudah melakukan pembayaran untuk pesanan ini, segera hubungi kami melalui kontak di bawah dengan menyertakan nomor pesanan dan bukti pembayaran.</p>
    </div>

    <div class="alert alert-info" style="margin-top: 15px;">
        <p style="margin: 0;"><strong>Ingin Memesan Lagi?</strong></p>
        <p style="margin: 8px 0 0 0; font-size: 13px;">Anda dapat membuat pesanan baru kapan saja. Kami siap melayani Anda!</p>
    </div>

    <div style="text-align: center;">
        <a href="{{ $order_again_url }}" class="button">Pesan Lagi</a>
    </div>

    <p style="margin-top: 25px;">Terima kasih atas pengertian Anda.</p>
@endsection
