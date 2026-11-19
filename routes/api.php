<?php

use App\Http\Controllers\Api\Admin;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LookupController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\TripController;
use Illuminate\Support\Facades\Route;

// Prefix /api/v1 diatur di bootstrap/app.php

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });
});

// Data publik
Route::get('locations', [LookupController::class, 'locations']);
Route::get('bus-types', [LookupController::class, 'busTypes']);
Route::get('routes', [LookupController::class, 'routes']);

// Customer (wajib login)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('trips', [TripController::class, 'index']);
    Route::get('trips/{id}', [TripController::class, 'show']);

    Route::get('reservations', [ReservationController::class, 'index']);
    Route::post('reservations', [ReservationController::class, 'store']);
    Route::get('reservations/{id}', [ReservationController::class, 'show']);
    Route::post('reservations/{id}/pay', [ReservationController::class, 'pay']);
    Route::post('reservations/{id}/cancel', [ReservationController::class, 'cancel']);
});

// Admin (login + role admin)
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('dashboard', [Admin\DashboardController::class, 'index']);

    Route::apiResource('buses', Admin\BusController::class)->parameters(['buses' => 'bus']);
    Route::apiResource('routes', Admin\RouteController::class)->parameters(['routes' => 'busRoute']);
    Route::apiResource('trips', Admin\TripController::class);

    Route::get('reservations', [Admin\ReservationController::class, 'index']);
    Route::get('reservations/{id}', [Admin\ReservationController::class, 'show']);
    Route::post('reservations/{id}/cancel', [Admin\ReservationController::class, 'cancel']);
});
