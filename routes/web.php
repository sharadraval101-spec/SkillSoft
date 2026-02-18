<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
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

    // Admin -> resources/views/admin/index.blade.php
    Route::get('/admin/dashboard', fn() => view('admin.index'))
        ->middleware('role:'.User::ROLE_ADMIN);

    // Provider -> resources/views/provider/index.blade.php
    Route::get('/provider/dashboard', fn() => view('provider.index'))
        ->middleware('role:'.User::ROLE_PROVIDER);

    // User -> resources/views/user/index.blade.php
    Route::get('/user/dashboard', fn() => view('user.index'))
        ->middleware('role:'.User::ROLE_USER);
});
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

// Route::get('/', function () {
//     return view('welcome');
// });
