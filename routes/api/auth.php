<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\User\BrowseController;
use App\Http\Controllers\User\CashoutController;
use App\Http\Controllers\User\DistributorTeamController;
use App\Http\Controllers\User\FavoriteController;
use App\Http\Controllers\User\IdentityController;
use App\Http\Controllers\User\IndexController;
use App\Http\Controllers\User\RestPasswordController;
use App\Http\Controllers\User\TeamController;
use Illuminate\Support\Facades\Route;

// 未授权
Route::middleware('guest')->group(function () {
    Route::post('login', [AuthenticatedSessionController::class, 'store'])->name('login');
    Route::post('register', [RegisteredUserController::class, 'store'])->name('register');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.account');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

// 已授权
Route::middleware('auth')->group(function () {
    Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware(['throttle:6,1'])
        ->name('verification.send');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);
    Route::put('password', [PasswordController::class, 'update'])->name('password.update');
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

// 基本信息
Route::middleware(['auth:sanctum'])->prefix('user')->name('user.')->group(function () {
    Route::get('/info', [IndexController::class, 'info'])->name('info');
    Route::get('/center', [IndexController::class, 'center'])->name('center');
    Route::post('/update', [IndexController::class, 'update'])->name('update');
    Route::post('/rest_password', [RestPasswordController::class, 'rest'])->name('rest_password');
});

// 认证管理
Route::middleware(['auth:sanctum'])->prefix('user/identity')->name('user.identity.')->group(function () {
    Route::get('/index', [IdentityController::class, 'index'])->name('index');
    Route::post('/apply', [TeamController::class, 'apply'])->name('apply');
});

// 浏览记录
Route::middleware(['auth:sanctum'])->prefix('user/browse')->name('user.browse.')->group(function () {
    Route::get('/lists', [BrowseController::class, 'index'])->name('lists');
    Route::post('/delete', [BrowseController::class, 'delete'])->name('delete');
    Route::post('/clear', [BrowseController::class, 'clear'])->name('clear');
});

// 收藏记录
Route::middleware(['auth:sanctum'])->prefix('user/favorite')->name('user.favorite.')->group(function () {
    Route::get('/lists', [FavoriteController::class, 'index'])->name('lists');
    Route::post('/favorite', [FavoriteController::class, 'favorite'])->name('favorite');
    Route::post('/unfavorite', [FavoriteController::class, 'unfavorite'])->name('unfavorite');
    Route::post('/clear', [FavoriteController::class, 'clear'])->name('clear');
});

// 提现管理
Route::middleware(['auth:sanctum'])->prefix('user/cashout')->name('user.cashout.')->group(function () {
    Route::get('/lists', [CashoutController::class, 'index'])->name('lists');
    Route::get('/info', [CashoutController::class, 'detail'])->name('info');
    Route::get('/apply_info', [CashoutController::class, 'getApplyInfo'])->name('apply_info');
    Route::post('/apply', [CashoutController::class, 'apply'])->name('apply');
});

// 分销商管理
Route::middleware(['auth:sanctum'])->prefix('user/distributor_team')->name('user.distributor_team.')->group(function () {
    Route::get('/invited_list', [DistributorTeamController::class, 'invitedList'])->name('invited_list');
    Route::get('/invited_detail', [DistributorTeamController::class, 'invitedDetail'])->name('invited_detail');
});

// 团队管理
Route::middleware(['auth:sanctum'])->prefix('user/team')->name('user.team.')->group(function () {
    Route::get('/invited_list', [TeamController::class, 'invitedList'])->name('invited_list');
    Route::get('/invited_detail', [TeamController::class, 'invitedDetail'])->name('invited_detail');
});
