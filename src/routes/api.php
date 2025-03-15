<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Middleware\CheckAccountSharingFraudMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::middleware(['api', CheckAccountSharingFraudMiddleware::class])->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
    });

    Route::controller(AuthController::class)->group(function () {
        Route::post('/login', 'login');
        Route::post('/refresh_token', 'refreshToken');
    });
});
