<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\LedgerController;
use App\Http\Controllers\AdminLedgerPostController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WithdrawalController;
use App\Http\Controllers\DepositController;

Route::get('/health', function () {
    return response()->json(['success' => true, 'message' => 'API is running']);
});

// Session-enabled routes (cookie/session works) BUT not necessarily authenticated
Route::middleware('web')->group(function () {
    // ✅ Public: login (must NOT be behind 'auth')
    Route::post('/auth/login', [AuthController::class, 'login']);

    // ✅ Authenticated session routes
    Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth');
    Route::get('/auth/me', [AuthController::class, 'me'])->middleware('auth');

    // ✅ Accounts (authenticated)
    Route::get('/account/me', [AccountController::class, 'myAccount'])->middleware('auth');
    Route::get('/accounts', [AccountController::class, 'index'])->middleware('auth');
    Route::get('/accounts/{account}', [AccountController::class, 'show'])->middleware('auth');

    // ✅ Ledger read endpoints (authenticated)
    Route::get('/ledger/me', [LedgerController::class, 'myLedger'])->middleware('auth');
    Route::get('/balances/me', [LedgerController::class, 'myBalances'])->middleware('auth');

    // ✅ Admin ledger post (authenticated + role check inside controller)
    Route::post('/admin/ledger/post', [AdminLedgerPostController::class, 'post'])->middleware('auth');

    // ✅ Deposits (session-enabled + authenticated)
    Route::post('/admin/deposits', [DepositController::class, 'create'])->middleware('auth');
    Route::get('/deposits/me', [DepositController::class, 'myDeposits'])->middleware('auth');
    Route::get('/admin/deposits', [DepositController::class, 'adminList'])->middleware('auth');
});

    // ✅ Withdrawals (session-enabled + authenticated)
    Route::middleware('web')->group(function () {
    Route::post('/withdrawals/request', [WithdrawalController::class, 'request'])->middleware('auth');
    Route::post('/withdrawals/{withdrawal}/approve', [WithdrawalController::class, 'approve'])->middleware('auth');
});

    // ✅ Withdrawals read/admin endpoints (session-enabled + authenticated)
    Route::middleware('web')->group(function () {
    Route::get('/withdrawals/me', [WithdrawalController::class, 'myWithdrawals'])->middleware('auth');
    Route::get('/admin/withdrawals', [WithdrawalController::class, 'adminQueue'])->middleware('auth');
    Route::post('/withdrawals/{withdrawal}/reject', [WithdrawalController::class, 'reject'])->middleware('auth');
});
