<?php

use Illuminate\Support\Facades\Route;
use Plugins\Coupon\App\Admin\Controllers\IndexController;

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
