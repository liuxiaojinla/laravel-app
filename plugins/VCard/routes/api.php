<?php

use Illuminate\Support\Facades\Route;
use Plugins\VCard\App\Http\Controllers\BillboardController;
use Plugins\VCard\App\Http\Controllers\DynamicController;
use Plugins\VCard\App\Http\Controllers\IndexController;
use Plugins\VCard\App\Http\Controllers\Manager\IndexController as ManagerIndexController;

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

Route::prefix('billboard')->name('billboard.')->group(function () {
    Route::get('/view', [BillboardController::class, 'view'])->name('view');
    Route::get('/like', [BillboardController::class, 'like'])->name('like');
    Route::get('/collect', [BillboardController::class, 'collect'])->name('collect');
});

Route::prefix('index')->name('index.')->group(function () {
    Route::get('/', [IndexController::class, 'index'])->name('view');
    Route::get('/info', [IndexController::class, 'detail'])->name('info');
    Route::get('/browse_users', [IndexController::class, 'browseUserList'])->name('browse_users');
});

Route::prefix('dynamic')->name('dynamic.')->group(function () {
    Route::get('/view', [DynamicController::class, 'index'])->name('view');
    Route::get('/info', [DynamicController::class, 'detail'])->name('info');
    Route::post('/create', [DynamicController::class, 'create'])->name('create')->middleware(['auth:sanctum']);
});

Route::middleware(['auth:sanctum'])->prefix('manager/index')->name('manager.index.')->group(function () {
    Route::get('/', [ManagerIndexController::class, 'index'])->name('info');
    Route::post('/update', [ManagerIndexController::class, 'update'])->name('update');
    Route::post('/mini_qrcode', [ManagerIndexController::class, 'makeWeappQrcode'])->name('mini_qrcode');
});
