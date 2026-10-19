<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email - {{ config('app.name') }}</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            line-height: 1.6; 
            color: #333; 
            max-width: 600px; 
            margin: 0 auto; 
            padding: 20px;
            background-color: #f8fafc;
        }
        .container { 
            background: white; 
            padding: 40px; 
            border-radius: 12px; 
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header { 
            text-align: center; 
            margin-bottom: 30px; 
            padding-bottom: 20px;
            border-bottom: 2px solid #e5e7eb;
        }
        .logo { 
            font-size: 28px; 
            font-weight: bold; 
            color: #2563eb; 
            margin-bottom: 10px;
        }
        .title { 
            font-size: 24px; 
            font-weight: 600; 
            color: #1f2937; 
            margin-bottom: 10px;
        }
        .subtitle { 
            color: #6b7280; 
            font-size: 16px;
        }
        .otp-section { 
            text-align: center; 
            margin: 30px 0; 
            padding: 25px; 
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%); 
            border-radius: 8px;
        }
        .otp-label { 
            font-size: 16px; 
            color: #4b5563; 
            margin-bottom: 15px; 
            font-weight: 500;
        }
        .otp-code { 
            font-size: 36px; 
            font-weight: bold; 
            color: #2563eb; 
            letter-spacing: 8px; 
            font-family: 'Courier New', monospace;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }
        .instructions { 
            background: #fef3cd; 
            border: 1px solid #fbbf24; 
            border-radius: 8px; 
            padding: 20px; 
            margin: 25px 0;
        }
        .instructions h3 { 
            margin: 0 0 10px 0; 
            color: #92400e; 
            font-size: 18px;
        }
        .instructions p { 
            margin: 8px 0; 
            color: #78350f;
        }
        .footer { 
            text-align: center; 
            margin-top: 30px; 
            padding-top: 20px; 
            border-top: 1px solid #e5e7eb; 
            color: #6b7280; 
            font-size: 14px;
        }
        .footer a { 
            color: #2563eb; 
            text-decoration: none;
        }
        .security-note {
            background: #fef2f2; 
            border: 1px solid #fca5a5; 
            border-radius: 8px; 
            padding: 15px; 
            margin: 20px 0;
            font-size: 14px;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">{{ \App\Helpers\ConfigHelper::siteName() }}</div>
            <h1 class="title">Verifikasi Email Anda</h1>
            <p class="subtitle">Selamat datang, {{ $userName }}!</p>
        </div>

        <p>Terima kasih telah mendaftar di {{ \App\Helpers\ConfigHelper::siteName() }}. Untuk menyelesaikan proses registrasi, silakan verifikasi email Anda dengan memasukkan kode OTP berikut:</p>

        <div class="otp-section">
            <div class="otp-label">Kode Verifikasi Anda:</div>
            <div class="otp-code">{{ $otpCode }}</div>
        </div>

        <div class="instructions">
            <h3>Cara Verifikasi:</h3>
            <p>1. Buka halaman verifikasi di browser Anda</p>
            <p>2. Masukkan kode OTP: <strong>{{ $otpCode }}</strong></p>
            <p>3. Klik tombol "Verifikasi"</p>
            <p>4. Akun Anda akan langsung aktif</p>
        </div>

        <div class="security-note">
            <strong>Catatan Keamanan:</strong><br>
            • Kode ini akan kedaluwarsa dalam 10 menit<br>
            • Jangan bagikan kode ini kepada siapa pun<br>
            • Jika Anda tidak melakukan registrasi, abaikan email ini
        </div>

        <p>Jika Anda mengalami kesulitan, silakan hubungi tim support kami.</p>

        <div class="footer">
            <p>Email ini dikirim otomatis dari sistem {{ \App\Helpers\ConfigHelper::siteName() }}.</p>
            <p><a href="{{ config('app.url') }}">Kunjungi Website Kami</a></p>
            <p style="margin-top: 10px;">
                Butuh bantuan? Hubungi kami di: 
                <a href="mailto:{{ \App\Helpers\ConfigHelper::get('contact_email', 'admin@digitalpay.web.id') }}">{{ \App\Helpers\ConfigHelper::get('contact_email', 'admin@digitalpay.web.id') }}</a> | 
                <a href="https://wa.me/{{ \App\Helpers\ConfigHelper::get('contact_whatsapp', '6281234567890') }}">WhatsApp</a>
            </p>
        </div>
    </div>
</body>
</html>