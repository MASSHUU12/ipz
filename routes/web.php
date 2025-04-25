<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Mail\SendMail;

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

Route::get('/idkfa', function () {
    return Inertia::render('admin');
});

Route::get('/email/verify/{id}/{hash}', [\App\Http\Controllers\Auth\VerifyEmailController::class, '__invoke'])
	//->middleware(['signed', 'throttle:6,1'])
	->name('verification.verify');

Route::get('/email/verify/thank-you', function () {
	return Inertia::render('pages/VerifyEmailThankYou');
})->name('verify.email.thank-you');

Route::get('/air-quality-ranking', function () {
    return Inertia::render('airQualityRanking');
})->name('air-quality-ranking');

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