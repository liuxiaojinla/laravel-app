<?php

use App\Admin\Controllers\AgreementController;
use App\Admin\Controllers\FeedbackController;
use App\Admin\Controllers\LeaveMessageController;
use App\Admin\Controllers\Media\AudioController;
use App\Admin\Controllers\Media\ImageController;
use App\Admin\Controllers\Media\VideoController;
use App\Admin\Controllers\NoticeController;
use Illuminate\Support\Facades\Route;

// 协议
Route::middleware([])->prefix('agreement')->name('agreement.')->group(function () {
    Route::get('/lists', [AgreementController::class, 'index'])->name('lists');
    Route::get('/info', [AgreementController::class, 'info'])->name('info');
    Route::post('/create', [AgreementController::class, 'store'])->name('store');
    Route::put('/update', [AgreementController::class, 'update'])->name('update');
    Route::delete('/delete', [AgreementController::class, 'delete'])->name('delete');
});

// 业务反馈
Route::middleware([])->prefix('feedback')->name('feedback.')->group(function () {
    Route::get('/lists', [FeedbackController::class, 'index'])->name('lists');
    Route::get('/info', [FeedbackController::class, 'info'])->name('info');
    Route::post('/create', [FeedbackController::class, 'store'])->name('store');
    Route::put('/update', [FeedbackController::class, 'update'])->name('update');
    Route::delete('/delete', [FeedbackController::class, 'delete'])->name('delete');
});

// 用户留言
Route::middleware([])->prefix('leave_message')->name('leave_message.')->group(function () {
    Route::get('/lists', [LeaveMessageController::class, 'index'])->name('lists');
    Route::get('/info', [LeaveMessageController::class, 'info'])->name('info');
    Route::delete('/delete', [LeaveMessageController::class, 'delete'])->name('delete');
});

// 客户端通知
Route::middleware([])->prefix('notice')->name('notice.')->group(function () {
    Route::get('/lists', [NoticeController::class, 'index'])->name('lists');
    Route::get('/info', [NoticeController::class, 'info'])->name('info');
    Route::post('/create', [NoticeController::class, 'store'])->name('store');
    Route::put('/update', [NoticeController::class, 'update'])->name('update');
    Route::delete('/delete', [NoticeController::class, 'delete'])->name('delete');
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
