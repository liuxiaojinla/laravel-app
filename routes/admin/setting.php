<?php

use App\Admin\Controllers\System\SettingController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('system/setting')->name('system.setting.')->group(function () {
    Route::get('/lists', [SettingController::class, 'index'])->name('lists');
    Route::get('/info', [SettingController::class, 'info'])->name('info');
    Route::get('/put', [SettingController::class, 'put'])->name('put');
    Route::get('/delete', [SettingController::class, 'delete'])->name('delete');
    Route::get('/set', [SettingController::class, 'set'])->name('set');
});
