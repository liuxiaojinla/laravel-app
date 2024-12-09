<?php

use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\VerifyCodeController;
use Illuminate\Support\Facades\Route;

Route::get('/test', [TestController::class, 'index']);

// 基础路由
Route::controller(IndexController::class)->group(function () {
    Route::get('/index', 'index');
    Route::get('/notices', 'notices');
    Route::get('/banners', 'banners');
    Route::get('/config', 'config');
    Route::get('/agreement', 'agreement');
    Route::get('/about', 'about');
    Route::get('/regions', 'regions');
    Route::get('/languages', 'languages');
});

// 常规路由
Route::apiResource('/feedback', FeedbackController::class)->only(['index', 'store']);

// 验证码
Route::post('/verify_code', [VerifyCodeController::class, 'index']);

// 文件上传
Route::prefix('upload')->name('upload.')->group(function () {
    Route::post('/file', [UploadController::class, 'upload']);
    Route::post('/token', [UploadController::class, 'token']);
    Route::post('/notify', [UploadController::class, 'notify']);
});
