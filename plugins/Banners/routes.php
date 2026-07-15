<?php

use Illuminate\Support\Facades\Route;
use Plugins\Banners\Http\Controllers\BannerController;

// Rotas do Admin
Route::middleware(['web', 'auth', 'role:admin,editor'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('banners', BannerController::class);
        Route::get('banners/{banner}/stats', [BannerController::class, 'stats'])->name('banners.stats');
    });

// Rota publica de click tracking (301 redirect)
Route::middleware(['web'])
    ->get('banner/click/{id}', [BannerController::class, 'click'])
    ->name('banner.click');
