<?php

use Illuminate\Support\Facades\Route;
use Plugins\Order\App\Http\Controllers\IndexController;
use Plugins\Order\App\Http\Controllers\LogisticsController;
use Plugins\Order\App\Http\Controllers\OrderPaidController;
use Plugins\Order\App\Http\Controllers\OrderPaidNotifyController;
use Plugins\Order\App\Http\Controllers\OrderRefundNotifyController;
use Plugins\Order\App\Http\Controllers\RefundApplyController;
use Plugins\Order\App\Http\Controllers\RefundController;
use Plugins\Order\App\Models\Express;

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
Route::middleware(['auth:sanctum'])->prefix('index')->name('index.')->group(function () {
    Route::get('/lists', [IndexController::class, 'index'])->name('lists');
    Route::get('/info', [IndexController::class, 'detail'])->name('info');
    Route::post('/delete', [IndexController::class, 'delete'])->name('delete');
    Route::post('/cancel', [IndexController::class, 'cancel'])->name('cancel');
    Route::post('/receipt', [IndexController::class, 'receipt'])->name('receipt');
});

Route::middleware(['auth:sanctum'])->prefix('paid')->name('paid.')->group(function () {
    Route::post('', [OrderPaidController::class, 'index']);
    Route::match('GET|POST', '/wechat_notify', [OrderPaidNotifyController::class, 'wechat'])->name('wechat_notify');
});

Route::middleware(['auth:sanctum'])->prefix('logistics')->name('logistics.')->group(function () {
    Route::get('/expresses', [LogisticsController::class, 'index'])->name('lists');
    Route::get('/tracks', [LogisticsController::class, 'tracks'])->name('tracks');
});

Route::middleware(['auth:sanctum'])->prefix('refund')->name('refund.')->group(function () {
    Route::get('/lists', [RefundController::class, 'index'])->name('lists');
    Route::get('/info', [RefundController::class, 'detail'])->name('info');
    Route::post('/delete', [RefundController::class, 'delete'])->name('delete');
    Route::post('/cancel', [RefundController::class, 'cancel'])->name('cancel');
    Route::post('/delivery', [RefundController::class, 'delivery'])->name('delivery');
    Route::post('/apply', [RefundApplyController::class, 'index'])->name('apply');
});
Route::match('GET|POST', '/refund_notify', [OrderRefundNotifyController::class, 'receipt'])->name('refund_notify');
