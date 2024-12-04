<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Plugins\Mall\app\Http\Controllers\MallController;

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
    Route::get('mall', fn (Request $request) => $request->user())->name('mall');
});

Route::group([], function () {
    Route::resource('mall', MallController::class)->names('mall');
});
