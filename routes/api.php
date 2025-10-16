<?php

use App\Http\Controllers\AirQualityController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\ImgwController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserFavoriteLocationsController;
use App\Http\Controllers\UserPreferenceController;
use App\Http\Middleware\CheckUserBlocked;
use App\Http\Middleware\EnsureUserIsVerified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AddressSuggestionController;

Route::get("/addresses/suggest", [AddressSuggestionController::class, "suggest"])->name("addresses.suggest");

Route::post("/login", [AuthController::class, "login"]);
Route::post("/register", [AuthController::class, "register"]);

Route::get("/leaderboard", [LeaderboardController::class, "index"]);
Route::get("/air-quality", [AirQualityController::class, "__invoke"]);

Route::get("/synop", [ImgwController::class, "synop"]);
Route::get("/hydro", [ImgwController::class, "hydro"]);
Route::get("/meteo", [ImgwController::class, "meteo"]);
Route::get("/products", [ImgwController::class, "products"]);
Route::get("/warnings/meteo", [ImgwController::class, "warningsMeteo"]);
Route::get("/warnings/hydro", [ImgwController::class, "warningsHydro"]);

Route::post("/chatbot/message", [ChatbotController::class, "message"])->middleware("throttle:chatbot");

Route::group(["middleware" => ["auth:sanctum", CheckUserBlocked::class]], function () {
    Route::group(["middleware" => ["role:User|Super Admin"]], function () {
        Route::group(["middleware" => [EnsureUserIsVerified::class]], function () {
            Route::get("/user/preferences", [UserPreferenceController::class, "showCurrentUserPreferences"]);
            Route::patch("/user/preferences", [UserPreferenceController::class, "updateCurrentUserPreferences"]);

            Route::apiResource("favorites", UserFavoriteLocationsController::class)->parameters([
                "favorites" => "favorite",
            ]);
        });

        Route::post("/logout", [AuthController::class, "logout"]);
        Route::get("/token/validate", [AuthController::class, "validateToken"]);

        Route::get("/user", [UserController::class, "showCurrentUser"]);
        Route::patch("/user", [UserController::class, "updateCurrentUser"]);
        Route::delete("/user", [UserController::class, "destroyCurrentUser"]);
        Route::patch("/user/password", [AuthController::class, "updatePassword"]);
    });

    Route::group(["middleware" => ["role:Super Admin"]], function () {
        Route::get("/admintest", function (Request $request) {
            return response(
                [
                    "message" => "Sphinx of black quartz, judge my vow",
                ],
                200,
            );
        });
    });
});
