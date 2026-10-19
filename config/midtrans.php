<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Midtrans Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration contains settings for Midtrans payment gateway
    | integration using the Core API.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Server Key
    |--------------------------------------------------------------------------
    |
    | Your Midtrans server key. This is used for server-to-server communication.
    | For sandbox: SB-Mid-server-xxx
    | For production: Mid-server-xxx
    |
    */
    'server_key' => env('MIDTRANS_SERVER_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Client Key
    |--------------------------------------------------------------------------
    |
    | Your Midtrans client key. This is used for client-side implementation.
    | For sandbox: SB-Mid-client-xxx  
    | For production: Mid-client-xxx
    |
    */
    'client_key' => env('MIDTRANS_CLIENT_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Production Mode
    |--------------------------------------------------------------------------
    |
    | Set to true for production environment, false for sandbox.
    |
    */
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),

    /*
    |--------------------------------------------------------------------------
    | Data Sanitization
    |--------------------------------------------------------------------------
    |
    | Enable sanitization for sensitive data.
    |
    */
    'is_sanitized' => env('MIDTRANS_IS_SANITIZED', true),

    /*
    |--------------------------------------------------------------------------
    | 3D Secure
    |--------------------------------------------------------------------------
    |
    | Enable 3D Secure for card transactions.
    |
    */
    'is_3ds' => env('MIDTRANS_IS_3DS', true),

    /*
    |--------------------------------------------------------------------------
    | API URLs
    |--------------------------------------------------------------------------
    |
    | Midtrans Core API endpoints for sandbox and production.
    |
    */
    'api_url' => [
        'sandbox' => 'https://api.sandbox.midtrans.com/v2',
        'production' => 'https://api.midtrans.com/v2',
    ],

    /*
    |--------------------------------------------------------------------------
    | Snap URL (Legacy - Core API doesn't use this)
    |--------------------------------------------------------------------------
    |
    | Midtrans Snap payment page URLs.
    |
    */
    'snap_url' => [
        'sandbox' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb',
        'production' => 'https://app.midtrans.com/snap/v2/vtweb',
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification URL
    |--------------------------------------------------------------------------
    |
    | URL for receiving payment notifications from Midtrans.
    |
    */
    'notification_url' => env('MIDTRANS_NOTIFICATION_URL', '/api/midtrans/notification'),

    /*
    |--------------------------------------------------------------------------
    | Finish URL
    |--------------------------------------------------------------------------
    |
    | URL where customers are redirected after completing payment.
    |
    */
    'finish_url' => env('MIDTRANS_FINISH_URL'),

    /*
    |--------------------------------------------------------------------------
    | Unfinish URL
    |--------------------------------------------------------------------------
    |
    | URL where customers are redirected if they don't complete payment.
    |
    */
    'unfinish_url' => env('MIDTRANS_UNFINISH_URL'),

    /*
    |--------------------------------------------------------------------------
    | Error URL
    |--------------------------------------------------------------------------
    |
    | URL where customers are redirected if there's an error during payment.
    |
    */
    'error_url' => env('MIDTRANS_ERROR_URL'),

    /*
    |--------------------------------------------------------------------------
    | Core API Payment Types Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for different payment methods using Core API.
    |
    */
    'payment_types' => [
        // Virtual Account Configuration
        'bank_transfer' => [
            'bca' => [
                'va_number' => null, // Auto-generated
                'free_text' => [
                    'inquiry' => [
                        ['en' => 'Payment for Order:', 'id' => 'Pembayaran untuk Pesanan:'],
                        ['en' => 'Order Number', 'id' => 'Nomor Pesanan']
                    ],
                    'payment' => [
                        ['en' => 'Thank you for your payment', 'id' => 'Terima kasih atas pembayaran Anda']
                    ]
                ]
            ],
            'bni' => [
                'va_number' => null, // Auto-generated
            ],
            'bri' => [
                'va_number' => null, // Auto-generated
            ],
            'permata' => [
                'va_number' => null, // Auto-generated
                'recipient_name' => env('APP_NAME', 'DigitalPay')
            ]
        ],

        // E-channel (Mandiri)
        'echannel' => [
            'bill_info1' => 'Payment For:',
            'bill_info2' => 'Online Purchase'
        ],

        // E-Wallet Configuration
        'gopay' => [
            'enable_callback' => true,
            'callback_url' => env('MIDTRANS_FINISH_URL', '/payment/finish')
        ],
        'shopeepay' => [
            'callback_url' => env('MIDTRANS_FINISH_URL', '/payment/finish')
        ],
        'dana' => [
            'callback_url' => env('MIDTRANS_FINISH_URL', '/payment/finish')
        ],
        'linkaja' => [
            'callback_url' => env('MIDTRANS_FINISH_URL', '/payment/finish')
        ],
        'ovo' => [
            'callback_url' => env('MIDTRANS_FINISH_URL', '/payment/finish')
        ],

        // QRIS Configuration
        'qris' => [
            'acquirer' => 'gopay', // Default QRIS acquirer
        ],

        // Convenience Store
        'cstore' => [
            'indomaret' => [
                'store' => 'indomaret',
                'message' => 'Payment for Order'
            ],
            'alfamart' => [
                'store' => 'alfamart', 
                'message' => 'Payment for Order'
            ]
        ],

        // Credit Card (if needed)
        'credit_card' => [
            'secure' => true,
            'channel' => 'migs',
            'bank' => 'bni',
            'installment' => [
                'required' => false,
                'terms' => [
                    'bni' => [3, 6, 12],
                    'mandiri' => [3, 6, 12],
                    'cimb' => [3],
                    'bca' => [3, 6, 12],
                ]
            ]
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    |
    | Default currency for transactions.
    |
    */
    'currency' => env('MIDTRANS_CURRENCY', 'IDR'),

    /*
    |--------------------------------------------------------------------------
    | Transaction Timeout
    |--------------------------------------------------------------------------
    |
    | Default timeout for transactions in minutes.
    |
    */
    'transaction_timeout' => env('MIDTRANS_TRANSACTION_TIMEOUT', 15),
];