<?php

namespace App\Http\Controllers;

use App\Support\TwoFactorConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;

class ProfileController extends Controller
{

    public function edit()
    {
        $user = Auth::user();
        $qrCodeUrl = null;
        $qrCodeSize = null;

        if (TwoFactorConfig::enabled() && $user->twoFactorSetting && !$user->twoFactorSetting->isActive()) {
            $google2fa = new Google2FA();
            $qrCodeUrl = $google2fa->getQRCodeUrl(
                TwoFactorConfig::issuer(),
                $user->email,
                $user->twoFactorSetting->secret
            );
            $qrCodeSize = TwoFactorConfig::qrCodeSize();
        }

        return view('admin.users.profile', compact('user', 'qrCodeUrl', 'qrCodeSize'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'new_password' => 'nullable|min:8|confirmed',
            'new_password_confirmation' => 'required_with:new_password',
        ],
        [
            'new_password.min' => 'A nova senha deve ter pelo menos 8 caracteres.',
            'new_password.confirmed' => 'A confirmação da senha não corresponde.',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (!empty($validated['new_password'])) {
            $user->password = Hash::make($validated['new_password']);
            log_admin("Usuário alterou a própria senha", "security");
        }

        $user->save();

        return back()->with('success', 'Perfil atualizado com sucesso!');
    }
}
