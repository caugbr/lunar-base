<?php

use Illuminate\Support\Facades\Route;
use Plugins\Share\Http\Controllers\ShareController;

Route::middleware(['web', 'auth'])->group(function () {
    // Route::get('/share', [ShareController::class, 'index'])->name('share.index');
});
