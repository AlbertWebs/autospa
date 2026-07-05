<?php

namespace App\Http\Middleware;

use App\Services\InstallService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInstalled
{
    public function __construct(
        protected InstallService $installService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->installService->isInstalled()) {
            return redirect()->route('setup.welcome');
        }

        return $next($request);
    }
}
