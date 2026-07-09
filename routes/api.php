<?php

use App\Http\Controllers\Api\MpesaCallbackController;
use Illuminate\Support\Facades\Route;

Route::prefix('mpesa')->name('api.mpesa.')->group(function () {
    Route::post('stk/result', [MpesaCallbackController::class, 'stkResult'])->name('stk.result');
    Route::post('result', [MpesaCallbackController::class, 'result'])->name('result');
    Route::post('timeout', [MpesaCallbackController::class, 'timeout'])->name('timeout');
    Route::post('balance/result', [MpesaCallbackController::class, 'balanceResult'])->name('balance.result');
    Route::post('balance/timeout', [MpesaCallbackController::class, 'balanceTimeout'])->name('balance.timeout');
});
