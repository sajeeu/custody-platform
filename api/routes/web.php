<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminWithdrawalController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\LedgerController;
use App\Http\Controllers\AdminLedgerPostController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WithdrawalController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\BarController;
use App\Http\Controllers\AllocatedDepositController;
use App\Http\Controllers\DepositPostController;
use App\Http\Controllers\WithdrawalPostController;


Route::prefix('api')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth');
    Route::get('/auth/me', [AuthController::class, 'me'])->middleware('auth');
    Route::get('/admin/withdrawals/allocated', [AdminWithdrawalController::class, 'allocated']);
    
});


Route::prefix('api')->middleware('auth')->group(function () {
    Route::get('/bars/me', [\App\Http\Controllers\BarController::class, 'myBars']);
    Route::get('/bars/me/available', [\App\Http\Controllers\BarController::class, 'myAvailableBars']);
    Route::get('/admin/bars', [BarController::class, 'adminList'])->middleware('auth');
    Route::get('/deposits/me', [\App\Http\Controllers\DepositController::class, 'mine']);
    Route::post('/deposits', [\App\Http\Controllers\DepositController::class, 'store']);

});


Route::prefix('api')->middleware('auth')->group(function () {

    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/admin/withdrawals/allocated', [AdminWithdrawalController::class, 'allocated']);
    Route::get('/account/me', [AccountController::class, 'myAccount'])->middleware('auth');
    Route::get('/accounts', [AccountController::class, 'index'])->middleware('auth');
    Route::get('/accounts/{account}', [AccountController::class, 'show'])->middleware('auth');
    Route::get('/ledger/me', [LedgerController::class, 'myLedger'])->middleware('auth');
    Route::get('/balances/me', [LedgerController::class, 'myBalances'])->middleware('auth');
});


Route::prefix('api')->middleware('auth')->group(function () {
    Route::get('/withdrawals/me', [\App\Http\Controllers\WithdrawalController::class, 'me']);
    Route::post('/withdrawals/request-allocated', [\App\Http\Controllers\WithdrawalController::class, 'requestAllocated']);
});


Route::prefix('api/admin')->middleware('auth')->group(function () {
    Route::get(
        '/withdrawals/allocated',
        [AdminWithdrawalController::class, 'allocated']
    );
});

Route::prefix('api/admin')->middleware('auth')->group(function () {
    Route::get('/allocated-deposits', [\App\Http\Controllers\AdminDepositController::class, 'allocated']);
});

Route::middleware('auth')->prefix('api')->group(function () {
    Route::post('/withdrawals/{id}/approve', [\App\Http\Controllers\WithdrawalController::class, 'approve']);
    Route::post('/withdrawals/{id}/reject', [\App\Http\Controllers\WithdrawalController::class, 'reject']);
});

