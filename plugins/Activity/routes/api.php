<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Plugins\Activity\app\Http\Controllers\ActivityController;

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

Route::middleware(['api', 'auth:sanctum'])->group(function () {
    Route::get('info', fn(Request $request) => $request->user())->name('activity');
});
Route::group([], function () {
    Route::resource('activity', ActivityController::class)->names('activity');
});
