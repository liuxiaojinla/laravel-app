<?php

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

use Illuminate\Support\Facades\Route;
use Plugins\Coupon\App\Http\Controllers\IndexController;
use Plugins\Coupon\App\Http\Controllers\UserController;

Route::get('/lists', [IndexController::class, 'index'])->name('lists');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/give', [IndexController::class, 'give'])->name('give');
    Route::get('/user/owens', [UserController::class, 'index'])->name('user.owens');
});
