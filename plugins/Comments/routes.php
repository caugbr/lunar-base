<?php

use Illuminate\Support\Facades\Route;
use Plugins\Comments\Http\Controllers\CommentController;

Route::middleware(['web'])->group(function () {
    Route::post('/comments', [CommentController::class, 'store'])->name('comments.store');
});
