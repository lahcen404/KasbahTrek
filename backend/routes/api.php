<?php

use App\Http\Controllers\Api\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\Admin\StatisticController as AdminStatisticController;
use App\Http\Controllers\Api\Admin\TourController as AdminTourController;
use App\Http\Controllers\Api\Admin\TripReportController as AdminTripReportController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\PayPalWebhookController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\StripeWebhookController;
use App\Http\Controllers\Api\TourController;
use App\Http\Controllers\Api\TripReportController;
use App\Http\Controllers\Api\VerificationController;
use Illuminate\Support\Facades\Route;

// PUBLIIIC routes

// auth routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle']);
Route::post('/webhooks/stripe', [StripeWebhookController::class, 'handle']);
Route::post('/paypal/webhook', [PayPalWebhookController::class, 'handle']);

// tours
Route::get('/tours', [TourController::class, 'index']);
Route::get('/tours/{id}', [TourController::class, 'show']);
Route::get('/tours/{id}/reviews', [ReviewController::class, 'tourReviews']);

// categories (public)
Route::get('/categories', [CategoryController::class, 'index']);

// PROTEEECTED routes
Route::middleware(['auth.custom'])->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

});

Route::middleware(['auth.custom', 'role:GUIDE'])->group(function () {

    Route::get('/guide/tours', [TourController::class, 'guideTours']);
    Route::get('/guide/reviews', [ReviewController::class, 'guideReviews']);
    Route::post('/tours', [TourController::class, 'store']);
    Route::put('/tours/{id}', [TourController::class, 'update']);
    Route::delete('/tours/{id}', [TourController::class, 'destroy']);
    Route::post('/tours/{id}/images', [TourController::class, 'uploadImages']);
    Route::delete('/tours/{tourId}/images/{imageId}', [TourController::class, 'deleteImage']);

    Route::get('/guide/bookings', [BookingController::class, 'guideBookings']);
    Route::patch('/bookings/{id}/status', [BookingController::class, 'updateStatus']);

    Route::post('/guide/verifications', [VerificationController::class, 'store']);
});

Route::middleware(['auth.custom', 'role:TRAVELER'])->group(function () {

    Route::get('/bookings/{id}', [BookingController::class, 'show']);
    Route::patch('/bookings/{id}/cancel', [BookingController::class, 'cancel']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::post('/bookings/{id}/checkout', [BookingController::class, 'checkout']);
    Route::post('/bookings/{id}/paypal/checkout', [BookingController::class, 'paypalCheckout']);
    Route::post('/bookings/{id}/paypal/capture', [BookingController::class, 'paypalCapture']);
    Route::get('/my-bookings', [BookingController::class, 'myBookings']);

    Route::get('/favorites', [FavoriteController::class, 'list']);
    Route::post('/favorites', [FavoriteController::class, 'add']);
    Route::delete('/favorites/{tourId}', [FavoriteController::class, 'remove']);

    Route::post('/reviews', [ReviewController::class, 'add']);
    Route::put('/reviews/{id}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'delete']);
    Route::get('/my-reviews', [ReviewController::class, 'myReviews']);

    Route::post('/reports', [TripReportController::class, 'store']);
});
Route::middleware(['auth.custom', 'role:ADMIN'])->prefix('admin')->group(function () {
    Route::get('/users', [AdminUserController::class, 'index']);
    Route::get('/users/{id}', [AdminUserController::class, 'show']);
    Route::put('/users/{id}', [AdminUserController::class, 'update']);
    Route::delete('/users/{id}', [AdminUserController::class, 'destroy']);

    Route::get('/verifications', [VerificationController::class, 'index']);
    Route::patch('/verifications/{id}/status', [VerificationController::class, 'updateStatus']);

    Route::get('/stats', [AdminStatisticController::class, 'index']);

    Route::get('/reports', [AdminTripReportController::class, 'index']);
    Route::patch('/reports/{id}/status', [AdminTripReportController::class, 'updateStatus']);

    Route::apiResource('categories', AdminCategoryController::class)->except(['show']);

    Route::get('/tours', [AdminTourController::class, 'index']);
    Route::delete('/tours/{id}', [AdminTourController::class, 'destroy']);
});
