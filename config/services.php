<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

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

    'manual_qris' => [
        'enabled' => env('MANUAL_QRIS_ENABLED', false),
        'image' => '/images/payment/qris-static.webp',
        'expires_minutes' => (int) env('MANUAL_QRIS_EXPIRES_MINUTES', 60),
    ],

];
