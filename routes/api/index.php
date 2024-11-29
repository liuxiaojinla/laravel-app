<?php

use App\Http\Api\Controllers\BannerController;
use App\Http\Api\Controllers\FeedbackController;
use App\Http\Api\Controllers\IndexController;
use App\Http\Api\Controllers\LanguageController;
use App\Http\Api\Controllers\NoticeController;
use App\Http\Api\Controllers\TestController;
use App\Http\Api\Controllers\VerifyCodeController;
use Illuminate\Support\Facades\Route;

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
Route::get('/test', [TestController::class, 'index']);

Route::controller(IndexController::class)->group(function () {
    Route::get('/index', 'index');
    Route::get('/config', 'config');
    Route::get('/agreement', 'agreement');
    Route::get('/about', 'about');
    Route::get('/regions', 'regions');
});

Route::get('/notices', [NoticeController::class, 'index']);
Route::get('/banners', [BannerController::class, 'index']);
Route::get('/languages', [LanguageController::class, 'index']);
Route::apiResource('/feedback', FeedbackController::class)->only(['index', 'store']);

Route::post('/verify_code', [VerifyCodeController::class, 'index']);

require __DIR__ . '/api/auth.php';
require __DIR__ . '/api/article.php';
require __DIR__ . '/api/media.php';
require __DIR__ . '/api/wechat.php';