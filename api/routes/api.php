<?php


use App\Http\Controllers\AccountController;
use App\Http\Controllers\LedgerController;
use App\Http\Controllers\AdminLedgerPostController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WithdrawalController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\BarController;
use App\Http\Controllers\AllocatedDepositController;

Route::middleware(['web', 'auth:web'])->group(function () {

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

    // Allocated deposit (admin registers bars)
    Route::post('/admin/allocated-deposits', [AllocatedDepositController::class, 'create'])->middleware('auth');

    // Allocated withdrawal request
    Route::post('/withdrawals/request-allocated', [WithdrawalController::class, 'requestAllocated'])->middleware('auth');

    // Admin approves allocated withdrawal (select bars)
    Route::get('/admin/withdrawals/allocated', [WithdrawalController::class, 'adminAllocatedQueue'])->middleware('auth');

    // ✅ Admin releases allocated bars (authenticated + role check inside controller)
    Route::post('/admin/withdrawals/{withdrawal}/release-bars', [WithdrawalController::class, 'adminReleaseBars'])->middleware('auth');

    // ✅ Withdrawals (session-enabled + authenticated)
    Route::post('/withdrawals/request', [WithdrawalController::class, 'request'])->middleware('auth');
    Route::post('/withdrawals/{withdrawal}/approve', [WithdrawalController::class, 'approve'])->middleware('auth');

    // ✅ Withdrawals read/admin endpoints (session-enabled + authenticated)
    Route::get('/withdrawals/me', [WithdrawalController::class, 'myWithdrawals'])->middleware('auth');
    Route::get('/admin/withdrawals', [WithdrawalController::class, 'adminQueue'])->middleware('auth');
    Route::post('/withdrawals/{withdrawal}/reject', [WithdrawalController::class, 'reject'])->middleware('auth');


    // Route::get('/bars/me', [BarController::class, 'me']);
    // Route::get('/bars/me/available', [BarController::class, 'available']);
    // Route::get('/admin/bars', [BarController::class, 'adminList'])->middleware('auth');
});




// Route::get('/health', function () {
//     return response()->json(['success' => true, 'message' => 'API is running']);
// });