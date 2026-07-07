<?php

use Illuminate\Support\Facades\Route;
use Plugins\Menus\Http\Controllers\MenuController;

// Rotas do Admin protegidas pelos middlewares padrão do Lunar Base
Route::middleware(['web', 'auth', 'role:admin,editor'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // CRUD de Menus e tela do construtor
        Route::resource('menus', MenuController::class);

        // Rota dedicada via POST para salvar a árvore serializada de links
        Route::post('menus/{menu}/save-items', [MenuController::class, 'saveItems'])->name('menus.save_items');
    });
