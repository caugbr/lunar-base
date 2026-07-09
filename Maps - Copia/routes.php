<?php

use Illuminate\Support\Facades\Route;
use Plugins\Maps\Http\Controllers\Admin\MapController;
use Plugins\Maps\Http\Controllers\Api\GeocodeController;

Route::middleware(['web', 'auth', 'role:admin,editor'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('maps', MapController::class)->middleware('permission:manage-pages');
});

Route::middleware(['web', 'auth'])->prefix('api/maps')->name('api.maps.')->group(function () {
    Route::get('/geocode', [GeocodeController::class, 'search'])->name('geocode');
});
