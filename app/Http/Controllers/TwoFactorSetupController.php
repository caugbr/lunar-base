<?php

namespace App\Http\Controllers;

use App\Models\TwoFactorSetting;
use App\Support\TwoFactorConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorSetupController extends Controller
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    public function show()
    {
        if (!TwoFactorConfig::enabled()) {
            abort(404);
        }

        $user = Auth::user();
        $setting = $user->twoFactorSetting;

        // Se já ativo, não precisa estar aqui
        if ($setting && $setting->isActive()) {
            return redirect()->route('admin.profile.edit');
        }

        // Se não tem secret, gera um novo
        if (!$setting || !$setting->secret) {
            $secret = $this->google2fa->generateSecretKey();

            TwoFactorSetting::updateOrCreate(
                ['user_id' => $user->id],
                ['secret' => $secret, 'confirmed_at' => null]
            );
        }

        // Redireciona para o profile com flag de setup em andamento
        return redirect()->route('admin.profile.edit', ['setup_2fa' => 1]);
    }

    public function confirm(Request $request)
    {
        $request->validate(['code' => 'required|string|size:6']);
        $user = Auth::user();
        $setting = $user->twoFactorSetting;

        if (!$setting) return back()->withErrors(['code' => 'Configuração não iniciada.']);

        $isAuthenticated = false;

        // 1. Tentar validar via E-mail (se houver código ativo e o código bater)
        if ($setting->otp_code && $setting->otp_expires_at && now()->lessThan($setting->otp_expires_at)) {
            if (\Illuminate\Support\Facades\Hash::check($request->code, $setting->otp_code)) {
                $isAuthenticated = true;
            }
        }

        // 2. Se não foi e-mail, tentar via Google Authenticator
        if (!$isAuthenticated && $setting->secret) {
            $validTotp = $this->google2fa->verifyKey($setting->secret, $request->code, \App\Support\TwoFactorConfig::windowPeriods());
            if ($validTotp) {
                $isAuthenticated = true;
            }
        }

        if ($isAuthenticated) {
            $setting->update([
                'confirmed_at' => now(),
                'otp_code' => null,
                'otp_expires_at' => null
            ]);
            log_admin("Usuário ativou a autenticação de duas etapas", "security");
            return redirect()->route('admin.profile.edit')->with('success', 'Autenticação de dois fatores ativada!');
        }

        return back()->withErrors(['code' => 'Código inválido ou expirado.']);
    }

    public function cancel()
    {
        $user = Auth::user();
        $setting = $user->twoFactorSetting;

        if ($setting && !$setting->isActive()) {
            $setting->delete();
        }

        return redirect()->route('admin.profile.edit');
    }

    public function setupEmailTrigger(Request $request)
    {
        $user = Auth::user();
        // Gera um secret dummy (necessário para a lógica do sistema)
        $secret = $this->google2fa->generateSecretKey();

        TwoFactorSetting::updateOrCreate(
            ['user_id' => $user->id],
            ['secret' => $secret, 'confirmed_at' => null]
        );

        \App\Support\TwoFactorService::sendOtpEmail($user, 'setup');
        return back()->with('info', 'Código enviado para seu e-mail!');
    }
}
