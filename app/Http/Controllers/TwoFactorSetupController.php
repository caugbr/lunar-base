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

    // public function show()
    // {
    //     if (!TwoFactorConfig::enabled()) {
    //         abort(404);
    //     }

    //     $user = Auth::user();
    //     $setting = $user->twoFactorSetting;

    //     // Se já ativo, não precisa estar aqui
    //     if ($setting && $setting->isActive()) {
    //         return redirect()->route('admin.profile.edit');
    //     }

    //     // Se não tem secret, gera um novo
    //     if (!$setting || !$setting->secret) {
    //         $secret = $this->google2fa->generateSecretKey();

    //         $setting = TwoFactorSetting::updateOrCreate(
    //             ['user_id' => $user->id],
    //             ['secret' => $secret, 'confirmed_at' => null]
    //         );
    //     }

    //     $qrCodeUrl = $this->google2fa->getQRCodeUrl(
    //         TwoFactorConfig::issuer(),
    //         $user->email,
    //         $setting->secret
    //     );

    //     return view('auth.two-factor.setup', [
    //         'qrCodeUrl' => $qrCodeUrl,
    //         'secret' => $setting->secret,
    //     ]);
    // }
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
        if (!TwoFactorConfig::enabled()) {
            abort(404);
        }

        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = Auth::user();
        $setting = $user->twoFactorSetting;

        if (!$setting || !$setting->secret) {
            return back()->withErrors(['code' => 'Configuração não iniciada. Tente novamente.']);
        }

        $valid = $this->google2fa->verifyKey(
            $setting->secret,
            $request->input('code'),
            TwoFactorConfig::windowPeriods()
        );

        if (!$valid) {
            return back()->withErrors(['code' => 'Código inválido. Verifique e tente novamente.']);
        }

        $setting->update(['confirmed_at' => now()]);

        return redirect()->route('admin.profile.edit')->with('success', 'Autenticação de dois fatores ativada com sucesso.');
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
}
