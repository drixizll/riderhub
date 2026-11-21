<?php

use App\Http\Controllers\Api\V1\AuthApiController;
use App\Http\Controllers\Api\V1\BookingApiController;
use App\Http\Controllers\Api\V1\PlatformApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| RiderHub Backend REST API (v1)
|--------------------------------------------------------------------------
| Accessible by both the main Laravel app and the decoupled /frontend client.
*/

Route::prefix('v1')->name('api.v1.')->group(function () {
    // Public platform & utility endpoints
    Route::get('/health', [PlatformApiController::class, 'health'])->name('health');
    Route::get('/summary', [PlatformApiController::class, 'summary'])->name('summary');
    Route::post('/fare-estimate', [PlatformApiController::class, 'estimateFare'])->name('fare-estimate');
    Route::get('/weather', [PlatformApiController::class, 'weather'])->name('weather');
    Route::post('/assistant/chat', [PlatformApiController::class, 'chat'])
        ->middleware('throttle:30,1')
        ->name('assistant.chat');

    // Public authentication endpoints
    Route::post('/register', [AuthApiController::class, 'register'])->middleware('throttle:15,1')->name('register');
    Route::post('/login', [AuthApiController::class, 'login'])->middleware('throttle:20,1')->name('login');

    // Protected endpoints (Bearer API token)
    Route::middleware('api.token')->group(function () {
        Route::get('/me', [AuthApiController::class, 'me'])->name('me');
        Route::post('/logout', [AuthApiController::class, 'logout'])->name('logout');
        Route::get('/bookings', [BookingApiController::class, 'index'])->name('bookings.index');
        Route::post('/bookings', [BookingApiController::class, 'store'])->name('bookings.store');
    });
});
