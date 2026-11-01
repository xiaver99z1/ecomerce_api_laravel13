<?php

use App\Http\Controllers\Api\V1\Admin\Auth\AuthController;
use App\Http\Controllers\Api\V1\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public Authentication Route
    Route::post('/auth/login', [AuthController::class, 'authenticate']);

    // Protected Routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Admin User Management Routes (ABAC protected)
        Route::get('/admin/users', [UserController::class, 'index']);
        Route::put('/admin/users/{targetUser}/attributes', [UserController::class, 'updateAttributes']);
    });
});
