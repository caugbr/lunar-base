<?php

use Illuminate\Support\Facades\Route;
use Plugins\FAQ\Http\Controllers\FAQController;

Route::middleware(['web', 'auth', 'role:admin,editor'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('faq', FAQController::class);
    });
