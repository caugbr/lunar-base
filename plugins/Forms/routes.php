<?php

use Illuminate\Support\Facades\Route;
use Plugins\Forms\Http\Controllers\FormsController;
use Plugins\Forms\Http\Controllers\FormSubmissionController;
use Plugins\Forms\Http\Controllers\GenericFormController;

// Rotas Admin (usando o namespace do plugin)
Route::middleware(['web', 'auth', 'role:admin,editor'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('forms', FormsController::class);
        Route::resource('forms.submissions', FormSubmissionController::class)->only(['index', 'show', 'destroy']);
    });

Route::middleware(['web'])
    ->group(function () {
        Route::get('/formulario/{slug}', [GenericFormController::class, 'show'])->name('public.forms.show');
        Route::post('/formulario/{slug}', [GenericFormController::class, 'submit'])->name('public.forms.submit');
    });
