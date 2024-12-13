<?php

use Illuminate\Support\Facades\Route;
use Plugins\Website\App\Http\Controllers\ArticleCategoryController;
use Plugins\Website\App\Http\Controllers\ArticleController;
use Plugins\Website\App\Http\Controllers\CaseController;
use Plugins\Website\App\Http\Controllers\IndexController;
use Plugins\Website\App\Http\Controllers\ProductCategoryController;
use Plugins\Website\App\Http\Controllers\ProductController;
use Plugins\Wechat\App\Http\Controllers\OpenPlatformServerController;

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

Route::prefix('article')->name('article.')->group(function () {
    Route::get('/about', [IndexController::class, 'about'])->name('about');
    Route::get('/submit_leaving_msg', [ArticleController::class, 'submitLeavingMsg'])->name('submit_leaving_msg');
});

Route::prefix('article')->name('article.')->group(function () {
    Route::get('/lists', [ArticleController::class, 'index'])->name('index');
    Route::get('/info', [ArticleController::class, 'detail'])->name('info');
});

Route::prefix('article_category')->name('article_category.')->group(function () {
    Route::get('/lists', [ArticleCategoryController::class, 'index'])->name('index');
    Route::get('/info', [ArticleCategoryController::class, 'detail'])->name('info');
    Route::get('/tree', [ArticleCategoryController::class, 'tree'])->name('tree');
});

Route::prefix('case')->name('case.')->group(function () {
    Route::get('/lists', [CaseController::class, 'index'])->name('index');
    Route::get('/info', [CaseController::class, 'detail'])->name('info');
});

Route::prefix('product')->name('product.')->group(function () {
    Route::get('/lists', [ProductController::class, 'index'])->name('index');
    Route::get('/info', [ProductController::class, 'detail'])->name('info');
});

Route::prefix('product_category')->name('product_category.')->group(function () {
    Route::get('/lists', [ProductCategoryController::class, 'index'])->name('index');
    Route::get('/info', [ProductCategoryController::class, 'detail'])->name('info');
    Route::get('/tree', [ProductCategoryController::class, 'tree'])->name('tree');
});
