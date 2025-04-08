<?php

use App\Http\Controllers\AirQualityController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ImgwController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserPreferenceController;
use App\Http\Middleware\CheckUserBlocked;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::get('/air-quality', [AirQualityController::class, 'getAirQuality']);

Route::get('/synop', [ImgwController::class, 'synop']);
Route::get('/hydro', [ImgwController::class, 'hydro']);
Route::get('/meteo', [ImgwController::class, 'meteo']);
Route::get('/products', [ImgwController::class, 'products']);
Route::get('/warnings/meteo', [ImgwController::class, 'warningsMeteo']);
Route::get('/warnings/hydro', [ImgwController::class, 'warningsHydro']);

Route::group(['middleware' => ['auth:sanctum', CheckUserBlocked::class]], function () {
    Route::group(['middleware' => ['role:User|Super Admin']], function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/token/validate', [AuthController::class, 'validateToken']);

        Route::get('/user', [UserController::class, 'showCurrentUser']);
        Route::patch('/user', [UserController::class, 'updateCurrentUser']);
        Route::delete('/user', [UserController::class, 'destroyCurrentUser']);

        Route::get('/user/preferences', [UserPreferenceController::class, 'showCurrentUserPreferences']);
        Route::patch('/user/preferences', [UserPreferenceController::class, 'updateCurrentUserPreferences']);
    });

    Route::group(['middleware' => ['role:Super Admin']], function () {
        Route::get('/admintest', function (Request $request) {
            return response([
                'message' => 'Sphinx of black quartz, judge my vow'
            ], 200);
        });
    });
});
