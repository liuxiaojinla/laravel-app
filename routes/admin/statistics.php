<?php

use App\Admin\Controllers\Statistics\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware([])->prefix('statistics')->name('statistics.')->group(function () {
    Route::get('/lists', [UserController::class, 'index'])->name('lists');
});
