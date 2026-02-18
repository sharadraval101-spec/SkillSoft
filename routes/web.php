<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminDashboardController;
use App\Models\User;
use App\Http\Controllers\ProfileController;

Route::middleware('guest')->group(function () {
    Route::view('/', 'auth.login')->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::view('/register', 'auth.register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])
        ->middleware('role:'.User::ROLE_ADMIN)
        ->name('admin.dashboard');

    Route::get('/provider/dashboard', fn() => view('provider.index'))
        ->middleware('role:'.User::ROLE_PROVIDER)
        ->name('provider.dashboard');

    Route::get('/user/dashboard', fn() => view('user.index'))
        ->middleware('role:'.User::ROLE_USER)
        ->name('user.dashboard');
});
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/photo/{user}', [ProfileController::class, 'showPhoto'])->name('profile.photo.show');
    Route::post('/profile/password/send-code', [ProfileController::class, 'sendPasswordResetCode'])->name('profile.password.send_code');
    Route::post('/profile/password/reset-by-code', [ProfileController::class, 'resetPasswordWithCode'])->name('profile.password.reset_by_code');
});
