<?php

use App\Http\Controllers\ChirpController;
use App\Http\Controllers\NotificationController;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Xin\Hint\Facades\Hint;

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

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::resource('chirps', ChirpController::class)
    ->only(['index', 'create', 'store'])
    ->middleware(['auth', 'verified']);



$fallback = Route::fallback(function () {
    return Hint::error("404 Not Found", 404, request()->path())->setStatusCode(404);
});
$fallback->methods = Router::$verbs;
Route::getRoutes()->add($fallback);
