<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Notify Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "notify" middleware group. Make something great!
|
*/
Route::get('/', [\App\Http\Notify\Controllers\IndexController::class, 'index']);
