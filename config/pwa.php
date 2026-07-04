<?php

return [
    'name' => env('PWA_NAME', 'AutoSpa Pro'),
    'short_name' => env('PWA_SHORT_NAME', 'AutoSpa'),
    'description' => 'AutoSpa management system for bookings, vehicles, POS, and daily operations.',
    'theme_color' => '#6366f1',
    'background_color' => '#0f172a',
    'display' => 'standalone',
    'orientation' => 'any',
    'start_url' => '/dashboard',
    'scope' => '/',
];
