<?php

use Illuminate\Support\Facades\Route;
use Plugins\Comments\Http\Controllers\CommentController;
use Plugins\Comments\Http\Controllers\AdminCommentController;

/*
 * Rotas públicas
 */
Route::middleware(['web'])->group(function () {
    Route::post('/comments', [CommentController::class, 'store'])->name('comments.store');
});

/*
 * Rotas administrativas
 *
 * NOTA: Se o seu RouteServiceProvider do core já aplica o prefixo 'admin'
 * (ex: via routes/admin.php ou Route::prefix('admin')->name('admin.')->group(...)),
 * NÃO use ->prefix('admin') aqui para evitar duplicação (/admin/admin/comments).
 *
 * O nome da rota é 'admin.comments.index', então route('admin.comments.index')
 * funcionará corretamente. A URL final depende do prefixo aplicado pelo core.
 *
 * Se o core NÃO aplica prefixo admin, descomente o ->prefix('admin') abaixo.
 */
Route::middleware(['web', 'auth'])->name('admin.')->group(function () {
    Route::get('/comments', [AdminCommentController::class, 'index'])->name('comments.index');
    Route::patch('/comments/bulk', [AdminCommentController::class, 'bulkUpdate'])->name('comments.bulk');
    Route::post('/comments/bulk-delete', [AdminCommentController::class, 'bulkDestroy'])->name('comments.bulk-delete');
    Route::patch('/comments/{comment}', [AdminCommentController::class, 'update'])->name('comments.update');
    Route::delete('/comments/{comment}', [AdminCommentController::class, 'destroy'])->name('comments.destroy');
});
