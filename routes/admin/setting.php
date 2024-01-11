<?php

use App\Http\Admin\Controllers\System\SettingController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('setting')->name('setting.')->group(function () {
    Route::get('/lists', [SettingController::class, 'lists'])->name('lists');
    Route::get('/read', [SettingController::class, 'read'])->name('read');
    Route::get('/put', [SettingController::class, 'put'])->name('put');
    Route::get('/delete', [SettingController::class, 'delete'])->name('delete');
    Route::get('/set', [SettingController::class, 'set'])->name('set');
});
