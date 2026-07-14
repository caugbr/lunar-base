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
use App\Http\Controllers\Admin\RolesPermissionsController;
use App\Http\Controllers\Admin\PluginController;
use App\Http\Controllers\Admin\ThemeController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\ProfileController as EditorProfileController;

// ========== ROTAS PÚBLICAS ==========
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ========== ROTAS PROTEGIDAS ==========
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    // Perfil (ambos editam seu próprio perfil)
    Route::get('/profile', [EditorProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [EditorProfileController::class, 'update'])->name('profile.update');

    // Plugins
    Route::get('plugins', [PluginController::class, 'index'])->name('plugins.index');
    Route::post('plugins/{plugin}/toggle', [PluginController::class, 'toggle'])->name('plugins.toggle');
    Route::post('plugins/toggle-all/{status}', [PluginController::class, 'toggleAll'])
        ->name('plugins.toggle_all')
        ->where('status', '0|1');

    // Temas
    Route::get('themes', [ThemeController::class, 'index'])->name('themes.index');
    // Route::post('themes/{theme}/activate', [ThemeController::class, 'activate'])->name('themes.activate');
    Route::post('themes/{theme}/toggle', [ThemeController::class, 'toggle'])->name('themes.toggle');
    Route::get('themes/{theme}/screenshot', [ThemeController::class, 'screenshot'])->name('themes.screenshot');

    // Dashboard (ambos veem)
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard.index');
});

// ========== ROTAS PROTEGIDAS (ADMIN + EDITOR) ==========
Route::middleware(['auth', 'role:admin,editor'])->prefix('admin')->name('admin.')->group(function () {
    // Permissões
    Route::get('/admin/roles-permissions', [RolesPermissionsController::class, 'index'])
        ->name('roles-permissions')->middleware('permission:manage-settings');

    // Páginas
    Route::resource('pages', PageController::class)
        ->middleware('permission:manage-pages');

    // Posts
    Route::resource('posts', PostController::class)
        ->middleware('permission:manage-posts');

    // Media Manager
    Route::get('media/data', [MediaController::class, 'data'])->name('media.data');
    Route::resource('media', MediaController::class)->except(['create', 'show'])->parameters(['media' => 'media']);

    // Taxonomias
    Route::resource('taxonomies', TaxonomyController::class);
    Route::resource('terms', TermController::class);

    // Logs
    Route::get('logs', [AdminLogController::class, 'index'])->name('logs.index');
});

// ========== ROTAS ADMIN APENAS ==========
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // CRUD de usuários (só admin)
    Route::resource('users', UserController::class);

    // Configurações
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
});
