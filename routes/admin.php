<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\TaxonomyController;
use App\Http\Controllers\Admin\TermController;
use App\Http\Controllers\Admin\AdminLogController;
use App\Http\Controllers\Admin\FormController;
use App\Http\Controllers\Admin\FormSubmissionController;
use App\Http\Controllers\Admin\RolesPermissionsController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\ProfileController as EditorProfileController;

// ========== ROTAS PÚBLICAS ==========
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ========== ROTAS PROTEGIDAS (ADMIN + EDITOR) ==========
Route::middleware(['auth', 'role:admin,editor'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard (ambos veem)
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

    // Perfil (ambos editam seu próprio perfil)
    Route::get('/profile', [EditorProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [EditorProfileController::class, 'update'])->name('profile.update');

    Route::get('/admin/roles-permissions', [RolesPermissionsController::class, 'index'])
        ->name('roles-permissions')
        ->middleware('permission:manage-settings');
});

// ========== ROTAS ADMIN APENAS ==========
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('pages', PageController::class);
    Route::resource('posts', PostController::class);

    // CRUD de usuários (só admin)
    Route::resource('users', UserController::class);

    // Configurações

    // [GET] /admin/settings → Exibe página de configurações globais
    Route::get('settings', [SettingController::class, 'index'])
        ->name('settings.index');
        // ->middleware('can:manage-settings');

    // [POST] /admin/settings → Salva alterações nas configurações globais
    Route::post('settings', [SettingController::class, 'update'])
        ->name('settings.update');
        // ->middleware('can:manage-settings');

    // Media Manager
    Route::get('media/data', [MediaController::class, 'data'])->name('media.data');
    Route::resource('media', MediaController::class)
        ->except(['create', 'show'])
        ->parameters(['media' => 'media']);

    // Taxonomias
    Route::resource('taxonomies', TaxonomyController::class);
    Route::resource('terms', TermController::class);

    // Logs
    Route::get('logs', [AdminLogController::class, 'index'])->name('logs.index');

    // CRUD dos Formulários (Configurações)
    Route::resource('forms', FormController::class);

    // CRUD das Respostas (Aninhado nos formulários)
    // O 'only' garante que ele só crie as rotas de listagem, visualização e exclusão.
    Route::resource('forms.submissions', FormSubmissionController::class)->only(['index', 'show', 'destroy']);
});
