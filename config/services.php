<?php

return [

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'midtrans' => [
        'server_key' => env('MIDTRANS_SERVER_KEY', ''),
        'client_key' => env('MIDTRANS_CLIENT_KEY', ''),
        'is_production' => (bool)env('MIDTRANS_IS_PRODUCTION', false),
        'preferred_va' => env('MIDTRANS_PREFERRED_VA', 'permata'),
        'enabled_channels' => array_filter(array_map('trim', explode(',', env(
            'MIDTRANS_ENABLED_CHANNELS',
            'permata_va,gopay,qris,shopeepay,bri_va,bni_va,bca_va'
        )))),
    ],
];
