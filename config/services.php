<?php

return [
    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'brevo' => [
        'api_key' => env('BREVO_API_KEY'),
        'api_url' => env('BREVO_API_URL', 'https://api.brevo.com/v3'),
        'enabled' => env('BREVO_ENABLED', false),
        'sender_email' => env('BREVO_SENDER_EMAIL', 'no-reply@digitalpay.web.id'),
        'sender_name' => env('BREVO_SENDER_NAME', 'DigitalPay'),
    ],

    'payment_gateway' => env('PAYMENT_GATEWAY', 'qrisgate'),

    'qrisgate' => [
        'base_url' => rtrim(env('QRISGATE_BASE_URL', 'https://qrisgate.web.id/api/v1'), '/'),
        'api_key' => env('QRISGATE_API_KEY'),
        'webhook_secret' => env('QRISGATE_WEBHOOK_SECRET'),
        'timeout' => (int) env('QRISGATE_TIMEOUT', 15),
        'signature_tolerance' => 300,
    ],

];
