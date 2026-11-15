@extends('emails.layout')

@section('title', 'Reset Password')

@section('preheader', 'Buat password baru untuk akun Anda. Link berlaku 60 menit.')

@section('content')
    <p class="eyebrow">Reset Password</p>
    <h1>Buat password baru</h1>
    <p class="muted">Halo {{ $user_name }}, kami menerima permintaan untuk mereset password akun Anda.</p>

    <div class="btn-wrap">
        <a href="{{ $reset_url }}" class="btn">Reset Password</a>
    </div>
    <p class="muted small">Link berlaku selama 60 menit. Jika tombol tidak berfungsi, salin link ini ke browser:<br>
        <a href="{{ $reset_url }}" style="word-break: break-all;">{{ $reset_url }}</a>
    </p>

    <p class="muted small" style="margin-top: 24px;">Jika Anda tidak meminta reset password, abaikan email ini. Password Anda tidak akan berubah.</p>
@endsection
