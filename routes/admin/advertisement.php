<?php

use App\Admin\Controllers\Advertisement\ItemController;
use App\Admin\Controllers\Advertisement\PositionController;
use Illuminate\Support\Facades\Route;

Route::middleware([])->prefix('advertisement/position')->name('advertisement.position.')->group(function () {
    Route::get('/lists', [PositionController::class, 'index'])->name('lists');
    Route::get('/info', [PositionController::class, 'info'])->name('info');
    Route::post('/create', [PositionController::class, 'store'])->name('store');
    Route::put('/update', [PositionController::class, 'update'])->name('update');
    Route::delete('/delete', [PositionController::class, 'destroy'])->name('delete');
});


Route::middleware([])->prefix('advertisement/item')->name('advertisement.item.')->group(function () {
    Route::get('/lists', [ItemController::class, 'index'])->name('lists');
    Route::get('/info', [ItemController::class, 'info'])->name('info');
    Route::post('/create', [ItemController::class, 'store'])->name('store');
    Route::put('/update', [ItemController::class, 'update'])->name('update');
    Route::delete('/delete', [ItemController::class, 'destroy'])->name('delete');
});
