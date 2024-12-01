<?php

use App\Admin\Controllers\System\IndexController;
use App\Admin\Controllers\System\MenuController;
use App\Admin\Controllers\System\SettingController;
use Illuminate\Support\Facades\Route;

// 系统操作
Route::middleware(['auth'])->prefix('system')->name('system.')->group(function () {
    Route::post('/clear_cache', [IndexController::class, 'clearCache'])->name('clear_cache');
    Route::post('/clear_setting_cache', [IndexController::class, 'clearSettingCache'])->name('clear_setting_cache');
    Route::post('/clear_menu_cache', [IndexController::class, 'clearMenuCache'])->name('clear_menu_cache');
});

// 配置管理
Route::middleware(['auth'])->prefix('system/setting')->name('system.setting.')->group(function () {
    Route::get('/lists', [SettingController::class, 'index'])->name('lists');
    Route::get('/info', [SettingController::class, 'info'])->name('info');
    Route::post('/put', [SettingController::class, 'put'])->name('put');
    Route::post('/delete', [SettingController::class, 'delete'])->name('delete');
    Route::post('/set', [SettingController::class, 'set'])->name('set');
});

// 菜单管理
Route::middleware(['auth'])->prefix('system/menu')->name('system.menu.')->group(function () {
    Route::get('/lists', [MenuController::class, 'index'])->name('lists');
    Route::get('/info', [MenuController::class, 'info'])->name('info');
    Route::post('/put', [MenuController::class, 'store'])->name('put');
    Route::post('/delete', [MenuController::class, 'delete'])->name('delete');
    Route::post('/set', [MenuController::class, 'set'])->name('set');
    Route::post('/sync', [MenuController::class, 'sync'])->name('sync');
});
