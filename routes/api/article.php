<?php

use App\Http\Controllers\Article\CategoryController;
use App\Http\Controllers\Article\IndexController;
use App\Http\Controllers\Article\Manager\IndexController as ArticleManagerController;
use Illuminate\Support\Facades\Route;

// 文章
Route::middleware([])->prefix('article/index')->name('article.index.')->group(function () {
    Route::get('/lists', [IndexController::class, 'index'])->name('lists');
    Route::get('/info', [IndexController::class, 'info'])->name('info');
    Route::post('/create', [IndexController::class, 'store'])->name('store');
    Route::put('/update', [IndexController::class, 'update'])->name('update');
    Route::delete('/delete', [IndexController::class, 'delete'])->name('delete');
});

// 分类
Route::middleware([])->prefix('article/category')->name('article.category.')->group(function () {
    Route::get('/lists', [CategoryController::class, 'index'])->name('lists');
    Route::get('/info', [CategoryController::class, 'info'])->name('info');
});

// 管理
Route::middleware([])->prefix('article/manager')->name('article.manager.')->group(function () {
    Route::get('/lists', [ArticleManagerController::class, 'index'])->name('lists');
    Route::get('/info', [ArticleManagerController::class, 'info'])->name('info');
    Route::post('/create', [ArticleManagerController::class, 'store'])->name('store');
    Route::put('/update', [ArticleManagerController::class, 'update'])->name('update');
    Route::delete('/delete', [ArticleManagerController::class, 'delete'])->name('delete');
});
