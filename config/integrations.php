<?php

return [
    'mpesa' => [
        'driver' => env('MPESA_DRIVER', 'stub'),
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
