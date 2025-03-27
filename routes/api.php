<?php

use App\Http\Controllers\AirQualityController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\CheckUserBlocked;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::get('/air-quality', [AirQualityController::class, 'getAirQuality']);

Route::group(
    ['middleware' => ['auth:sanctum', 'jws.verify', CheckUserBlocked::class]],
    function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/token/validate', [AuthController::class, 'validateToken']);

        Route::get('/user', [UserController::class, 'showCurrentUser']);
        Route::patch('/user', [UserController::class, 'updateCurrentUser']);
        Route::delete('/user', [UserController::class, 'destroyCurrentUser']);

        Route::get('/jwstest', function (): JsonResponse {
            return response()->json(['data' => ':)']);
        });
    }
);
