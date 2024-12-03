<?php

use App\Admin\Controllers\Statistics\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('statistics')->name('statistics.')->group(function () {
    Route::get('/user', [UserController::class, 'index'])->name('lists');
});
