<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminUserManagementController;
use App\Http\Controllers\AdminProviderApprovalController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\CustomerBookingController;
use App\Http\Controllers\CustomerPaymentController;
use App\Http\Controllers\CustomerWebsiteController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProviderAvailabilityManagementController;
use App\Http\Controllers\ProviderCategoryManagementController;
use App\Http\Controllers\ProviderDashboardController;
use App\Http\Controllers\ProviderPayoutController;
use App\Http\Controllers\ProviderServiceManagementController;
use App\Models\User;
use App\Http\Controllers\ProfileController;

Route::get('/', [CustomerWebsiteController::class, 'home'])->name('site.home');
Route::get('/services', [CustomerWebsiteController::class, 'services'])->name('site.services.index');
Route::get('/services/data', [CustomerWebsiteController::class, 'servicesData'])->name('site.services.data');
Route::get('/services/{slug}/availability', [CustomerWebsiteController::class, 'availability'])->name('site.services.availability');
Route::get('/services/{slug}', [CustomerWebsiteController::class, 'serviceDetail'])->name('site.services.show');

Route::middleware('guest')->group(function () {
    Route::view('/login', 'auth.login')->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
    Route::post('/forgot-password/send-otp', [AuthController::class, 'sendForgotPasswordOtp'])->name('password.forgot.send_otp');
    Route::post('/forgot-password/verify-otp', [AuthController::class, 'verifyForgotPasswordOtp'])->name('password.forgot.verify_otp');
    Route::post('/forgot-password/reset', [AuthController::class, 'resetForgotPassword'])->name('password.forgot.reset');

    Route::view('/register', 'auth.register')->name('register');
    Route::view('/register/customer', 'auth.register-customer')->name('register.customer');
    Route::view('/register/provider', 'auth.register-provider')->name('register.provider');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])
        ->middleware([
            'role:'.User::ROLE_ADMIN,
            'spatie.role:admin',
        ])
        ->name('admin.dashboard');

    Route::get('/admin/providers/pending', [AdminProviderApprovalController::class, 'index'])
        ->middleware([
            'role:'.User::ROLE_ADMIN,
            'spatie.role:admin',
        ])
        ->name('admin.providers.pending');

    Route::patch('/admin/providers/{providerProfile}/approve', [AdminProviderApprovalController::class, 'approve'])
        ->middleware([
            'role:'.User::ROLE_ADMIN,
            'spatie.role:admin',
        ])
        ->name('admin.providers.approve');

    Route::get('/admin/users', [AdminUserManagementController::class, 'index'])
        ->middleware([
            'role:'.User::ROLE_ADMIN,
            'spatie.role:admin',
        ])
        ->name('admin.users.index');

    Route::get('/admin/users/data', [AdminUserManagementController::class, 'data'])
        ->middleware([
            'role:'.User::ROLE_ADMIN,
            'spatie.role:admin',
        ])
        ->name('admin.users.data');

    Route::post('/admin/users', [AdminUserManagementController::class, 'store'])
        ->middleware([
            'role:'.User::ROLE_ADMIN,
            'spatie.role:admin',
        ])
        ->name('admin.users.store');

    Route::put('/admin/users/{user}', [AdminUserManagementController::class, 'update'])
        ->middleware([
            'role:'.User::ROLE_ADMIN,
            'spatie.role:admin',
        ])
        ->name('admin.users.update');

    Route::patch('/admin/users/{user}/toggle-active', [AdminUserManagementController::class, 'toggleActive'])
        ->middleware([
            'role:'.User::ROLE_ADMIN,
            'spatie.role:admin',
        ])
        ->name('admin.users.toggle-active');

    Route::delete('/admin/users/{user}', [AdminUserManagementController::class, 'destroy'])
        ->middleware([
            'role:'.User::ROLE_ADMIN,
            'spatie.role:admin',
        ])
        ->name('admin.users.destroy');

    Route::get('/provider/dashboard', [ProviderDashboardController::class, 'index'])
        ->middleware([
            'role:'.User::ROLE_PROVIDER,
            'spatie.role:provider',
            'provider.approved',
        ])
        ->name('provider.dashboard');

    Route::prefix('/provider/schedule')
        ->middleware([
            'role:'.User::ROLE_PROVIDER,
            'spatie.role:provider',
            'provider.approved',
        ])
        ->group(function (): void {
            Route::get('/', [ProviderAvailabilityManagementController::class, 'index'])->name('provider.schedule.index');
            Route::get('/data', [ProviderAvailabilityManagementController::class, 'data'])->name('provider.schedule.data');
            Route::post('/', [ProviderAvailabilityManagementController::class, 'store'])->name('provider.schedule.store');
            Route::put('/{slot}', [ProviderAvailabilityManagementController::class, 'update'])
                ->whereUuid('slot')
                ->name('provider.schedule.update');
            Route::patch('/{slot}/toggle-block', [ProviderAvailabilityManagementController::class, 'toggleBlocked'])
                ->whereUuid('slot')
                ->name('provider.schedule.toggle-block');
            Route::delete('/{slot}', [ProviderAvailabilityManagementController::class, 'destroy'])
                ->whereUuid('slot')
                ->name('provider.schedule.destroy');
        });

    Route::get('/provider/services', [ProviderServiceManagementController::class, 'index'])
        ->middleware([
            'role:'.User::ROLE_PROVIDER,
            'spatie.role:provider',
            'provider.approved',
        ])
        ->name('provider.services.index');

    Route::get('/provider/services/data', [ProviderServiceManagementController::class, 'data'])
        ->middleware([
            'role:'.User::ROLE_PROVIDER,
            'spatie.role:provider',
            'provider.approved',
        ])
        ->name('provider.services.data');

    Route::post('/provider/services', [ProviderServiceManagementController::class, 'store'])
        ->middleware([
            'role:'.User::ROLE_PROVIDER,
            'spatie.role:provider',
            'provider.approved',
        ])
        ->name('provider.services.store');

    Route::put('/provider/services/{service}', [ProviderServiceManagementController::class, 'update'])
        ->middleware([
            'role:'.User::ROLE_PROVIDER,
            'spatie.role:provider',
            'provider.approved',
        ])
        ->name('provider.services.update');

    Route::patch('/provider/services/{service}/toggle-status', [ProviderServiceManagementController::class, 'toggleStatus'])
        ->middleware([
            'role:'.User::ROLE_PROVIDER,
            'spatie.role:provider',
            'provider.approved',
        ])
        ->name('provider.services.toggle-status');

    Route::delete('/provider/services/{service}', [ProviderServiceManagementController::class, 'destroy'])
        ->middleware([
            'role:'.User::ROLE_PROVIDER,
            'spatie.role:provider',
            'provider.approved',
        ])
        ->name('provider.services.destroy');

    Route::get('/provider/categories', [ProviderCategoryManagementController::class, 'index'])
        ->middleware([
            'role:'.User::ROLE_PROVIDER,
            'spatie.role:provider',
            'provider.approved',
        ])
        ->name('provider.categories.index');

    Route::get('/provider/categories/data', [ProviderCategoryManagementController::class, 'data'])
        ->middleware([
            'role:'.User::ROLE_PROVIDER,
            'spatie.role:provider',
            'provider.approved',
        ])
        ->name('provider.categories.data');

    Route::post('/provider/categories', [ProviderCategoryManagementController::class, 'store'])
        ->middleware([
            'role:'.User::ROLE_PROVIDER,
            'spatie.role:provider',
            'provider.approved',
        ])
        ->name('provider.categories.store');

    Route::put('/provider/categories/{serviceCategory}', [ProviderCategoryManagementController::class, 'update'])
        ->middleware([
            'role:'.User::ROLE_PROVIDER,
            'spatie.role:provider',
            'provider.approved',
        ])
        ->name('provider.categories.update');

    Route::patch('/provider/categories/{serviceCategory}/toggle-status', [ProviderCategoryManagementController::class, 'toggleStatus'])
        ->middleware([
            'role:'.User::ROLE_PROVIDER,
            'spatie.role:provider',
            'provider.approved',
        ])
        ->name('provider.categories.toggle-status');

    Route::delete('/provider/categories/{serviceCategory}', [ProviderCategoryManagementController::class, 'destroy'])
        ->middleware([
            'role:'.User::ROLE_PROVIDER,
            'spatie.role:provider',
            'provider.approved',
        ])
        ->name('provider.categories.destroy');

    Route::get('/provider/payouts', [ProviderPayoutController::class, 'index'])
        ->middleware([
            'role:'.User::ROLE_PROVIDER,
            'spatie.role:provider',
            'provider.approved',
        ])
        ->name('provider.payouts.index');

    Route::get('/user/dashboard', fn() => view('user.index'))
        ->middleware([
            'role:'.User::ROLE_CUSTOMER,
            'spatie.role:customer',
        ])
        ->name('user.dashboard');

    Route::get('/customer/dashboard', [CustomerWebsiteController::class, 'dashboard'])
        ->middleware([
            'role:'.User::ROLE_CUSTOMER,
            'spatie.role:customer',
        ])
        ->name('customer.dashboard');
    Route::get('/customer/dashboard/bookings-data', [CustomerWebsiteController::class, 'dashboardBookingsData'])
        ->middleware([
            'role:'.User::ROLE_CUSTOMER,
            'spatie.role:customer',
        ])
        ->name('customer.dashboard.bookings.data');

    Route::prefix('/customer/bookings')
        ->middleware([
            'role:'.User::ROLE_CUSTOMER,
            'spatie.role:customer',
        ])
        ->group(function (): void {
            Route::get('/', [CustomerBookingController::class, 'index'])->name('customer.bookings.index');
            Route::get('/create', [CustomerBookingController::class, 'create'])->name('customer.bookings.create');
            Route::post('/', [CustomerBookingController::class, 'store'])->name('customer.bookings.store');
            Route::get('/{booking}/reschedule', [CustomerBookingController::class, 'rescheduleForm'])->name('customer.bookings.reschedule.form');
            Route::put('/{booking}/reschedule', [CustomerBookingController::class, 'reschedule'])->name('customer.bookings.reschedule');
            Route::post('/{booking}/cancel', [CustomerBookingController::class, 'cancel'])->name('customer.bookings.cancel');
        });

    Route::prefix('/customer/payments')
        ->middleware([
            'role:'.User::ROLE_CUSTOMER,
            'spatie.role:customer',
        ])
        ->group(function (): void {
            Route::get('/', [CustomerPaymentController::class, 'index'])->name('customer.payments.index');
            Route::get('/checkout/{booking}', [CustomerPaymentController::class, 'checkout'])->name('customer.payments.checkout');
            Route::post('/online/{booking}', [CustomerPaymentController::class, 'payOnline'])->name('customer.payments.online');
            Route::post('/cash/{booking}', [CustomerPaymentController::class, 'payCash'])->name('customer.payments.cash');
            Route::post('/refund/{payment}', [CustomerPaymentController::class, 'refund'])->name('customer.payments.refund');
        });

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read', [NotificationController::class, 'read'])->name('notifications.read');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/photo/{user}', [ProfileController::class, 'showPhoto'])->name('profile.photo.show');
    Route::post('/profile/password/send-code', [ProfileController::class, 'sendPasswordResetCode'])->name('profile.password.send_code');
    Route::post('/profile/password/reset-by-code', [ProfileController::class, 'resetPasswordWithCode'])->name('profile.password.reset_by_code');
});
