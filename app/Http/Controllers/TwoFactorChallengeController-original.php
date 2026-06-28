<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\TwoFactorConfig;
use App\Support\TwoFactorRateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorChallengeController extends Controller
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    public function show()
    {
        if (!session()->has('mfa.user_id')) {
            return redirect()->route('login');
        }

        // Verifica se sessão parcial expirou
        $startedAt = session('mfa.started_at');
        $timeout = TwoFactorConfig::partialLoginTimeout() * 60;

        if ($startedAt && now()->diffInSeconds($startedAt) > $timeout) {
            session()->forget(['mfa.user_id', 'mfa.started_at']);
            return redirect()->route('login')->withErrors([
                'email' => 'Sessão expirada. Faça login novamente.'
            ]);
        }

        return view('auth.two-factor.challenge');
    }

    public function verify(Request $request)
    {
        if (!session()->has('mfa.user_id')) {
            return redirect()->route('login');
        }

        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $userId = session('mfa.user_id');

        if (TwoFactorRateLimiter::tooManyAttempts($userId)) {
            return back()->withErrors([
                'code' => 'Muitas tentativas. Aguarde um minuto.'
            ]);
        }

        $user = User::find($userId);

        if (!$user || !$user->hasTwoFactorEnabled()) {
            session()->forget(['mfa.user_id', 'mfa.started_at']);
            return redirect()->route('login');
        }

        $setting = $user->twoFactorSetting;

        $valid = $this->google2fa->verifyKey(
            $setting->secret,
            $request->input('code'),
            TwoFactorConfig::windowPeriods()
        );

        if (!$valid) {
            TwoFactorRateLimiter::hit($userId);
            $remaining = TwoFactorRateLimiter::remaining($userId);

            return back()->withErrors([
                'code' => 'Código inválido.' . ($remaining > 0 ? " Tentativas restantes: {$remaining}." : '')
            ]);
        }

        // Limpa rate limiter e sessão parcial
        TwoFactorRateLimiter::clear($userId);
        session()->forget(['mfa.user_id', 'mfa.started_at']);

        // Login definitivo
        Auth::login($user);
        $request->session()->regenerate();

        // Redirect baseado no role (mesma lógica do seu AuthController)
        if ($user->role !== 'subscriber') {
            return redirect()->intended('/admin/dashboard');
        }

        return redirect()->intended('/');
    }
}
