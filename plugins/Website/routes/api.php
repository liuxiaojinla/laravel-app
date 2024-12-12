<?php

use Illuminate\Support\Facades\Route;
use Plugins\Wechat\App\Http\Controllers\OpenPlatformServerController;

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

// 微信开放平台
Route::match('GET|POST', 'wechat/open_platform', [
    OpenPlatformServerController::class, 'index',
])->name('wechat.open_platform.server');
