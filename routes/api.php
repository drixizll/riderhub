<?php

use App\Http\Controllers\Api\V1\PlatformApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| RiderHub Backend REST API (v1)
|--------------------------------------------------------------------------
| Accessible by both the main Laravel app and the decoupled /frontend client.
*/

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::get('/health', [PlatformApiController::class, 'health'])->name('health');
    Route::get('/summary', [PlatformApiController::class, 'summary'])->name('summary');
    Route::post('/fare-estimate', [PlatformApiController::class, 'estimateFare'])->name('fare-estimate');
    Route::get('/weather', [PlatformApiController::class, 'weather'])->name('weather');
    Route::post('/assistant/chat', [PlatformApiController::class, 'chat'])
        ->middleware('throttle:30,1')
        ->name('assistant.chat');
});
