<?php

use App\Admin\Controllers\System\EventController;
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

// 事件管理
Route::middleware(['auth'])->prefix('system/event')->name('system.event.')->group(function () {
    Route::get('/lists', [EventController::class, 'index'])->name('lists');
    Route::get('/info', [EventController::class, 'info'])->name('info');
    Route::post('/create', [EventController::class, 'store'])->name('create');
    Route::post('/update', [EventController::class, 'update'])->name('update');
    Route::post('/delete', [EventController::class, 'delete'])->name('delete');
});

// 菜单管理
Route::middleware(['auth'])->prefix('system/menu')->name('system.menu.')->group(function () {
    Route::get('/lists', [MenuController::class, 'index'])->name('lists');
    Route::get('/info', [MenuController::class, 'info'])->name('info');
    Route::post('/create', [MenuController::class, 'store'])->name('create');
    Route::post('/update', [MenuController::class, 'update'])->name('update');
    Route::post('/delete', [MenuController::class, 'delete'])->name('delete');
    Route::post('/sync', [MenuController::class, 'sync'])->name('sync');
});
