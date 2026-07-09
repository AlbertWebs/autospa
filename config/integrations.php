<?php

return [
    'mpesa' => [
        'driver' => env('MPESA_DRIVER', 'stub'),
        'daraja' => [
            'base_url' => env('MPESA_BASE_URL', 'https://sandbox.safaricom.co.ke'),
            'consumer_key' => env('MPESA_CONSUMER_KEY'),
            'consumer_secret' => env('MPESA_CONSUMER_SECRET'),
            'shortcode' => env('MPESA_SHORTCODE'),
            'passkey' => env('MPESA_PASSKEY'),
            'initiator_name' => env('MPESA_INITIATOR_NAME'),
            'security_credential' => env('MPESA_SECURITY_CREDENTIAL'),
            'stk_result_url' => env('MPESA_STK_RESULT_URL', env('APP_URL').'/api/mpesa/stk/result'),
            'queue_timeout_url' => env('MPESA_QUEUE_TIMEOUT_URL', env('APP_URL').'/api/mpesa/timeout'),
            'result_url' => env('MPESA_RESULT_URL', env('APP_URL').'/api/mpesa/result'),
            'balance_result_url' => env('MPESA_BALANCE_RESULT_URL', env('APP_URL').'/api/mpesa/balance/result'),
            'balance_timeout_url' => env('MPESA_BALANCE_TIMEOUT_URL', env('APP_URL').'/api/mpesa/balance/timeout'),
        ],
    ],
    'sms' => [
        'driver' => env('SMS_DRIVER', 'stub'),
        'rebuetext' => [
            'base_url' => env('REBUETEXT_BASE_URL', 'https://rebuetext.com/api/v1'),
            'access_token' => env('REBUETEXT_ACCESS_TOKEN'),
            'sender_id' => env('REBUETEXT_SENDER_ID', 'AUTOSPA'),
        ],
    ],
    'whatsapp' => [
        'driver' => env('WHATSAPP_DRIVER', 'stub'),
    ],
];
