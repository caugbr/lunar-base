<?php

use Illuminate\Support\Facades\Route;
use Plugins\Reactions\Http\Controllers\ReactionController;

Route::middleware(['web'])->group(function () {
    Route::post('/react/{type}/{id}/{value}', [ReactionController::class, 'store'])
        ->name('react')
        ->where(['value' => 'plus|minus']);
});
