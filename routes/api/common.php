<?php

use App\Http\Controllers\BannerController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\VerifyCodeController;
use Illuminate\Support\Facades\Route;

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
