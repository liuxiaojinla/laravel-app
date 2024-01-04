<?php

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "admin" middleware group. Make something great!
|
*/

use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Admin\Controllers\IndexController::class, 'index']);
