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

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Xin\Hint\Facades\Hint;

require __DIR__ . '/common.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/authorization.php';
require __DIR__ . '/advertisement.php';
require __DIR__ . '/article.php';
require __DIR__ . '/finance.php';
require __DIR__ . '/statistics.php';
require __DIR__ . '/system.php';

$fallback = Route::fallback(function () {
    return Hint::error("404 Not Found", 404, request()->path())->setStatusCode(404);
});
$fallback->methods = Router::$verbs;
Route::getRoutes()->add($fallback);
