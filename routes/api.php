<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\CheckUserBlocked;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::group(['middleware' => ['auth:sanctum', CheckUserBlocked::class]], function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/user', [UserController::class, 'showCurrentUser']);
    Route::patch('/user', [UserController::class, 'updateCurrentUser']);
    Route::delete('/user', [UserController::class, 'destroyCurrentUser']);
});
