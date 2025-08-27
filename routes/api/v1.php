<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\TranslationController;
use App\Http\Controllers\Api\V1\ExportController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\UserController;

/*
|--------------------------------------------------------------------------
| API V1 Routes
|--------------------------------------------------------------------------
|
| Version 1 of the Translation Management Service API.
| This version includes all current functionality.
|
*/

// Authentication routes (no auth required)
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // User management
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);

    // Translation management
    Route::apiResource('translations', TranslationController::class);

    // Export functionality
    Route::get('/export/{locale}', [ExportController::class, 'export']);

    // User administration
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
});
