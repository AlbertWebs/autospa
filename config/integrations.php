<?php

return [
    'mpesa' => [
        'driver' => env('MPESA_DRIVER', 'stub'),
    ],
    'sms' => [
        'driver' => env('SMS_DRIVER', 'stub'),
    ],
    'whatsapp' => [
        'driver' => env('WHATSAPP_DRIVER', 'stub'),
    ],
];
