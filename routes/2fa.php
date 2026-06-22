<?php

use App\Http\Controllers\TwoFactorSetupController;
use App\Http\Controllers\TwoFactorChallengeController;
use App\Http\Controllers\Admin\TwoFactorManagementController;
use Illuminate\Support\Facades\Route;

// 2FA Challenge (público, mas requer sessão parcial)
Route::get('/two-factor/challenge', [TwoFactorChallengeController::class, 'show'])
    ->name('two-factor.challenge');
Route::post('/two-factor/challenge', [TwoFactorChallengeController::class, 'verify']);

// 2FA Setup (requer autenticação completa)
Route::middleware('auth')->group(function () {
    Route::get('/two-factor/setup', [TwoFactorSetupController::class, 'show'])
        ->name('two-factor.setup');
    Route::post('/two-factor/setup', [TwoFactorSetupController::class, 'confirm']);
    Route::delete('/two-factor/setup', [TwoFactorSetupController::class, 'cancel'])
        ->name('two-factor.cancel');
});


Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::delete('/users/{user}/two-factor', [TwoFactorManagementController::class, 'disable'])
        ->name('admin.users.two-factor.disable');
});
