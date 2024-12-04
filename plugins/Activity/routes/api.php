<?php

use Illuminate\Support\Facades\Route;
use Plugins\Activity\App\Http\Controllers\IndexController;
use Plugins\Activity\App\Http\Controllers\JoinController;

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

Route::group(['api',], function () {
    Route::get('/lists', [IndexController::class, 'index']);
    Route::get('/info', [IndexController::class, 'detail']);
});

Route::middleware(['api', 'auth:sanctum'])->prefix('join')->name('join')->group(function () {
    Route::get('/lists', [JoinController::class, 'index']);
    Route::post('/submit', [JoinController::class, 'join']);
});
