<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/login', function() {
    return Inertia::render('login');
})->name('login');

Route::get('/', function () {
    return Inertia::render('dashboard');
})->name('dashboard');

Route::get('profile', function () {
    return Inertia::render('profile');
})->name('profile');
Route::get('/dashboard', function () {
    return Inertia::render('dashboard');
});

Route::get('/register', function () {
    return Inertia::render('register');
});

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
