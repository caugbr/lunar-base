<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
// use App\Http\Controllers\Public\PublicPageController;
// use App\Http\Controllers\Public\PublicPostController;
use App\Http\Controllers\DocsController;
use App\Http\Controllers\GenericFormController;
use App\Http\Controllers\Public\ReactionController;

require __DIR__.'/admin.php';

// home
Route::get('/', [HomeController::class, 'index'])->name('home');

//Docs
Route::get('/docs', [DocsController::class, 'index'])->name('docs');

// Forms
Route::get('/formulario/{slug}', [GenericFormController::class, 'show'])->name('public.forms.show');
Route::post('/formulario/{slug}', [GenericFormController::class, 'submit'])->name('public.forms.submit');

Route::post('/react/{type}/{id}/{value}', [ReactionController::class, 'store'])
    ->name('react')
    ->where(['value' => 'plus|minus']);

// =========================================================================
// ORQUESTRADOR DE CONFIGURAÇÃO DE PERMALINKS
// =========================================================================

// Casos com 3 segmentos (Páginas com namespace OU Blog filtrado por taxonomia)
Route::get('/{base}/{namespace}/{slug}', [App\Http\Controllers\Public\RouteOrchestratorController::class, 'handleThreeSegments'])
    ->name('dynamic.three.segments');

// Casos com 2 segmentos (Página individual OU Post individual)
Route::get('/{base}/{slug}', [App\Http\Controllers\Public\RouteOrchestratorController::class, 'handleTwoSegments'])
    ->name('dynamic.two.segments');

// Casos com 1 segmento (Listagem principal do Blog)
Route::get('/{base}', [App\Http\Controllers\Public\RouteOrchestratorController::class, 'handleOneSegment'])
    ->name('dynamic.one.segment');
