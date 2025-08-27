<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// API Versioning
Route::prefix('v1')->group(function () {
    require __DIR__.'/api/v1.php';
});

// Default to v1 for backward compatibility
Route::group([], function () {
    require __DIR__.'/api/v1.php';
});
