<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\MeterController;
use App\Http\Controllers\API\ReadingController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

// public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/cities', [AuthController::class, 'getCities']);
Route::get('/quartiers', [AuthController::class, 'getQuartiers']);

// private routes
Route::middleware('auth:sanctum')->group(function () {
    
    // Routes of AuthController
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    
    // Routes for Meters - CRUD
    Route::apiResource('meters', MeterController::class);
    
    // Custom meter endpoints for citizen/user
    Route::get('/my-meters', [MeterController::class, 'getUserMeters']);
    Route::get('/meters-with-readings', [MeterController::class, 'getMetersWithReadings']);
    
    // Meter analytics and comparisons
    Route::get('/meters/{meterId}/monthly-data', [MeterController::class, 'getMonthlyData']);
    Route::get('/meters/{meterId}/compare-months', [MeterController::class, 'compareMonths']);
    Route::get('/meters/{meterId}/average-comparison', [MeterController::class, 'getAverageComparison']);
    
    // Routes for Readings - CRUD
    Route::apiResource('readings', ReadingController::class);
    
    // Custom reading endpoints
    Route::get('/meters/{meterId}/readings', [ReadingController::class, 'getMeterReadings']);
    Route::post('/meters/{meterId}/readings/by-date-range', [ReadingController::class, 'getReadingsByDateRange']);
    
    // Admin routes
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/users', [AdminController::class, 'getAllUsers']);
        Route::post('/admin/users', [AdminController::class, 'createUser']);
        Route::put('/admin/users/{id}', [AdminController::class, 'updateUser']);
        Route::delete('/admin/users/{id}', [AdminController::class, 'deleteUser']);
        Route::patch('/admin/users/{id}/toggle-status', [AdminController::class, 'toggleUserStatus']);
        
        Route::get('/admin/statistics', [AdminController::class, 'GlobalStatistics']);
        Route::get('/admin/consumption-stats', [MeterController::class, 'getConsumptionStatistics']);
        
        Route::get('/admin/readings/export-csv', [AdminController::class, 'exportReadingsCsv']);
        Route::get('/admin/readings/export-pdf', [AdminController::class, 'exportStatsPdf']);
    });
});