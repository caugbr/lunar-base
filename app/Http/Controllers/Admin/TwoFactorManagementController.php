<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class TwoFactorManagementController extends Controller
{
    public function disable(Request $request, User $user)
    {
        if ($user->twoFactorSetting) {
            $user->twoFactorSetting->delete();
        }

        log_admin("2FA desativado para o usuário: {$user->name}", "security");

        return back()->with('success', "2FA desativado para {$user->name}.");
    }
}
