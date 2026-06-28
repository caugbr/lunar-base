<?php

use App\Http\Controllers\TwoFactorSetupController;
use App\Http\Controllers\TwoFactorChallengeController;
use App\Http\Controllers\Admin\TwoFactorManagementController;
use Illuminate\Support\Facades\Route;

// 2FA Challenge (público, mas requer sessão parcial)
Route::get('/two-factor/challenge', [TwoFactorChallengeController::class, 'show'])
    ->name('two-factor.challenge');
Route::post('/two-factor/challenge', [TwoFactorChallengeController::class, 'verify']);
Route::post('/two-factor/send-email', [TwoFactorChallengeController::class, 'sendEmailCode'])
    ->name('two-factor.send-email');

// 2FA Setup (requer autenticação completa)
Route::middleware('auth')->group(function () {
    Route::get('/two-factor/setup', [TwoFactorSetupController::class, 'show'])
        ->name('two-factor.setup');
    Route::post('/two-factor/setup', [TwoFactorSetupController::class, 'confirm']);
    Route::delete('/two-factor/setup', [TwoFactorSetupController::class, 'cancel'])
        ->name('two-factor.cancel');

        // Rotas para Setup por Email
    Route::post('/two-factor/setup-email-trigger', [TwoFactorSetupController::class, 'setupEmailTrigger'])
        ->name('two-factor.setup-email-trigger');
    Route::post('/two-factor/setup-email-confirm', [TwoFactorSetupController::class, 'setupEmailConfirm'])
        ->name('two-factor.setup-email-confirm');
});


Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::delete('/users/{user}/two-factor', [TwoFactorManagementController::class, 'disable'])
        ->name('admin.users.two-factor.disable');
});
