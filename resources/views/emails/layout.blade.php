<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .content {
            padding: 30px 20px;
        }
        .greeting {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #333;
        }
        .order-info {
            background-color: #f8f9fa;
            border-left: 3px solid #667eea;
            padding: 12px 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .order-info p {
            margin: 4px 0;
            font-size: 13px;
        }
        .order-info strong {
            color: #667eea;
            font-weight: 600;
        }
        .info-box {
            background-color: #f8f9fa;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
            border: 1px solid #e9ecef;
        }
        .info-box h4 {
            margin: 0 0 10px 0;
            font-size: 14px;
            font-weight: 600;
            color: #333;
        }
        .product-box {
            background-color: #f8f9fa;
            padding: 15px;
            margin: 12px 0;
            border-radius: 4px;
            border: 1px solid #e9ecef;
        }
        .product-box h4 {
            margin: 0 0 8px 0;
            font-size: 15px;
            font-weight: 600;
            color: #333;
        }
        .content-box {
            background-color: #ffffff;
            padding: 12px;
            margin: 12px 0;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }
        .text-small {
            font-size: 12px;
            color: #6c757d;
        }
        .text-xs {
            font-size: 11px;
            color: #6c757d;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .items-table th {
            background-color: #f8f9fa;
            padding: 8px 10px;
            text-align: left;
            font-weight: 600;
            font-size: 12px;
            border-bottom: 1px solid #dee2e6;
        }
        .items-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #dee2e6;
            font-size: 13px;
        }
        .total-section {
            margin-top: 15px;
            padding: 12px 15px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 4px 0;
            font-size: 13px;
        }
        .total-row.grand-total {
            font-size: 15px;
            font-weight: 600;
            color: #667eea;
            border-top: 1px solid #dee2e6;
            padding-top: 8px;
            margin-top: 8px;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 600;
            text-align: center;
            margin: 20px 0;
        }
        .button:hover {
            opacity: 0.9;
        }
        .alert {
            padding: 12px 15px;
            margin: 15px 0;
            border-radius: 4px;
            border-left: 3px solid;
            font-size: 13px;
        }
        .alert-info {
            background-color: #d1ecf1;
            border-color: #0c5460;
            color: #0c5460;
        }
        .alert-success {
            background-color: #d4edda;
            border-color: #155724;
            color: #155724;
        }
        .alert-warning {
            background-color: #fff3cd;
            border-color: #856404;
            color: #856404;
        }
        .alert-danger {
            background-color: #f8d7da;
            border-color: #721c24;
            color: #721c24;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 15px 20px;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
            border-top: 1px solid #dee2e6;
        }
        .footer p {
            margin: 4px 0;
        }
        @media only screen and (max-width: 600px) {
            .container {
                margin: 0;
                border-radius: 0;
            }
            .content {
                padding: 20px 15px;
            }
            .items-table th,
            .items-table td {
                padding: 8px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>@yield('header-title')</h1>
        </div>
        
        <div class="content">
            @yield('content')
        </div>
        
        <div class="footer">
            <p><strong>{{ \App\Helpers\ConfigHelper::siteName() }}</strong></p>
            <p>Email ini dikirim secara otomatis, mohon tidak membalas email ini.</p>
            <p>Jika Anda memiliki pertanyaan, silakan hubungi customer service kami di:</p>
            <p>
                Email: <a href="mailto:{{ \App\Helpers\ConfigHelper::get('contact_email', 'admin@digitalpay.web.id') }}" style="color: #667eea;">{{ \App\Helpers\ConfigHelper::get('contact_email', 'admin@digitalpay.web.id') }}</a> | 
                WhatsApp: <a href="https://wa.me/{{ \App\Helpers\ConfigHelper::get('contact_whatsapp', '6281234567890') }}" style="color: #667eea;">{{ \App\Helpers\ConfigHelper::get('contact_phone', '081234567890') }}</a>
            </p>
        </div>
    </div>
</body>
</html>
