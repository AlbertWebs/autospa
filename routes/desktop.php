<?php

use App\Http\Controllers\Desktop\DesktopSyncController;
use Illuminate\Support\Facades\Route;

Route::middleware(['installed', 'auth', 'verified', 'branch', 'desktop.client', 'throttle:60,1'])
    ->prefix('desktop/sync')
    ->name('desktop.sync.')
    ->group(function () {
        Route::get('ping', [DesktopSyncController::class, 'ping'])->name('ping');
        Route::get('bootstrap', [DesktopSyncController::class, 'bootstrap'])->name('bootstrap');
        Route::post('push', [DesktopSyncController::class, 'push'])->name('push');
    });
