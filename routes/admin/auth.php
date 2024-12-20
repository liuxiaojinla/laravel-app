<?php

use App\Admin\Controllers\Auth\AuthenticatedSessionController;
use App\Admin\Controllers\Auth\ConfirmablePasswordController;
use App\Admin\Controllers\Auth\MenuController;
use App\Admin\Controllers\Auth\NewPasswordController;
use App\Admin\Controllers\Auth\PasswordController;
use App\Admin\Controllers\Auth\PasswordResetLinkController;
use App\Admin\Controllers\Auth\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['guest'])->group(function () {
    Route::post('login', [AuthenticatedSessionController::class, 'store'])->name('login');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.account');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::middleware(['auth'])->group(function () {
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);
    Route::put('password', [PasswordController::class, 'update'])->name('password.update');
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

Route::middleware(['auth'])->prefix('auth')->name('auth.')->group(function () {
    Route::get('/info', [ProfileController::class, 'info'])->name('info');
    Route::get('/menus', [MenuController::class, 'index'])->name('menus');
});
