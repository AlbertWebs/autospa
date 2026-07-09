<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class ServiceWorkerController extends Controller
{
    public function __invoke(): Response
    {
        $path = public_path('sw.js');

        abort_unless(is_file($path), HttpResponse::HTTP_NOT_FOUND);

        return response(
            file_get_contents($path),
            HttpResponse::HTTP_OK,
            [
                'Content-Type' => 'application/javascript; charset=utf-8',
                'Service-Worker-Allowed' => '/',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
            ],
        );
    }
}
