<?php

use Illuminate\Support\Facades\Route;
use Plugins\Shop\App\Http\Controllers\IndexController;
use Plugins\Shop\App\Http\Controllers\Manager\IndexController as ManagerIndexController;
use Plugins\Shop\App\Http\Controllers\Manager\ConfigController as ManagerConfigController;
use Plugins\Shop\App\Http\Controllers\Manager\CashoutController as ManagerCashoutController;

/*
    |--------------------------------------------------------------------------
    | API Routes
    |--------------------------------------------------------------------------
    |
    | Here is where you can register API routes for your application. These
    | routes are loaded by the RouteServiceProvider within a group which
    | is assigned the "api" middleware group. Enjoy building your API!
    |
*/

// 公共
Route::group([], function () {
    Route::get('/lists', [IndexController::class, 'index'])->name('lists');
    Route::get('/info', [IndexController::class, 'detail'])->name('info');
    Route::get('/categories', [IndexController::class, 'categories'])->name('categories');
});

// 店铺管理
Route::middleware(['auth:sanctum'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/info', [ManagerIndexController::class, 'info'])->name('info');
    Route::get('/bank', [ManagerIndexController::class, 'bank'])->name('bank');
    Route::get('/pay_qrcode', [ManagerIndexController::class, 'payQrCode'])->name('bank');
});

// 店铺配置
Route::middleware(['auth:sanctum'])->prefix('manager/config')->name('manager.config.')->group(function () {
    Route::get('/info', [ManagerConfigController::class, 'info'])->name('info');
    Route::post('/update', [ManagerConfigController::class, 'update'])->name('update');
});

// 提现管理
Route::middleware(['auth:sanctum'])->prefix('manager/cashout')->name('manager.cashout.')->group(function () {
    Route::get('/lists', [ManagerCashoutController::class, 'index'])->name('lists');
    Route::get('/info', [ManagerCashoutController::class, 'info'])->name('info');
    Route::get('/apply_info', [ManagerCashoutController::class, 'applyInfo'])->name('apply_info');
    Route::get('/apply', [ManagerCashoutController::class, 'apply'])->name('apply');
    Route::get('/apply', [ManagerCashoutController::class, 'apply'])->name('apply');
});
