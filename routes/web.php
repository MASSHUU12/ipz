<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return Inertia::render('login');
})->name('home');

Route::get('/login', function() {
    return Inertia::render('login');
})->name('login');

Route::get('/dashboard', function () {
    return Inertia::render('dashboard');
})->name('dashboard');


Route::get('profile', function () {
    return Inertia::render('profile');
})->name('profile');

Route::get('/register', function() {
    return Inertia::render('register');
})->name('register');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);


// Route::get('/', function () {
//     return Inertia::render('welcome');
// })->name('home');

// Route::middleware(['auth', 'verified'])->group(function () {
//     Route::get('dashboard', function () {
//         return Inertia::render('dashboard');
//     })->name('dashboard');
// });

// require __DIR__.'/settings.php';
// require __DIR__.'/auth.php';
