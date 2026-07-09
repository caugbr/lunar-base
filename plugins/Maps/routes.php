<?php

use Illuminate\Support\Facades\Route;
use Plugins\Maps\Http\Controllers\Admin\MapController;
use Plugins\Maps\Http\Controllers\Api\GeocodeController;
use Plugins\Maps\Http\Controllers\Api\GeoJsonController;

// ─── ADMIN ────────────────────────────────────────────────────────────────
Route::middleware(['web', 'auth', 'role:admin,editor'])
    ->prefix('admin')->name('admin.')
    ->group(function () {
        Route::resource('maps', MapController::class)
            ->middleware('permission:manage-pages');
    });

// ─── API interna (autenticada) ────────────────────────────────────────────
Route::middleware(['web', 'auth'])
    ->prefix('api/maps')->name('api.maps.')
    ->group(function () {
        // Geocoding de endereço (Nominatim)
        Route::get('/geocode', [GeocodeController::class, 'search'])->name('geocode');

        // GeoJSON pré-cadastrados
        Route::get('/geojson',            [GeoJsonController::class, 'index'])->name('geojson.index');
        Route::get('/geojson/{pid}',      [GeoJsonController::class, 'show'])->name('geojson.show')
            ->where('pid', '[A-Za-z0-9_-]+');
        Route::post('/geojson/find',      [GeoJsonController::class, 'find'])->name('geojson.find');
        Route::post('/geojson/save',      [GeoJsonController::class, 'save'])->name('geojson.save');
    });

// ─── API pública (leitura de GeoJSON para o mapa no front) ────────────────
Route::middleware(['web'])
    ->prefix('api/maps/public')->name('api.maps.public.')
    ->group(function () {
        Route::get('/geojson/{pid}', [GeoJsonController::class, 'show'])->name('geojson.show')
            ->where('pid', '[A-Za-z0-9_-]+');
    });
