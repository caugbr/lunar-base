<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Public\PublicPageController;

require __DIR__.'/admin.php';

// home
Route::get('/', [HomeController::class, 'index'])->name('home');

// public pages
Route::get('/page/{slug}', [PublicPageController::class, 'show'])->name('public.page');
Route::get('/page/{widget_slug}/{slug}', [PublicPageController::class, 'showWidgetPage'])->name('public.widget.page');

