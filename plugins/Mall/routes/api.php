<?php

use Illuminate\Support\Facades\Route;
use Plugins\Mall\App\Http\Controllers\AdvanceOrderController;
use Plugins\Mall\App\Http\Controllers\CategoryController;
use Plugins\Mall\App\Http\Controllers\GoodsAppraiseController;
use Plugins\Mall\App\Http\Controllers\GoodsController;
use Plugins\Mall\App\Http\Controllers\ShoppingCartController;

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

Route::prefix('goods')->name('goods.')->group(function () {
    Route::get('/lists', [GoodsController::class, 'index'])->name('lists');
    Route::get('/info', [GoodsController::class, 'detail'])->name('info');
    Route::get('/sku_data', [GoodsController::class, 'skuData'])->name('sku_data');
});

Route::prefix('category')->name('category.')->group(function () {
    Route::get('/lists', [CategoryController::class, 'index'])->name('lists');
});

Route::middleware(['auth:sanctum'])->prefix('goods_appraise')->name('goods_appraise.')->group(function () {
    Route::get('/lists', [GoodsAppraiseController::class, 'index'])->name('lists');
    Route::post('/create', [GoodsAppraiseController::class, 'store'])->name('submit');
});

Route::middleware(['auth:sanctum'])->prefix('shopping_cart')->name('shopping_cart.')->group(function () {
    Route::get('/lists', [ShoppingCartController::class, 'index'])->name('lists');
    Route::post('/create', [ShoppingCartController::class, 'store'])->name('create');
    Route::post('/change', [ShoppingCartController::class, 'change'])->name('change');
    Route::post('/delete', [ShoppingCartController::class, 'delete'])->name('delete');
});

Route::middleware(['auth:sanctum'])->prefix('order')->name('order.')->group(function () {
    Route::get('/prepay', [AdvanceOrderController::class, 'fromGoods'])->name('prepay');
    Route::post('/submit', [AdvanceOrderController::class, 'store'])->name('submit');
});
