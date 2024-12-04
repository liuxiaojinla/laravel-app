<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Plugins\Shop\app\Http\Controllers\ShopController;

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

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::get('shop', fn (Request $request) => $request->user())->name('shop');
});

Route::group([], function () {
    Route::resource('shop', ShopController::class)->names('shop');
});
