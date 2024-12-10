<?php

use Illuminate\Support\Facades\Route;
use Plugins\Mall\App\Admin\Controllers\BrandController;
use Plugins\Mall\App\Admin\Controllers\CategoryController;
use Plugins\Mall\App\Admin\Controllers\GoodsAppraiseController;
use Plugins\Mall\App\Admin\Controllers\GoodsController;
use Plugins\Mall\App\Admin\Controllers\GoodsServiceController;

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

Route::middleware(['auth'])->prefix('goods')->name('goods.')->group(function () {
    Route::get('/lists', [GoodsController::class, 'index'])->name('lists');
    Route::get('/info', [GoodsController::class, 'info'])->name('info');
    Route::post('/create', [GoodsController::class, 'store'])->name('create');
    Route::post('/update', [GoodsController::class, 'update'])->name('update');
    Route::post('/delete', [GoodsController::class, 'delete'])->name('delete');
});

Route::middleware(['auth'])->prefix('category')->name('category.')->group(function () {
    Route::get('/lists', [CategoryController::class, 'index']);
    Route::get('/info', [CategoryController::class, 'info'])->name('info');
    Route::post('/create', [CategoryController::class, 'store'])->name('create');
    Route::post('/update', [CategoryController::class, 'update'])->name('update');
    Route::post('/delete', [CategoryController::class, 'delete'])->name('delete');
});

Route::middleware(['auth'])->prefix('brand')->name('brand.')->group(function () {
    Route::get('/lists', [BrandController::class, 'index']);
    Route::get('/info', [BrandController::class, 'info'])->name('info');
    Route::post('/create', [BrandController::class, 'store'])->name('create');
    Route::post('/update', [BrandController::class, 'update'])->name('update');
    Route::post('/delete', [BrandController::class, 'delete'])->name('delete');
});

Route::middleware(['auth'])->prefix('goods_services')->name('goods_services.')->group(function () {
    Route::get('/lists', [GoodsServiceController::class, 'index']);
    Route::get('/info', [GoodsServiceController::class, 'info'])->name('info');
    Route::post('/create', [GoodsServiceController::class, 'store'])->name('create');
    Route::post('/update', [GoodsServiceController::class, 'update'])->name('update');
    Route::post('/delete', [GoodsServiceController::class, 'delete'])->name('delete');
});

Route::middleware(['auth'])->prefix('goods_appraise')->name('goods_appraise.')->group(function () {
    Route::get('/lists', [GoodsAppraiseController::class, 'index']);
});
