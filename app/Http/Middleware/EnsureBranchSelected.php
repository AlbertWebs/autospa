<?php

namespace App\Http\Middleware;

use App\Services\BranchService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBranchSelected
{
    public function __construct(
        protected BranchService $branchService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            $this->branchService->ensureBranchSelected($user);
        }

        return $next($request);
    }
}
