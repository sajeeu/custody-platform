<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountController;

Route::get('/health', function () {
    return response()->json(['success' => true, 'message' => 'API is running']);
});

Route::middleware(['web', 'auth'])->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/account/me', [AccountController::class, 'myAccount']);
    Route::get('/accounts', [AccountController::class, 'index']);
    Route::get('/accounts/{account}', [AccountController::class, 'show']);
});



// Route::middleware('web')->group(function () {
//     Route::post('/auth/login', [AuthController::class, 'login']);
//     Route::post('/auth/logout', [AuthController::class, 'logout']);
//     Route::get('/auth/me', [AuthController::class, 'me'])->middleware('auth');
// });

// Route::middleware('auth:sanctum')->group(function () {
//     Route::get('/account/me', [AccountController::class, 'myAccount']);
//     Route::get('/accounts', [AccountController::class, 'index']);
//     Route::get('/accounts/{account}', [AccountController::class, 'show']);
// });