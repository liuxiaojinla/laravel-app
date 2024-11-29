<?php

use App\Admin\Controllers\Finance\UserCashoutController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('finance/user_cashout')->name('finance.user_cashout.')->group(function () {
    Route::get('/lists', [UserCashoutController::class, 'index'])->name('lists');
    Route::get('/payment', [UserCashoutController::class, 'payment'])->name('payment');
    Route::post('/payment', [UserCashoutController::class, 'payment'])->name('payment.store');
});
