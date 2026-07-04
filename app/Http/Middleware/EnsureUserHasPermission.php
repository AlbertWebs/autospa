<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasPermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();
        $message = 'Access denied. You do not have permission to use this area. Please contact an administrator if you believe this is a mistake.';

        if (! $user) {
            abort(403, $message);
        }

        if ($permissions === []) {
            return $next($request);
        }

        $mode = 'any';

        if (in_array($permissions[0], ['any', 'all'], true)) {
            $mode = array_shift($permissions);
        }

        $authorized = $mode === 'all'
            ? $user->hasAllPermissions($permissions)
            : $user->hasAnyPermission($permissions);

        abort_unless($authorized, 403, $message);

        return $next($request);
    }
}
