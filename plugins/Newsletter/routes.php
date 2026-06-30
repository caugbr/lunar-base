<?php

use Illuminate\Support\Facades\Route;
use Plugins\Newsletter\Http\Controllers\NewsletterController;

Route::middleware(['web'])->group(function () {
    // Route::get('/newsletter', [NewsletterController::class, 'index'])->name('newsletter.index');
});
