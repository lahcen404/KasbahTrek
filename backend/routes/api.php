<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\TourController;
use Illuminate\Support\Facades\Route;

// PUBLIIIC routes

// auth routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// tours
Route::get('/tours', [TourController::class, 'index']);
Route::get('/tours/{id}', [TourController::class, 'show']);
Route::get('/tours/{id}/reviews', [ReviewController::class, 'tourReviews']);


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

Route::middleware(['auth.custom', 'role:TRAVELER'])->group(function () {

    Route::patch('/bookings/{id}/cancel', [BookingController::class, 'cancel']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/my-bookings', [BookingController::class, 'myBookings']);

    Route::get('/favorites', [FavoriteController::class, 'list']);
    Route::post('/favorites', [FavoriteController::class, 'add']);
    Route::delete('/favorites/{tourId}', [FavoriteController::class, 'remove']);

    Route::post('/reviews', [ReviewController::class, 'add']);
    Route::get('/my-reviews', [ReviewController::class, 'myReviews']);
});

Route::middleware(['auth.custom', 'role:ADMIN'])->prefix('admin')->group(function () {
    Route::get('/users', [AdminUserController::class, 'index']);
    Route::get('/users/{id}', [AdminUserController::class, 'show']);
    Route::put('/users/{id}', [AdminUserController::class, 'update']);
    Route::delete('/users/{id}', [AdminUserController::class, 'destroy']);
});
