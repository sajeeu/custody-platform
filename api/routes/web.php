<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminWithdrawalController;

Route::prefix('api')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth');
    Route::get('/auth/me', [AuthController::class, 'me'])->middleware('auth');
});


Route::prefix('api')->middleware('auth')->group(function () {
    Route::get('/bars/me', [\App\Http\Controllers\BarController::class, 'myBars']);
    Route::get('/bars/me/available', [\App\Http\Controllers\BarController::class, 'myAvailableBars']);
    Route::get('/admin/bars', [BarController::class, 'adminList'])->middleware('auth');
});



Route::prefix('api/admin')->middleware('auth')->group(function () {
    Route::get(
        '/withdrawals/allocated',
        [AdminWithdrawalController::class, 'allocated']
    );
});