<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

require __DIR__.'/admin.php';

if (dbAvailable('settings') && setting('auth.2fa_enabled', false)) {
    require __DIR__.'/2fa.php';
}

// home
Route::get('/', [HomeController::class, 'index'])->name('home');

if (dbAvailable('settings') && setting('auth.verify_email', false)) {
    // 1. Rota da tela de aviso para verificar e-mail
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->middleware('auth')->name('verification.notice');

    // 2. Rota que processa o clique no link do e-mail
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect()->route('admin.users.index')
            ->with('success', 'Seu e-mail foi confirmado com sucesso!');
    })->middleware(['auth', 'signed'])->name('verification.verify');

    // 3. Rota para re-enviar o e-mail caso não tenha chegado
    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('success', 'Um novo link de verificação foi enviado para o seu e-mail!');
    })->middleware(['auth', 'throttle:6,1'])->name('verification.send');
}

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

Route::get('/{any}', [App\Http\Controllers\Public\RouteOrchestratorController::class, 'handleCatchAll'])
    ->where('any', '.*')
    ->name('dynamic.catch.all');
