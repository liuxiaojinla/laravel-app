<?php

use App\Http\Admin\Controllers\Authorization\AdminController;
use App\Http\Admin\Controllers\Authorization\MenuController;
use App\Http\Admin\Controllers\Authorization\RoleController;
use Illuminate\Support\Facades\Route;

Route::middleware([])->prefix('authorization/admin')->name('authorization.admin.')->group(function () {
    Route::get('/lists', [AdminController::class, 'index'])->name('lists');
    Route::get('/read', [AdminController::class, 'read'])->name('read');
    Route::get('/put', [AdminController::class, 'put'])->name('put');
    Route::get('/delete', [AdminController::class, 'delete'])->name('delete');
    Route::get('/set', [AdminController::class, 'set'])->name('set');
});

Route::middleware([])->prefix('authorization/role')->name('authorization.role.')->group(function () {
    Route::get('/lists', [RoleController::class, 'index'])->name('lists');
    Route::get('/read', [RoleController::class, 'read'])->name('read');
    Route::get('/put', [RoleController::class, 'put'])->name('put');
    Route::get('/delete', [RoleController::class, 'delete'])->name('delete');
    Route::get('/set', [RoleController::class, 'set'])->name('set');
});

Route::middleware([])->prefix('authorization/menu')->name('authorization.menu.')->group(function () {
    Route::get('/', [MenuController::class, 'index'])->name('lists');
});
