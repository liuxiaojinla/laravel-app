<?php

use App\Http\Admin\Controllers\Advertisement\ItemController;
use App\Http\Admin\Controllers\Advertisement\PositionController;
use App\Http\Admin\Controllers\Article\CategoryController;
use App\Http\Admin\Controllers\Article\IndexController;
use Illuminate\Support\Facades\Route;

Route::middleware([])->prefix('article/index')->name('article.index.')->group(function () {
    Route::get('/lists', [IndexController::class, 'index'])->name('lists');
    Route::get('/info', [IndexController::class, 'info'])->name('info');
    Route::post('/create', [IndexController::class, 'store'])->name('store');
    Route::put('/update', [IndexController::class, 'update'])->name('update');
    Route::delete('/delete', [IndexController::class, 'delete'])->name('delete');
});


Route::middleware([])->prefix('article/category')->name('article.category.')->group(function () {
    Route::get('/lists', [CategoryController::class, 'index'])->name('lists');
    Route::get('/info', [CategoryController::class, 'info'])->name('info');
    Route::post('/create', [CategoryController::class, 'store'])->name('store');
    Route::put('/update', [CategoryController::class, 'update'])->name('update');
    Route::delete('/delete', [CategoryController::class, 'delete'])->name('delete');
});
