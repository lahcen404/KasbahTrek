<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\TourController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// PUBLIIIC routes

// auth routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// tours
Route::get('/tours', [TourController::class, 'index']);
Route::get('/tours/{id}', [TourController::class, 'show']);


// PROTEEECTED routes
Route::middleware(['auth.custom'])->group(function() {
    Route::post('/logout',[AuthController::class, 'logout']);

});

Route::middleware(['auth.custom', 'role:GUIDE'])->group(function() {

    Route::post('/tours', [TourController::class, 'store']);
    Route::put('/tours/{id}', [TourController::class, 'update']);
    Route::delete('/tours/{id}', [TourController::class, 'destroy']);
    Route::post('/tours/{id}/images', [TourController::class, 'uploadImages']);

    Route::get('/guide/bookings', [BookingController::class, 'guideBookings']);
    Route::patch('/bookings/{id}/status', [BookingController::class, 'updateStatus']);

});

Route::middleware(['auth.custom','role:TRAVELER'])->group(function () {

        Route::patch('/bookings/{id}/cancel', [BookingController::class, 'cancel']);
        Route::post('/bookings', [BookingController::class, 'store']);
        Route::get('/my-bookings', [BookingController::class, 'myBookings']);
    });
