<?php

use App\Admin\Controllers\Authorization\AdminController;
use App\Admin\Controllers\Authorization\RoleController;
use Illuminate\Support\Facades\Route;

Route::middleware([])->prefix('authorization/admin')->name('authorization.admin.')->group(function () {
    Route::get('/lists', [AdminController::class, 'index'])->name('lists');
    Route::get('/info', [AdminController::class, 'info'])->name('info');
    Route::post('/create', [AdminController::class, 'store'])->name('store');
    Route::post('/update', [AdminController::class, 'update'])->name('update');
    Route::post('/delete', [AdminController::class, 'delete'])->name('delete');
});

Route::middleware([])->prefix('authorization/role')->name('authorization.role.')->group(function () {
    Route::get('/lists', [RoleController::class, 'index'])->name('lists');
    Route::get('/info', [RoleController::class, 'info'])->name('info');
    Route::post('/create', [RoleController::class, 'store'])->name('store');
    Route::post('/update', [RoleController::class, 'update'])->name('update');
    Route::post('/delete', [RoleController::class, 'delete'])->name('delete');
});
