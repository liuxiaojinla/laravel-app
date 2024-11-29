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

use App\Admin\Controllers\IndexController;
use Illuminate\Support\Facades\Route;
use Xin\Hint\Facades\Hint;

Route::get('/', [IndexController::class, 'index']);

require __DIR__ . '/common.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/authorization.php';
require __DIR__ . '/advertisement.php';
require __DIR__ . '/article.php';
require __DIR__ . '/finance.php';
require __DIR__ . '/statistics.php';
require __DIR__ . '/setting.php';

Route::fallback(function () {
    return Hint::error("404 Not Found");
});
