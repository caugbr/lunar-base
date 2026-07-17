<?php

use Illuminate\Support\Facades\Route;
use Plugins\Populator\Http\Controllers\PopulatorController;

Route::middleware(['web', 'auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('populator', [PopulatorController::class, 'index'])->name('populator.index');
        Route::post('populator/users', [PopulatorController::class, 'generateUsers'])->name('populator.users');
        Route::post('populator/posts', [PopulatorController::class, 'generatePosts'])->name('populator.posts');
        Route::post('populator/pages', [PopulatorController::class, 'generatePages'])->name('populator.pages');
    });
