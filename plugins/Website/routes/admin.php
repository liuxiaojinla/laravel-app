<?php

use Illuminate\Support\Facades\Route;
use Plugins\Website\App\Admin\Controllers\ArticleController;
use Plugins\Website\App\Admin\Controllers\CaseController;
use Plugins\Website\App\Admin\Controllers\IndexController;
use Plugins\Website\App\Admin\Controllers\ProductController;
use Plugins\Website\App\Admin\Controllers\SettingController;

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

Route::middleware(['auth'])->group(function () {
    Route::get('/lists', [IndexController::class, 'index'])->name('lists');
    Route::get('/info', [IndexController::class, 'info'])->name('info');
    Route::post('/create', [IndexController::class, 'store'])->name('create');
    Route::post('/update', [IndexController::class, 'update'])->name('update');
    Route::post('/delete', [IndexController::class, 'delete'])->name('delete');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/lists', [CaseController::class, 'index'])->name('lists');
    Route::get('/info', [CaseController::class, 'info'])->name('info');
    Route::post('/create', [CaseController::class, 'store'])->name('create');
    Route::post('/update', [CaseController::class, 'update'])->name('update');
    Route::post('/delete', [CaseController::class, 'delete'])->name('delete');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/lists', [ArticleController::class, 'index'])->name('lists');
    Route::get('/info', [ArticleController::class, 'info'])->name('info');
    Route::post('/create', [ArticleController::class, 'store'])->name('create');
    Route::post('/update', [ArticleController::class, 'update'])->name('update');
    Route::post('/delete', [ArticleController::class, 'delete'])->name('delete');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/lists', [ProductController::class, 'index'])->name('lists');
    Route::get('/info', [ProductController::class, 'info'])->name('info');
    Route::post('/create', [ProductController::class, 'store'])->name('create');
    Route::post('/update', [ProductController::class, 'update'])->name('update');
    Route::post('/delete', [ProductController::class, 'delete'])->name('delete');
});


Route::middleware(['auth'])->group(function () {
    Route::get('/lists', [SettingController::class, 'index'])->name('lists');
    Route::get('/about', [SettingController::class, 'about'])->name('about');
});
