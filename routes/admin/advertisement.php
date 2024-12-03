<?php

use App\Admin\Controllers\Advertisement\ItemController;
use App\Admin\Controllers\Advertisement\PositionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('advertisement')->name('advertisement.position.')->group(function () {
    Route::get('/lists', [PositionController::class, 'index'])->name('lists');
    Route::get('/info', [PositionController::class, 'info'])->name('info');
    Route::post('/create', [PositionController::class, 'store'])->name('store');
    Route::post('/update', [PositionController::class, 'update'])->name('update');
    Route::post('/delete', [PositionController::class, 'destroy'])->name('delete');
});


Route::middleware(['auth'])->prefix('advertisement/item')->name('advertisement.item.')->group(function () {
    Route::get('/lists', [ItemController::class, 'index'])->name('lists');
    Route::get('/info', [ItemController::class, 'info'])->name('info');
    Route::post('/create', [ItemController::class, 'store'])->name('store');
    Route::post('/update', [ItemController::class, 'update'])->name('update');
    Route::post('/delete', [ItemController::class, 'destroy'])->name('delete');
});
