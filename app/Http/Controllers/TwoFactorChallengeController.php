<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\TwoFactorConfig;
use App\Support\TwoFactorRateLimiter;
use App\Support\TwoFactorService;
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
            return back()->withErrors(['code' => 'Muitas tentativas. Aguarde um minuto.']);
        }

        $user = User::find($userId);
        if (!$user || !$user->hasTwoFactorEnabled()) {
            session()->forget(['mfa.user_id', 'mfa.started_at']);
            return redirect()->route('login');
        }

        $setting = $user->twoFactorSetting;
        $inputCode = $request->input('code');
        $isAuthenticated = false;

        // Tentar validar via E-mail (se houver código ativo)
        if ($setting->otp_code && $setting->otp_expires_at && now()->lessThan($setting->otp_expires_at)) {
            if (\Illuminate\Support\Facades\Hash::check($inputCode, $setting->otp_code)) {
                $isAuthenticated = true;
                // Limpa o código para evitar reuso
                $setting->update(['otp_code' => null, 'otp_expires_at' => null]);
            }
        }

        // Se não validou por e-mail, tentar via Google Authenticator (TOTP)
        if (!$isAuthenticated) {
            $validTotp = $this->google2fa->verifyKey(
                $setting->secret,
                $inputCode,
                \App\Support\TwoFactorConfig::windowPeriods()
            );

            if ($validTotp) {
                $isAuthenticated = true;
            }
        }

        // Resultado final
        if (!$isAuthenticated) {
            TwoFactorRateLimiter::hit($userId);
            $remaining = TwoFactorRateLimiter::remaining($userId);

            return back()->withErrors([
                'code' => 'Código inválido ou expirado.' . ($remaining > 0 ? " Tentativas restantes: {$remaining}." : '')
            ]);
        }

        // Sucesso
        TwoFactorRateLimiter::clear($userId);
        session()->forget(['mfa.user_id', 'mfa.started_at']);

        \Illuminate\Support\Facades\Auth::login($user);
        $request->session()->regenerate();

        return $user->role === 'subscriber' ? redirect()->intended('/') : redirect()->intended('/admin/dashboard');
    }

    public function sendEmailCode(Request $request)
    {
        $userId = session('mfa.user_id');
        $user = User::find($userId);
        TwoFactorService::sendOtpEmail($user);
        return back()->with('status', 'Código enviado para seu e-mail!');
    }
}
