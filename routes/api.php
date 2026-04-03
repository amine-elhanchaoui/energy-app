<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\MeterController;
use App\Http\Controllers\API\ReadingController;
use Illuminate\Support\Facades\Route;

// public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// private routes
Route::middleware('auth:sanctum')->group(function () {
    
    // Routes of AuthController
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    
    // route of MeterController and ReadingController
    //Route::apiResource('meters', MeterController::class);
    
   
    //Route::apiResource('readings', ReadingController::class);
    
    
    // Route::middleware('role:admin')->group(function () {
    //     Route::get('/admin/users', [AdminController::class, 'getAllUsers']);
    //     Route::get('/admin/statistics', [AdminController::class, 'getGlobalStatistics']);
    // });
});