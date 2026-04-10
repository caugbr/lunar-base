<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\TaxonomyController;
use App\Http\Controllers\Admin\TermController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\ProfileController as EditorProfileController;

// ========== ROTAS PÚBLICAS ==========
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ========== ROTAS PROTEGIDAS (ADMIN + EDITOR) ==========
Route::middleware(['auth', 'role:admin,editor'])->prefix('admin')->name('admin.')->group(function () {
// Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard (ambos veem)
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

    // Perfil (ambos editam seu próprio perfil)
    Route::get('/profile', [EditorProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [EditorProfileController::class, 'update'])->name('profile.update');
});

// ========== ROTAS ADMIN APENAS ==========
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
// Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    // CRUD de páginas (só admin)
    Route::resource('pages', PageController::class);

    // CRUD de usuários (só admin)
    Route::resource('users', UserController::class);

    // Dentro do grupo com middleware auth e role:admin
    Route::resource('taxonomies', TaxonomyController::class);
    Route::resource('terms', TermController::class);

    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
});
