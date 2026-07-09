<?php

return [
    'enabled' => env('APP_RUNTIME') === 'electron',
    'remote_sync_url' => env('DESKTOP_REMOTE_URL'),
    'auto_sync' => env('DESKTOP_AUTO_SYNC', true),
];
