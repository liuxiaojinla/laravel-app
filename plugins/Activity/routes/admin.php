<?php

use Illuminate\Support\Facades\Route;
use Plugins\Activity\App\Admin\Controllers\IndexController;
use Plugins\Activity\App\Admin\Controllers\JoinController;

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

Route::middleware(['auth'])->prefix('join')->name('join')->group(function () {
    Route::get('/lists', [JoinController::class, 'index']);
    Route::post('/submit', [JoinController::class, 'join']);
});
