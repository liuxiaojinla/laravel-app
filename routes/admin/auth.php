<?php

use App\Http\Admin\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Admin\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Admin\Controllers\Auth\NewPasswordController;
use App\Http\Admin\Controllers\Auth\PasswordController;
use App\Http\Admin\Controllers\Auth\PasswordResetLinkController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.account');

    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
