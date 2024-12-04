<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Plugins\Invoice\app\Http\Controllers\InvoiceController;

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
    Route::get('invoice', fn (Request $request) => $request->user())->name('invoice');
});


Route::group([], function () {
    Route::resource('invoice', InvoiceController::class)->names('invoice');
});
