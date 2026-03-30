<?php

use App\Http\Controllers\Api\V1\BookingController as ApiBookingController;
use App\Http\Controllers\Api\V1\NotificationController as ApiNotificationController;
use App\Http\Controllers\Api\V1\PaymentController as ApiPaymentController;
use App\Http\Controllers\Api\V1\ScheduleController as ApiScheduleController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/me', function (Request $request) {
        /** @var \App\Models\User $user */
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'uuid' => $user->uuid,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->getRoleName(),
        ]);
    });

    Route::middleware([
        'role:'.User::ROLE_ADMIN,
        'spatie.role:admin',
        'spatie.permission:reports.update',
    ])->get('/admin/access-check', fn () => response()->json(['ok' => true, 'scope' => 'admin']));

    Route::middleware([
        'role:'.User::ROLE_PROVIDER,
        'spatie.role:provider',
        'provider.approved',
        'spatie.permission:services.update',
    ])->get('/provider/access-check', fn () => response()->json(['ok' => true, 'scope' => 'provider']));

    Route::middleware([
        'role:'.User::ROLE_CUSTOMER,
        'spatie.role:customer',
        'spatie.permission:bookings.create',
    ])->get('/customer/access-check', fn () => response()->json(['ok' => true, 'scope' => 'customer']));

    Route::prefix('security')->group(function () {
        Route::post('/bookings', fn () => response()->json(['ok' => true, 'permission' => 'bookings.create']))
            ->middleware('spatie.permission:bookings.create');
        Route::put('/bookings/{id}', fn (string $id) => response()->json(['ok' => true, 'permission' => 'bookings.update', 'id' => $id]))
            ->middleware('spatie.permission:bookings.update');

        Route::post('/services', fn () => response()->json(['ok' => true, 'permission' => 'services.create']))
            ->middleware('spatie.permission:services.create');
        Route::put('/services/{id}', fn (string $id) => response()->json(['ok' => true, 'permission' => 'services.update', 'id' => $id]))
            ->middleware('spatie.permission:services.update');

        Route::post('/payments', fn () => response()->json(['ok' => true, 'permission' => 'payments.create']))
            ->middleware('spatie.permission:payments.create');
        Route::put('/payments/{id}', fn (string $id) => response()->json(['ok' => true, 'permission' => 'payments.update', 'id' => $id]))
            ->middleware('spatie.permission:payments.update');

        Route::post('/reports', fn () => response()->json(['ok' => true, 'permission' => 'reports.create']))
            ->middleware('spatie.permission:reports.create');
        Route::put('/reports/{id}', fn (string $id) => response()->json(['ok' => true, 'permission' => 'reports.update', 'id' => $id]))
            ->middleware('spatie.permission:reports.update');
    });

    Route::prefix('v1')
        ->middleware([
            'role:'.User::ROLE_PROVIDER,
            'spatie.role:provider',
            'provider.approved',
        ])
        ->group(function (): void {
            Route::post('/schedule/block', [ApiScheduleController::class, 'block']);
            Route::get('/schedule/slots', [ApiScheduleController::class, 'slots']);
        });

    Route::prefix('v1')
        ->middleware([
            'role:'.User::ROLE_CUSTOMER,
            'spatie.role:customer',
        ])
        ->group(function (): void {
            Route::post('/bookings', [ApiBookingController::class, 'store'])
                ->middleware('spatie.permission:bookings.create');
            Route::put('/bookings/{id}/reschedule', [ApiBookingController::class, 'reschedule'])
                ->middleware('spatie.permission:bookings.update');
            Route::post('/bookings/{id}/cancel', [ApiBookingController::class, 'cancel'])
                ->middleware('spatie.permission:bookings.update');
            Route::post('/payments/online', [ApiPaymentController::class, 'online'])
                ->middleware('spatie.permission:payments.create');
            Route::post('/payments/cash', [ApiPaymentController::class, 'cash'])
                ->middleware('spatie.permission:payments.create');
            Route::post('/payments/refund', [ApiPaymentController::class, 'refund'])
                ->middleware('spatie.permission:payments.update');
        });

    Route::prefix('v1')->group(function (): void {
        Route::get('/notifications', [ApiNotificationController::class, 'index']);
        Route::post('/notifications/read', [ApiNotificationController::class, 'read']);
    });
});
