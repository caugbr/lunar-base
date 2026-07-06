<?php

use Illuminate\Support\Facades\Route;
use Plugins\Maintenance\Http\Controllers\MaintenanceController;

Route::middleware(['web', 'auth'])->group(function () {
    // Route::get('/maintenance', [MaintenanceController::class, 'index'])->name('maintenance.index');
});
