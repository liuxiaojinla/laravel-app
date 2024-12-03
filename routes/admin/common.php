<?php

use App\Admin\Controllers\AgreementController;
use App\Admin\Controllers\FeedbackController;
use App\Admin\Controllers\LeaveMessageController;
use App\Admin\Controllers\Media\AudioController;
use App\Admin\Controllers\Media\ImageController;
use App\Admin\Controllers\Media\VideoController;
use App\Admin\Controllers\NoticeController;
use App\Http\Controllers\IndexController;
use Illuminate\Support\Facades\Route;

Route::get('/', [IndexController::class, 'index']);

// 协议
Route::middleware([])->prefix('agreement')->name('agreement.')->group(function () {
    Route::get('/lists', [AgreementController::class, 'index'])->name('lists');
    Route::get('/info', [AgreementController::class, 'info'])->name('info');
    Route::post('/create', [AgreementController::class, 'store'])->name('store');
    Route::post('/update', [AgreementController::class, 'update'])->name('update');
    Route::post('/delete', [AgreementController::class, 'delete'])->name('delete');
});

// 业务反馈
Route::middleware([])->prefix('feedback')->name('feedback.')->group(function () {
    Route::get('/lists', [FeedbackController::class, 'index'])->name('lists');
    Route::get('/info', [FeedbackController::class, 'info'])->name('info');
    Route::post('/delete', [FeedbackController::class, 'delete'])->name('delete');
});

// 用户留言
Route::middleware([])->prefix('leave_message')->name('leave_message.')->group(function () {
    Route::get('/lists', [LeaveMessageController::class, 'index'])->name('lists');
    Route::get('/info', [LeaveMessageController::class, 'info'])->name('info');
    Route::post('/delete', [LeaveMessageController::class, 'delete'])->name('delete');
});

// 公告
Route::middleware([])->prefix('notice')->name('notice.')->group(function () {
    Route::get('/lists', [NoticeController::class, 'index'])->name('lists');
    Route::get('/info', [NoticeController::class, 'info'])->name('info');
    Route::post('/create', [NoticeController::class, 'store'])->name('store');
    Route::post('/update', [NoticeController::class, 'update'])->name('update');
    Route::post('/delete', [NoticeController::class, 'delete'])->name('delete');
});

// 资源库
Route::middleware([])->prefix('media')->name('media.')->group(function () {
    Route::prefix('audio')->name('audio.')->group(function () {
        Route::get('/lists', [AudioController::class, 'index'])->name('lists');
    });
    Route::prefix('video')->name('video.')->group(function () {
        Route::get('/lists', [VideoController::class, 'index'])->name('lists');
    });
    Route::prefix('image')->name('image.')->group(function () {
        Route::get('/lists', [ImageController::class, 'index'])->name('lists');
    });
});
