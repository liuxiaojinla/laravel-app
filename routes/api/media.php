<?php
// 文章
use App\Http\Api\Controllers\Media\AudioController;
use App\Http\Api\Controllers\Media\ImageController;
use App\Http\Api\Controllers\Media\VideoController;
use Illuminate\Support\Facades\Route;

Route::middleware([])->prefix('media/audio')->name('media.audio.')->group(function () {
    Route::get('/lists', [AudioController::class, 'index'])->name('lists');
});

Route::middleware([])->prefix('media/image')->name('media.image.')->group(function () {
    Route::get('/lists', [ImageController::class, 'index'])->name('lists');
});

Route::middleware([])->prefix('media/video')->name('media.video.')->group(function () {
    Route::get('/lists', [VideoController::class, 'index'])->name('lists');
});

