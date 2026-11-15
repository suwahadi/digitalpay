<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>@yield('title')</title>
    <style>
        body { margin: 0; padding: 0; background: #ffffff; color: #111827; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; font-size: 15px; line-height: 1.6; -webkit-text-size-adjust: 100%; }
        table { border-collapse: collapse; }
        a { color: #2563eb; }
        .wrap { width: 100%; background: #ffffff; }
        .container { width: 100%; max-width: 560px; margin: 0 auto; }
        .pad { padding: 0 24px; }
        .brand { padding: 32px 24px 0; font-size: 15px; font-weight: 700; letter-spacing: -0.01em; color: #111827; text-decoration: none; }
        .eyebrow { margin: 32px 0 6px; font-size: 12px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: #6b7280; }
        .eyebrow.alert { color: #b45309; }
        h1 { margin: 0 0 12px; font-size: 22px; line-height: 1.3; font-weight: 700; letter-spacing: -0.01em; color: #111827; }
        p { margin: 0 0 14px; }
        .muted { color: #6b7280; }
        .small { font-size: 13px; }
        .amount { margin: 20px 0 4px; font-size: 30px; line-height: 1.2; font-weight: 700; letter-spacing: -0.02em; color: #111827; }
        .btn-wrap { padding: 20px 0 8px; }
        .btn { display: inline-block; padding: 12px 22px; background: #111827; color: #ffffff !important; text-decoration: none; font-weight: 600; font-size: 15px; border-radius: 8px; }
        .rule { border: 0; border-top: 1px solid #e5e7eb; margin: 28px 0; }
        .section-title { margin: 0 0 10px; font-size: 13px; font-weight: 600; color: #6b7280; }
        .rows { width: 100%; }
        .rows td { padding: 6px 0; vertical-align: top; font-size: 14px; }
        .rows td.label { color: #6b7280; padding-right: 16px; }
        .rows td.value { text-align: right; color: #111827; }
        .rows tr.total td { padding-top: 12px; border-top: 1px solid #e5e7eb; font-weight: 700; font-size: 15px; color: #111827; }
        .item-name { font-weight: 600; color: #111827; }
        .code { margin: 8px 0 18px; padding: 14px 16px; background: #f3f4f6; border-radius: 8px; font-family: 'SFMono-Regular', Menlo, Consolas, 'Courier New', monospace; font-size: 13px; line-height: 1.6; white-space: pre-wrap; word-break: break-word; color: #111827; }
        .otp { margin: 20px 0; font-family: 'SFMono-Regular', Menlo, Consolas, 'Courier New', monospace; font-size: 32px; font-weight: 700; letter-spacing: 0.3em; color: #111827; }
        .footer { padding: 0 24px 40px; font-size: 12px; line-height: 1.7; color: #9ca3af; }
        .footer a { color: #6b7280; text-decoration: none; }
        .preheader { display: none !important; visibility: hidden; opacity: 0; color: transparent; height: 0; width: 0; overflow: hidden; mso-hide: all; }
        @media only screen and (max-width: 600px) {
            .amount { font-size: 26px; }
            h1 { font-size: 20px; }
        }
    </style>
</head>
<body>
    <span class="preheader">@yield('preheader')</span>
    <table role="presentation" class="wrap" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <table role="presentation" class="container" width="560" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="brand">
                            <a href="{{ config('app.url') }}" style="color: #111827; text-decoration: none;">{{ \App\Helpers\ConfigHelper::siteName() }}</a>
                        </td>
                    </tr>
                    <tr>
                        <td class="pad">
                            @yield('content')
                            <hr class="rule">
                        </td>
                    </tr>
                    <tr>
                        <td class="footer">
                            @php
                                $contactEmail = \App\Helpers\ConfigHelper::get('contact_email', 'admin@digitalpay.web.id');
                                $contactWhatsapp = \App\Helpers\ConfigHelper::get('contact_whatsapp', '6281234567890');
                                $contactPhone = \App\Helpers\ConfigHelper::get('contact_phone', '081234567890');
                            @endphp
                            Butuh bantuan? Hubungi <a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a> atau WhatsApp <a href="https://wa.me/{{ $contactWhatsapp }}">{{ $contactPhone }}</a>.<br>
                            Email ini dikirim otomatis oleh {{ \App\Helpers\ConfigHelper::siteName() }}.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
