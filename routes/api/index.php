<?php
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Xin\Hint\Facades\Hint;

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
require __DIR__ . '/common.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/article.php';
require __DIR__ . '/media.php';
require __DIR__ . '/wechat.php';

$fallback = Route::fallback(function () {
    return Hint::error("404 Not Found", 404, request()->path())->setStatusCode(404);
});
$fallback->methods = Router::$verbs;
Route::getRoutes()->add($fallback);
