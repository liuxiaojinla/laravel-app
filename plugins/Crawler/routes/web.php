<?php

use Illuminate\Support\Facades\Route;
use Plugins\Crawler\app\Http\Controllers\CrawlerController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group([], function () {
    Route::resource('crawler', CrawlerController::class)->names('crawler');
});
