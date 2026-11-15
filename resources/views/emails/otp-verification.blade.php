@extends('emails.layout')

@section('title', 'Kode Verifikasi')

@section('preheader', 'Kode verifikasi Anda: '.$otp_code.'. Berlaku 10 menit.')

@section('content')
    <p class="eyebrow">Verifikasi Email</p>
    <h1>Kode verifikasi Anda</h1>
    <p class="muted">Halo {{ $user_name }}, masukkan kode berikut di halaman verifikasi untuk mengaktifkan akun Anda.</p>

    <p class="otp">{{ $otp_code }}</p>
    <p class="muted small">Berlaku selama 10 menit.</p>

    <p class="muted small" style="margin-top: 24px;">Jangan bagikan kode ini kepada siapa pun, termasuk yang mengaku sebagai tim kami. Jika Anda tidak mendaftar, abaikan email ini.</p>
@endsection
