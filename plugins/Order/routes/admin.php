<?php

use Illuminate\Support\Facades\Route;
use Plugins\Order\App\Admin\Controllers\ExpressController;
use Plugins\Order\App\Admin\Controllers\FreightTemplateController;
use Plugins\Order\App\Admin\Controllers\IndexController;
use Plugins\Order\App\Admin\Controllers\RefundController;
use Plugins\Order\App\Admin\Controllers\ReturnAddressController;
use Plugins\Order\App\Admin\Controllers\VerifyController;

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

Route::middleware(['auth'])->prefix('order')->name('order.')->group(function () {
    Route::get('/lists', [IndexController::class, 'index'])->name('lists');
    Route::get('/info', [IndexController::class, 'info'])->name('info');
    Route::post('/create', [IndexController::class, 'store'])->name('create');
    Route::post('/update', [IndexController::class, 'update'])->name('update');
    Route::post('/delete', [IndexController::class, 'delete'])->name('delete');
    Route::post('/change_amount', [IndexController::class, 'changeAmount'])->name('change_amount');
    Route::post('/confirm_cancel', [IndexController::class, 'confirmCancel'])->name('confirm_cancel');
    Route::post('/extract', [IndexController::class, 'extract'])->name('extract');
    Route::post('/send', [IndexController::class, 'send'])->name('send');
});

Route::middleware(['auth'])->prefix('refund')->name('refund.')->group(function () {
    Route::get('/lists', [RefundController::class, 'index'])->name('lists');
    Route::get('/info', [RefundController::class, 'detail'])->name('info');
    Route::post('/audit', [RefundController::class, 'audit'])->name('audit');
    Route::post('/refuse', [RefundController::class, 'refuse'])->name('refuse');
    Route::post('/receipt', [RefundController::class, 'receipt'])->name('receipt');
    Route::post('/refund', [RefundController::class, 'refund'])->name('refund');
});

Route::middleware(['auth'])->prefix('return_address')->name('return_address.')->group(function () {
    Route::get('/lists', [ReturnAddressController::class, 'index'])->name('lists');
    Route::get('/info', [ReturnAddressController::class, 'info'])->name('info');
    Route::post('/create', [ReturnAddressController::class, 'store'])->name('create');
    Route::post('/update', [ReturnAddressController::class, 'update'])->name('update');
    Route::post('/delete', [ReturnAddressController::class, 'delete'])->name('delete');
});

Route::middleware(['auth'])->prefix('express')->name('express.')->group(function () {
    Route::get('/lists', [ExpressController::class, 'index'])->name('lists');
    Route::get('/info', [ExpressController::class, 'info'])->name('info');
    Route::post('/create', [ExpressController::class, 'store'])->name('create');
    Route::post('/update', [ExpressController::class, 'update'])->name('update');
    Route::post('/delete', [ExpressController::class, 'delete'])->name('delete');
});

Route::middleware(['auth'])->prefix('freight_template')->name('freight_template.')->group(function () {
    Route::get('/lists', [FreightTemplateController::class, 'index'])->name('lists');
    Route::get('/info', [FreightTemplateController::class, 'info'])->name('info');
    Route::post('/create', [FreightTemplateController::class, 'store'])->name('create');
    Route::post('/update', [FreightTemplateController::class, 'update'])->name('update');
    Route::post('/delete', [FreightTemplateController::class, 'delete'])->name('delete');
});

Route::middleware(['auth'])->prefix('verify')->name('verify.')->group(function () {
    Route::get('/lists', [VerifyController::class, 'index'])->name('lists');
    Route::get('/info', [VerifyController::class, 'logs'])->name('info');
    Route::post('/verify', [VerifyController::class, 'verify'])->name('verify');
});

