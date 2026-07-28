<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\GenericApiController;

/*
|--------------------------------------------------------------------------
| API Routes (Lunar Base - Headless Content Engine)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // Informações globais públicas do site
    Route::get('site', [GenericApiController::class, 'site']);

    // Resolver genérico de Permalinks e URLs amigáveis
    Route::get('resolve', [GenericApiController::class, 'resolve']);

    // Endpoints genéricos para entidades expostas
    Route::get('{entity}', [GenericApiController::class, 'index']);
    Route::get('{entity}/{key}', [GenericApiController::class, 'show']);

});
