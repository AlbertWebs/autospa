<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDesktopClient
{
    public function handle(Request $request, Closure $next): Response
    {
        $expectedHeader = config('desktop.client_header');
        $expectedValue = config('desktop.client_value');
        $provided = $request->header($expectedHeader);

        if (! is_string($provided) || ! hash_equals($expectedValue, $provided)) {
            return response()->json([
                'message' => 'Desktop client required.',
            ], 403);
        }

        return $next($request);
    }
}
