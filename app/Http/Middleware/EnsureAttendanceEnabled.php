<?php

namespace App\Http\Middleware;

use App\Support\AttendanceSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAttendanceEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! AttendanceSettings::enabled()) {
            abort(404);
        }

        return $next($request);
    }
}
