<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\DeviceController;

Route::middleware(['throttle:api', \App\Http\Middleware\ApiRateLimitMiddleware::class, \App\Http\Middleware\CriticalFailureMiddleware::class])->group(function () {

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/logout', [AuthController::class, 'logout']);

        Route::get('/tickets', [TicketController::class, 'index']);
        Route::get('/tickets/{id}', [TicketController::class, 'show']);
        Route::post('/tickets', [TicketController::class, 'store']);
        Route::put('/tickets/{id}', [TicketController::class, 'update']);
        Route::delete('/tickets/{id}', [TicketController::class, 'destroy']);

        Route::post('/devices/assign', [DeviceController::class, 'assign']);
        Route::get('/devices', [DeviceController::class, 'index']);
    });

    // Debug/test route (only active when APP_DEBUG=true)
    Route::match(['get', 'post'], '/debug/notify', [App\Http\Controllers\Api\DebugController::class, 'notifyTest']);

    // Temporary test routes for Discord alerts (remove in production)
    Route::get('/debug/exception', function () {
        \Illuminate\Support\Facades\Log::info('Debug exception route called');
        throw new Exception('Test exception for Discord alert');
    });
    Route::get('/debug/critical', function () {
        \Illuminate\Support\Facades\Log::info('Debug critical route called');
        abort(500, 'Test critical failure for Discord alert');
    });
});