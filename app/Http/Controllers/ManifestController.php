<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class ManifestController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $config = config('pwa');

        return response()->json([
            'name' => $config['name'],
            'short_name' => $config['short_name'],
            'description' => $config['description'],
            'start_url' => url($config['start_url']),
            'scope' => url($config['scope']),
            'display' => $config['display'],
            'orientation' => $config['orientation'],
            'theme_color' => $config['theme_color'],
            'background_color' => $config['background_color'],
            'icons' => [
                [
                    'src' => asset('logo.png'),
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => asset('logo.png'),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => asset('logo.png'),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'maskable',
                ],
            ],
        ])->header('Content-Type', 'application/manifest+json');
    }
}
