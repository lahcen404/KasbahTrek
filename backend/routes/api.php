<?php

use App\Http\Controllers\Api\AuthController;
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
    Route::post('/tours/{id}/images', [TourController::class, 'uploadImages']);
});
