<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Outbound HTTP SSL verification
    |--------------------------------------------------------------------------
    |
    | cURL error 60 ("unable to get local issuer certificate") means PHP cannot
    | find a CA bundle. AutoSpa ships one at storage/certs/cacert.pem; override
    | with HTTP_CA_BUNDLE or disable verification locally via HTTP_VERIFY_SSL=false.
    |
    */
    'verify_ssl' => env('HTTP_VERIFY_SSL', true),

    'ca_bundle' => env('HTTP_CA_BUNDLE'),
];
