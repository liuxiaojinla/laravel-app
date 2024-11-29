<?php

use App\Http\Controllers\Wechat\AuthorizeController;
use App\Http\Controllers\Wechat\OpenPlatformServerController;
use App\Http\Controllers\Wechat\WechatController;
use Illuminate\Support\Facades\Route;

//
Route::middleware([''])->prefix('wechat')->name('wechat.')->group(function () {
    Route::post('/code2session', [WechatController::class, 'code2session'])->name('code2session');
    Route::post('/decrypt_phone', [WechatController::class, 'decryptPhoneNumber'])->name('decrypt_phone');
});

// 微信授权相关
Route::middleware([''])->prefix('wechat/authorize')->name('wechat.authorize.')->group(function () {
    Route::post('/weapp', [AuthorizeController::class, 'weapp'])->name('weapp');
    Route::post('/official', [AuthorizeController::class, 'official'])->name('official');
    Route::post('/authorize', [AuthorizeController::class, 'authorize'])->name('authorize');
});

// 微信开放平台
Route::addRoute('GET|POST', 'wechat/open_platform', [OpenPlatformServerController::class, 'index'])->name('wechat.open_platform.server');
