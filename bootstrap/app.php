<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/desktop.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'api/mpesa/*',
            'desktop/sync/*',
        ]);
        $middleware->alias([
            'attendance.enabled' => \App\Http\Middleware\EnsureAttendanceEnabled::class,
            'branch' => \App\Http\Middleware\EnsureBranchSelected::class,
            'permission' => \App\Http\Middleware\EnsureUserHasPermission::class,
            'installed' => \App\Http\Middleware\EnsureInstalled::class,
            'not.installed' => \App\Http\Middleware\EnsureNotInstalled::class,
            'desktop.client' => \App\Http\Middleware\EnsureDesktopClient::class,
        ]);

        $middleware->priority([
            \App\Http\Middleware\EnsureInstalled::class,
            \App\Http\Middleware\EnsureNotInstalled::class,
            \Illuminate\Auth\Middleware\Authenticate::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
