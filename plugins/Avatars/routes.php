<?php

use Illuminate\Support\Facades\Route;
use Plugins\Avatars\Http\Controllers\AvatarController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::post('/admin/profile/avatar', [AvatarController::class, 'update'])->name('admin.profile.avatar.update');
    Route::delete('/admin/profile/avatar', [AvatarController::class, 'destroy'])->name('admin.profile.avatar.destroy');
});
