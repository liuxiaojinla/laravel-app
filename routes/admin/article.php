<?php

use App\Admin\Controllers\Article\CategoryController;
use App\Admin\Controllers\Article\IndexController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('article')->name('article.')->group(function () {
    Route::get('/lists', [IndexController::class, 'index'])->name('lists');
    Route::get('/info', [IndexController::class, 'info'])->name('info');
    Route::post('/create', [IndexController::class, 'store'])->name('store');
    Route::post('/update', [IndexController::class, 'update'])->name('update');
    Route::post('/delete', [IndexController::class, 'delete'])->name('delete');
    Route::post('/setvalue', [IndexController::class, 'setValue'])->name('setvalue');
});


Route::middleware(['auth'])->prefix('article/category')->name('article.category.')->group(function () {
    Route::get('/lists', [CategoryController::class, 'index'])->name('lists');
    Route::get('/info', [CategoryController::class, 'info'])->name('info');
    Route::post('/create', [CategoryController::class, 'store'])->name('store');
    Route::post('/update', [CategoryController::class, 'update'])->name('update');
    Route::post('/delete', [CategoryController::class, 'delete'])->name('delete');
    Route::post('/setvalue', [CategoryController::class, 'setValue'])->name('setvalue');
});
